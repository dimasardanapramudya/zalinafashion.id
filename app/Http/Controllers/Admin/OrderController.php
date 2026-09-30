<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PAYMENT VERIFICATION HELPER
    |--------------------------------------------------------------------------
    |
    | Hanya pembayaran dengan status "paid" yang dianggap sudah diverifikasi.
    |
    */

    private function paymentIsVerified(Order $order): bool
    {
        return strtolower(
            trim(
                (string) $order->payment_status
            )
        ) === 'paid';
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER SUDAH DIBATALKAN?
    |--------------------------------------------------------------------------
    |
    | BUG FIX: pesanan berstatus "cancelled" (stok sudah dikembalikan) masih
    | bisa didorong ke packing/shipped/delivered atau diubah kembali ke status
    | lain. Akibatnya pesanan "hidup lagi" tanpa stok dikurangi ulang, dan
    | stok jadi lebih besar dari kenyataan. Semua endpoint fulfillment sekarang
    | menolak pesanan yang sudah dibatalkan.
    |
    */

    private function orderIsCancelled(Order $order): bool
    {
        return strtolower(
            trim(
                (string) $order->status
            )
        ) === 'cancelled';
    }

    private function cancelledOrderMessage(): string
    {
        return 'Pesanan ini sudah dibatalkan dan stoknya sudah dikembalikan. Status dan progress pengiriman tidak dapat diubah lagi.';
    }


    /*
    |--------------------------------------------------------------------------
    | NOMOR RESI WAJIB SEBELUM PROSES PENGIRIMAN
    |--------------------------------------------------------------------------
    |
    | Progress pengiriman (Dikemas -> Selesai -> Ke Kurir -> Dikirim ->
    | Diterima) dan perubahan status processing/shipped/delivered hanya boleh
    | berjalan setelah nomor resi / AWB diisi dan disimpan, supaya shipping
    | label selalu memuat nomor + barcode resi yang ditempel ke paket sebelum
    | diserahkan ke kurir.
    |
    */

    private function orderHasTracking(Order $order): bool
    {
        return trim(
            (string) $order->tracking_number
        ) !== '';
    }

    private function trackingRequiredMessage(): string
    {
        return 'Nomor resi / AWB wajib diisi dan disimpan terlebih dahulu sebelum proses pengiriman dapat dijalankan.';
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT BLOCK MESSAGE
    |--------------------------------------------------------------------------
    |
    | Dipertahankan sebagai pengaman tambahan apabila ada order lama/unpaid
    | yang mencoba mengakses endpoint fulfillment secara langsung.
    |
    */

    private function paymentNotVerifiedMessage(): string
    {
        return 'Pembayaran pesanan ini belum diverifikasi. Proses pengemasan dan pengiriman hanya dapat dilakukan setelah pembayaran dikonfirmasi.';
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER LIST
    |--------------------------------------------------------------------------
    |
    | PENTING:
    |
    | Halaman Pesanan Admin HANYA menampilkan order yang:
    |
    | payment_status = paid
    |
    | Pesanan dengan status:
    |
    | pending_verification
    | pending
    | failed
    | expired
    | unpaid
    | atau status pembayaran lainnya
    |
    | TIDAK akan ditampilkan di halaman Pesanan Admin.
    |
    | Setelah pembayaran diverifikasi menjadi "paid", order otomatis
    | dapat muncul di daftar Pesanan Admin.
    |
    */

    public function index(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | FILTER (pencarian + status)
            |--------------------------------------------------------------------------
            |
            | q      : nomor pesanan, nama/telepon/email pembeli, nomor resi,
            |          atau nama produk / varian / SKU di dalam pesanan.
            | filter : waiting | packing | shipping | delivered | cancelled
            |          | no_tracking
            |
            */

            $search = trim((string) $request->query('q', ''));
            $filter = (string) $request->query('filter', '');

            $allowedFilters = [
                'waiting',
                'packing',
                'shipping',
                'delivered',
                'cancelled',
                'no_tracking',
            ];

            if (!in_array($filter, $allowedFilters, true)) {
                $filter = '';
            }


            /*
            |--------------------------------------------------------------------------
            | QUERY DASAR: hanya order yang sudah paid
            |--------------------------------------------------------------------------
            */

            $paidOrders = fn () => Order::query()
                ->where('payment_status', 'paid');

            $notCancelled = function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhere('status', '!=', 'cancelled');
                });

                // Wajib mengembalikan query: dipakai berantai di
                // $notCancelled($paidOrders())->sum('grand_total').
                return $query;
            };

            /*
            | Satu tempat untuk aturan tiap filter, dipakai untuk daftar
            | maupun untuk menghitung angka di kartu statistik.
            */
            $applyFilter = function ($query, string $key) use ($notCancelled) {
                switch ($key) {
                    case 'waiting':
                        $notCancelled($query);
                        $query->where(function ($q) {
                            $q->whereNull('shipping_status')
                                ->orWhereIn('shipping_status', ['', 'waiting']);
                        });
                        break;

                    case 'packing':
                        $notCancelled($query);
                        $query->whereIn('shipping_status', ['packing', 'packed']);
                        break;

                    case 'shipping':
                        $notCancelled($query);
                        $query->whereIn('shipping_status', ['handed_to_courier', 'shipped']);
                        break;

                    case 'delivered':
                        $notCancelled($query);
                        $query->where('shipping_status', 'delivered');
                        break;

                    case 'cancelled':
                        $query->where('status', 'cancelled');
                        break;

                    case 'no_tracking':
                        $notCancelled($query);
                        $query->whereIn('shipping_status', ['packed', 'handed_to_courier', 'shipped'])
                            ->where(function ($q) {
                                $q->whereNull('tracking_number')
                                    ->orWhere('tracking_number', '');
                            });
                        break;
                }

                return $query;
            };


            /*
            |--------------------------------------------------------------------------
            | DAFTAR PESANAN
            |--------------------------------------------------------------------------
            */

            $query = $paidOrders()->with([
                'items.product',
                'items.variant',
                'payment',
                'payment.method',
                'user',
            ]);

            if ($filter !== '') {
                $applyFilter($query, $filter);
            }

            if ($search !== '') {
                $like = '%' . addcslashes($search, '%_\\') . '%';

                $query->where(function ($q) use ($like) {
                    $q->where('order_number', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like)
                        ->orWhere('customer_email', 'like', $like)
                        ->orWhere('tracking_number', 'like', $like)
                        ->orWhereHas('items', function ($items) use ($like) {
                            $items->where('product_name', 'like', $like)
                                ->orWhere('variant_name', 'like', $like)
                                ->orWhere('sku', 'like', $like);
                        });
                });
            }

            $orders = $query
                ->latest()
                ->paginate(30)
                ->withQueryString();


            /*
            |--------------------------------------------------------------------------
            | STATISTIK (lintas seluruh order paid, bukan hanya halaman ini)
            |--------------------------------------------------------------------------
            */

            $statusCounts = ['all' => $paidOrders()->count()];

            foreach ($allowedFilters as $key) {
                $statusCounts[$key] = $applyFilter($paidOrders(), $key)->count();
            }

            $stats = [
                'revenue' => (float) $notCancelled($paidOrders())
                    ->sum('grand_total'),
            ];


            /*
            |--------------------------------------------------------------------------
            | RETURN VIEW
            |--------------------------------------------------------------------------
            */

            return view(
                'admin.orders.index',
                [
                    'orders' => $orders,
                    'statusCounts' => $statusCounts,
                    'stats' => $stats,
                    'filters' => [
                        'q' => $search,
                        'filter' => $filter,
                    ],
                ]
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'ADMIN ORDER INDEX ERROR',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            |
            | BUG FIX: sebelumnya `return back()`. Kalau halaman ini dibuka
            | langsung / di-refresh, Referer-nya adalah halaman ini sendiri,
            | sehingga back() menyebabkan redirect berulang (loop) dan admin
            | hanya melihat "too many redirects". Sekarang halaman tetap
            | dirender dengan daftar kosong + pesan error.
            |
            */

            $request->session()->now(
                'error',
                'Terjadi kesalahan saat memuat daftar pesanan.'
            );

            return view(
                'admin.orders.index',
                [
                    'orders' => Order::query()
                        ->whereRaw('1 = 0')
                        ->paginate(30),
                    'statusCounts' => [],
                    'stats' => ['revenue' => 0],
                    'filters' => ['q' => '', 'filter' => ''],
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER DETAIL
    |--------------------------------------------------------------------------
    |
    | Detail order juga diamankan.
    |
    | Walaupun seseorang mengetahui URL order secara langsung,
    | order yang belum paid tidak boleh dibuka melalui fitur Pesanan Admin.
    |
    */

    public function show(
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return redirect()
                    ->route('admin.orders.index')
                    ->with(
                        'error',
                        'Pesanan belum dapat ditampilkan karena pembayaran belum diverifikasi.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | LOAD RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $order->loadMissing([
                'items',
                'items.product',
                'items.variant',
                'payment',
                'payment.method',
                'user',
            ]);


            /*
            |--------------------------------------------------------------------------
            | RETURN VIEW
            |--------------------------------------------------------------------------
            */

            return view(
                'admin.orders.show',
                [
                    'order' => $order,
                ]
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'ADMIN ORDER DETAIL ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat membuka detail pesanan.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MAIN ORDER STATUS
    |--------------------------------------------------------------------------
    |
    | Status utama:
    |
    | pending
    | processing
    | shipped
    | delivered
    | cancelled
    |
    | ATURAN:
    |
    | Karena halaman Pesanan Admin hanya berisi order paid,
    | status fulfillment secara normal hanya akan digunakan pada
    | order yang sudah diverifikasi.
    |
    | Payment gate tetap dipertahankan sebagai pengaman tambahan.
    |
    */

    public function updateStatus(
        Request $request,
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATE REQUEST
            |--------------------------------------------------------------------------
            */

            $data = $request->validate([
                'status' => [
                    'required',
                    'string',
                    'in:pending,processing,shipped,delivered,cancelled',
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | NEW STATUS
            |--------------------------------------------------------------------------
            */

            $newStatus =
                $data['status'];


            /*
            |--------------------------------------------------------------------------
            | PREVIOUS STATUS
            |--------------------------------------------------------------------------
            |
            | Disimpan sebelum ditimpa, supaya kita tahu apakah order ini
            | BARU SAJA dibatalkan (bukan sudah cancelled sebelumnya) —
            | dipakai sebagai penjaga supaya stok tidak dikembalikan dua kali.
            |
            */

            $previousStatus =
                $order->status;

            /*
            | GUARD: pesanan cancelled tidak boleh diubah lagi, dan pesanan
            | yang sudah diterima pelanggan tidak boleh dibatalkan (stok
            | tidak boleh dikembalikan untuk barang yang sudah sampai).
            */
            $blockedMessage = null;

            if (
                $this->orderIsCancelled($order)
                &&
                $newStatus !== 'cancelled'
            ) {
                $blockedMessage = $this->cancelledOrderMessage();
            } elseif (
                $newStatus === 'cancelled'
                &&
                strtolower((string) $previousStatus) === 'delivered'
            ) {
                $blockedMessage = 'Pesanan yang sudah diterima pelanggan tidak dapat dibatalkan.';
            }

            if (
                $blockedMessage === null
                &&
                in_array(
                    $newStatus,
                    ['processing', 'shipped', 'delivered'],
                    true
                )
                &&
                !$this->orderHasTracking($order)
            ) {
                $blockedMessage = $this->trackingRequiredMessage();
            }

            if ($blockedMessage !== null) {
                if (
                    $request->expectsJson()
                    ||
                    $request->ajax()
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => $blockedMessage,
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'shipping_status' => $order->shipping_status,
                        'payment_status' => $order->payment_status,
                    ], 422);
                }

                return back()->with('error', $blockedMessage);
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT GATE
            |--------------------------------------------------------------------------
            |
            | Status fulfillment tidak boleh berjalan jika payment belum paid.
            |
            */

            $shippingRelatedStatuses = [
                'processing',
                'shipped',
                'delivered',
            ];


            if (
                in_array(
                    $newStatus,
                    $shippingRelatedStatuses,
                    true
                )
                &&
                !$this->paymentIsVerified($order)
            ) {

                $message =
                    $this->paymentNotVerifiedMessage();


                /*
                |--------------------------------------------------------------------------
                | JSON RESPONSE
                |--------------------------------------------------------------------------
                */

                if (
                    $request->expectsJson()
                    ||
                    $request->ajax()
                ) {
                    return response()->json([
                        'success' =>
                            false,

                        'message' =>
                            $message,

                        'order_id' =>
                            $order->id,

                        'status' =>
                            $order->status,

                        'shipping_status' =>
                            $order->shipping_status,

                        'payment_status' =>
                            $order->payment_status,
                    ], 422);
                }


                /*
                |--------------------------------------------------------------------------
                | NORMAL RESPONSE
                |--------------------------------------------------------------------------
                */

                return redirect()
                    ->route('admin.orders.index')
                    ->with(
                        'error',
                        $message
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MAIN ORDER STATUS
            |--------------------------------------------------------------------------
            */

            $order->status =
                $newStatus;


            /*
            |--------------------------------------------------------------------------
            | SYNCHRONIZE SHIPPING STATUS
            |--------------------------------------------------------------------------
            */

            switch ($newStatus) {

                /*
                |--------------------------------------------------------------------------
                | PROCESSING
                |--------------------------------------------------------------------------
                */

                case 'processing':

                    if (
                        empty(
                            $order->shipping_status
                        )
                        ||
                        $order->shipping_status === 'waiting'
                    ) {
                        $order->shipping_status =
                            'packing';
                    }

                    break;


                /*
                |--------------------------------------------------------------------------
                | SHIPPED
                |--------------------------------------------------------------------------
                */

                case 'shipped':

                    $order->shipping_status =
                        'shipped';

                    break;


                /*
                |--------------------------------------------------------------------------
                | DELIVERED
                |--------------------------------------------------------------------------
                */

                case 'delivered':

                    $order->shipping_status =
                        'delivered';

                    break;


                /*
                |--------------------------------------------------------------------------
                | CANCELLED
                |--------------------------------------------------------------------------
                */

                case 'cancelled':

                    /*
                    | Riwayat shipping tetap dipertahankan.
                    */

                    break;


                /*
                |--------------------------------------------------------------------------
                | PENDING
                |--------------------------------------------------------------------------
                */

                case 'pending':

                    if (
                        empty(
                            $order->shipping_status
                        )
                    ) {
                        $order->shipping_status =
                            'waiting';
                    }

                    break;
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE + KEMBALIKAN STOK JIKA BARU DIBATALKAN
            |--------------------------------------------------------------------------
            |
            | BUG FIX: stok untuk setiap item pesanan sudah dikurangi saat
            | customer checkout (lihat CheckoutController@store). Tapi
            | sebelumnya, saat admin mengubah status order menjadi
            | "cancelled" di sini, stok yang sudah dikurangi itu TIDAK
            | PERNAH dikembalikan — sama seperti bug yang terjadi pada
            | penolakan pembayaran di PaymentController@reject.
            |
            | Sekarang: penyimpanan status order dan pengembalian stok
            | dibungkus dalam satu transaksi database. Stok hanya
            | dikembalikan kalau order ini BARU SAJA berubah menjadi
            | cancelled ($previousStatus !== 'cancelled') — supaya kalau
            | admin menyimpan ulang status "cancelled" pada order yang
            | memang sudah cancelled, stok tidak ikut bertambah lagi.
            |
            */

            DB::transaction(function () use ($order, $newStatus, $previousStatus) {

                $order->save();

                if (
                    $newStatus === 'cancelled'
                    &&
                    $previousStatus !== 'cancelled'
                ) {
                    $this->restoreStockForOrder($order);
                }

            });


            /*
            |--------------------------------------------------------------------------
            | AJAX / JSON RESPONSE
            |--------------------------------------------------------------------------
            */

            if (
                $request->expectsJson()
                ||
                $request->ajax()
            ) {
                return response()->json([
                    'success' =>
                        true,

                    'message' =>
                        'Status pesanan berhasil diperbarui.',

                    'order_id' =>
                        $order->id,

                    'status' =>
                        $order->status,

                    'shipping_status' =>
                        $order->shipping_status,

                    'payment_status' =>
                        $order->payment_status,

                    'tracking_number' =>
                        $order->tracking_number,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | NORMAL RESPONSE
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'success',
                    'Status pesanan berhasil diperbarui.'
                );

        } catch (ValidationException $e) {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION ERROR
            |--------------------------------------------------------------------------
            */

            if (
                $request->expectsJson()
                ||
                $request->ajax()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Data status pesanan tidak valid.',

                    'errors' =>
                        $e->errors(),
                ], 422);
            }


            throw $e;

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG SERVER ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'ADMIN UPDATE ORDER STATUS ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | JSON ERROR
            |--------------------------------------------------------------------------
            */

            if (
                $request->expectsJson()
                ||
                $request->ajax()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Terjadi kesalahan pada server saat memperbarui status pesanan.',
                ], 500);
            }


            /*
            |--------------------------------------------------------------------------
            | NORMAL ERROR
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat memperbarui status pesanan.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN STOK UNTUK SEMUA ITEM DALAM SATU ORDER
    |--------------------------------------------------------------------------
    |
    | Dipakai saat order dibatalkan (lihat updateStatus()). Mengunci baris
    | produk/varian (lockForUpdate) sebelum menambah stok, supaya nilai
    | stok yang ditambah selalu berdasarkan angka terbaru di database,
    | bukan angka yang mungkin sudah usang (stale) di memori.
    |
    */

    private function restoreStockForOrder(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {

            $quantity = (int) $item->quantity;

            if ($quantity <= 0) {
                continue;
            }

            if ($item->variant_id) {

                $variant = ProductVariant::where(
                    'id',
                    $item->variant_id
                )
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->increment('stock', $quantity);

                    /*
                    | BUG FIX: increment() tidak memicu event model, jadi
                    | products.stock (stok induk) tidak ikut naik dan jadi
                    | basi. Sinkronkan dari total stok varian aktif.
                    */
                    Product::find($variant->product_id)
                        ?->syncStockFromVariants();
                }

                continue;

            }

            $product = Product::where(
                'id',
                $item->product_id
            )
                ->lockForUpdate()
                ->first();

            if ($product) {
                $product->increment('stock', $quantity);
            }

        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SHIPPING STATUS
    |--------------------------------------------------------------------------
    |
    | Alur:
    |
    | waiting
    |     ↓
    | packing
    |     ↓
    | packed
    |     ↓
    | handed_to_courier
    |     ↓
    | shipped
    |     ↓
    | delivered
    |
    */

    public function updateShippingStatus(
        Request $request,
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATE REQUEST
            |--------------------------------------------------------------------------
            */

            $data = $request->validate([
                'status' => [
                    'required',
                    'string',
                    'in:waiting,packing,packed,handed_to_courier,shipped,delivered',
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | STATUS STEP MAP
            |--------------------------------------------------------------------------
            */

            $statusSteps = [
                'waiting' =>
                    0,

                'packing' =>
                    1,

                'packed' =>
                    2,

                'handed_to_courier' =>
                    3,

                'shipped' =>
                    4,

                'delivered' =>
                    5,
            ];


            /*
            |--------------------------------------------------------------------------
            | CURRENT STATUS
            |--------------------------------------------------------------------------
            */

            $currentStatus =
                $order->shipping_status
                ?: 'waiting';


            /*
            |--------------------------------------------------------------------------
            | REQUESTED STATUS
            |--------------------------------------------------------------------------
            */

            $requestedStatus =
                $data['status'];


            /*
            |--------------------------------------------------------------------------
            | CURRENT STEP
            |--------------------------------------------------------------------------
            */

            $currentStep =
                $statusSteps[$currentStatus]
                ?? 0;


            /*
            |--------------------------------------------------------------------------
            | REQUESTED STEP
            |--------------------------------------------------------------------------
            */

            $requestedStep =
                $statusSteps[$requestedStatus]
                ?? 0;


            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            |
            | Karena hanya order paid yang masuk daftar admin,
            | order normal pasti sudah lolos.
            |
            | Pengaman tetap dipertahankan untuk request langsung.
            |
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        $this->paymentNotVerifiedMessage(),

                    'order_id' =>
                        $order->id,

                    'status' =>
                        $order->status,

                    'shipping_status' =>
                        $currentStatus,

                    'payment_status' =>
                        $order->payment_status,

                    'current_step' =>
                        $currentStep,

                    'requested_status' =>
                        $requestedStatus,
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | ONLY ALLOW NEXT STEP
            |--------------------------------------------------------------------------
            */

            if (
                !array_key_exists(
                    $requestedStatus,
                    $statusSteps
                )
            ) {

                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Status pengiriman tidak dikenal.',

                    'shipping_status' =>
                        $currentStatus,

                    'current_step' =>
                        $currentStep,
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | PREVENT INVALID PROGRESS
            |--------------------------------------------------------------------------
            */

            if (
                $requestedStep
                !==
                $currentStep + 1
            ) {

                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Progress pengiriman harus diperbarui secara berurutan.',

                    'shipping_status' =>
                        $currentStatus,

                    'current_step' =>
                        $currentStep,

                    'requested_status' =>
                        $requestedStatus,

                    'requested_step' =>
                        $requestedStep,

                    'payment_status' =>
                        $order->payment_status,
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHIPPING STATUS
            |--------------------------------------------------------------------------
            */

            if ($this->orderIsCancelled($order)) {
                return response()->json([
                    'success' =>
                        false,
                    'message' =>
                        $this->cancelledOrderMessage(),
                    'order_id' =>
                        $order->id,
                    'status' =>
                        $order->status,
                    'shipping_status' =>
                        $currentStatus,
                    'payment_status' =>
                        $order->payment_status,
                    'current_step' =>
                        $currentStep,
                    'requested_status' =>
                        $requestedStatus,
                ], 422);
            }

            if (!$this->orderHasTracking($order)) {
                return response()->json([
                    'success' =>
                        false,
                    'message' =>
                        $this->trackingRequiredMessage(),
                    'requires_tracking' =>
                        true,
                    'order_id' =>
                        $order->id,
                    'status' =>
                        $order->status,
                    'shipping_status' =>
                        $currentStatus,
                    'payment_status' =>
                        $order->payment_status,
                    'current_step' =>
                        $currentStep,
                    'requested_status' =>
                        $requestedStatus,
                ], 422);
            }

            $order->shipping_status =
                $requestedStatus;


            /*
            |--------------------------------------------------------------------------
            | SYNCHRONIZE MAIN ORDER STATUS
            |--------------------------------------------------------------------------
            */

            switch ($requestedStatus) {

                case 'waiting':

                    if (
                        $order->status !== 'cancelled'
                    ) {
                        $order->status =
                            'pending';
                    }

                    break;


                case 'packing':

                    $order->status =
                        'processing';

                    break;


                case 'packed':

                    $order->status =
                        'processing';

                    break;


                case 'handed_to_courier':

                    $order->status =
                        'processing';

                    break;


                case 'shipped':

                    $order->status =
                        'shipped';

                    break;


                case 'delivered':

                    $order->status =
                        'delivered';

                    break;
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE ORDER
            |--------------------------------------------------------------------------
            */

            $order->save();


            /*
            |--------------------------------------------------------------------------
            | SUCCESS MESSAGE
            |--------------------------------------------------------------------------
            */

            $messages = [

                'waiting' =>
                    'Pesanan menunggu proses admin.',

                'packing' =>
                    'Pesanan sekarang sedang dikemas.',

                'packed' =>
                    'Pesanan selesai dikemas.',

                'handed_to_courier' =>
                    'Pesanan telah diserahkan kepada kurir.',

                'shipped' =>
                    'Pesanan sekarang sedang dalam pengiriman.',

                'delivered' =>
                    'Pesanan telah diterima pelanggan.',
            ];


            /*
            |--------------------------------------------------------------------------
            | RETURN JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    $messages[$requestedStatus]
                    ??
                    'Status pengiriman berhasil diperbarui.',

                'order_id' =>
                    $order->id,

                'status' =>
                    $order->status,

                'shipping_status' =>
                    $order->shipping_status,

                'payment_status' =>
                    $order->payment_status,

                'current_step' =>
                    $statusSteps[
                        $order->shipping_status
                    ]
                    ?? 0,

                'previous_status' =>
                    $currentStatus,

                'previous_step' =>
                    $currentStep,

                'tracking_number' =>
                    $order->tracking_number,
            ], 200);

        } catch (ValidationException $e) {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION ERROR
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Data status pengiriman tidak valid.',

                'errors' =>
                    $e->errors(),
            ], 422);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG REAL ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'UPDATE SHIPPING STATUS ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'request_status' =>
                        $request->input(
                            'status'
                        ),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | ALWAYS RETURN JSON
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Terjadi kesalahan pada server saat memperbarui status pengiriman.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | START PACKING
    |--------------------------------------------------------------------------
    */

    public function startPacking(
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return back()
                    ->with(
                        'error',
                        $this->paymentNotVerifiedMessage()
                    );
            }

            if ($this->orderIsCancelled($order)) {
                return back()->with(
                    'error',
                    $this->cancelledOrderMessage()
                );
            }

            if (!$this->orderHasTracking($order)) {
                return back()->with(
                    'error',
                    $this->trackingRequiredMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT STATUS
            |--------------------------------------------------------------------------
            */

            $currentStatus =
                $order->shipping_status
                ?: 'waiting';


            /*
            |--------------------------------------------------------------------------
            | VALIDATE CURRENT STATUS
            |--------------------------------------------------------------------------
            */

            if (
                $currentStatus !== 'waiting'
            ) {

                return back()
                    ->with(
                        'error',
                        'Pesanan tidak dapat langsung dimulai karena progress pengiriman sudah berjalan.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MAIN STATUS
            |--------------------------------------------------------------------------
            */

            $order->status =
                'processing';


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHIPPING STATUS
            |--------------------------------------------------------------------------
            */

            $order->shipping_status =
                'packing';


            /*
            |--------------------------------------------------------------------------
            | SAVE
            |--------------------------------------------------------------------------
            */

            $order->save();


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'success',
                    'Pembayaran telah diverifikasi. Pesanan sekarang sedang dikemas.'
                );

        } catch (\Throwable $e) {

            Log::error(
                'START PACKING ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat memulai proses pengemasan.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HANDOVER TO COURIER
    |--------------------------------------------------------------------------
    */

    public function handoverToCourier(
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return back()
                    ->with(
                        'error',
                        $this->paymentNotVerifiedMessage()
                    );
            }

            if ($this->orderIsCancelled($order)) {
                return back()->with(
                    'error',
                    $this->cancelledOrderMessage()
                );
            }

            if (!$this->orderHasTracking($order)) {
                return back()->with(
                    'error',
                    $this->trackingRequiredMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT STATUS
            |--------------------------------------------------------------------------
            */

            $currentStatus =
                $order->shipping_status
                ?: 'waiting';


            /*
            |--------------------------------------------------------------------------
            | VALIDATE PROGRESS
            |--------------------------------------------------------------------------
            */

            if (
                $currentStatus !== 'packed'
            ) {

                return back()
                    ->with(
                        'error',
                        'Pesanan harus selesai dikemas sebelum diserahkan kepada kurir.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MAIN STATUS
            |--------------------------------------------------------------------------
            */

            $order->status =
                'processing';


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHIPPING STATUS
            |--------------------------------------------------------------------------
            */

            $order->shipping_status =
                'handed_to_courier';


            /*
            |--------------------------------------------------------------------------
            | SAVE
            |--------------------------------------------------------------------------
            */

            $order->save();


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'success',
                    'Pesanan berhasil diserahkan kepada kurir.'
                );

        } catch (\Throwable $e) {

            Log::error(
                'HANDOVER TO COURIER ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat menyerahkan paket kepada kurir.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MARK AS DELIVERED
    |--------------------------------------------------------------------------
    */

    public function markAsDelivered(
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return back()
                    ->with(
                        'error',
                        $this->paymentNotVerifiedMessage()
                    );
            }

            if ($this->orderIsCancelled($order)) {
                return back()->with(
                    'error',
                    $this->cancelledOrderMessage()
                );
            }

            if (!$this->orderHasTracking($order)) {
                return back()->with(
                    'error',
                    $this->trackingRequiredMessage()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CURRENT STATUS
            |--------------------------------------------------------------------------
            */

            $currentStatus =
                $order->shipping_status
                ?: 'waiting';


            /*
            |--------------------------------------------------------------------------
            | VALIDATE PROGRESS
            |--------------------------------------------------------------------------
            */

            if (
                $currentStatus !== 'shipped'
            ) {

                return back()
                    ->with(
                        'error',
                        'Pesanan harus berstatus dikirim sebelum ditandai telah diterima.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MAIN ORDER
            |--------------------------------------------------------------------------
            */

            $order->status =
                'delivered';


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHIPPING
            |--------------------------------------------------------------------------
            */

            $order->shipping_status =
                'delivered';


            /*
            |--------------------------------------------------------------------------
            | SAVE
            |--------------------------------------------------------------------------
            */

            $order->save();


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return back()
                ->with(
                    'success',
                    'Pesanan berhasil ditandai telah diterima pelanggan.'
                );

        } catch (\Throwable $e) {

            Log::error(
                'MARK AS DELIVERED ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat memperbarui status pesanan.'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE TRACKING NUMBER / AWB
    |--------------------------------------------------------------------------
    |
    | Nomor resi berasal dari pihak kurir.
    | Zalina tidak membuat nomor resi sendiri.
    |
    */

    public function updateTrackingNumber(
        Request $request,
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION + ORDER CANCELLED
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    $this->paymentNotVerifiedMessage(),
                    422
                );
            }

            if ($this->orderIsCancelled($order)) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    $this->cancelledOrderMessage(),
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | VALIDATE REQUEST
            |--------------------------------------------------------------------------
            */

            $data = $request->validate([
                'tracking_number' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);

            $trackingNumber = trim($data['tracking_number']);

            if ($trackingNumber === '') {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    'Nomor resi tidak boleh kosong.',
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CEGAH RESI GANDA
            |--------------------------------------------------------------------------
            |
            | Nomor resi dimasukkan manual. Kalau ada salah ketik yang kebetulan
            | sama dengan resi pesanan lain, label bisa tertukar. Tolak dan
            | beri tahu pesanan mana yang sudah memakainya.
            |
            */

            $usedBy = Order::query()
                ->where('tracking_number', $trackingNumber)
                ->where('id', '!=', $order->id)
                ->value('order_number');

            if ($usedBy) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    'Nomor resi ' . $trackingNumber . ' sudah dipakai pesanan ' . $usedBy . '. Periksa kembali nomor resi.',
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | SAVE TRACKING NUMBER
            |--------------------------------------------------------------------------
            */

            /*
            | Kalau nomor resi berubah setelah dikirim ke pelanggan, status
            | "terkirim" direset supaya pelanggan tidak melihat resi lama
            | dan admin mengirim ulang lewat "Cetak & Kirim Resi".
            */
            $previousTracking = trim((string) $order->tracking_number);
            $resetSent = false;

            if (
                $previousTracking !== $trackingNumber
                && $this->hasTrackingSentColumn($order)
                && filled($order->tracking_sent_at)
            ) {
                $order->tracking_sent_at = null;
                $resetSent = true;
            }

            $order->tracking_number = $trackingNumber;
            $order->save();


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            |
            | BUG FIX: form resi di halaman Pesanan mengirim request AJAX
            | (Accept: application/json) dan membaca balasan JSON. Sebelumnya
            | controller selalu me-redirect (back()), sehingga browser menerima
            | halaman HTML dan muncul "Server tidak mengembalikan JSON yang
            | valid." Sekarang request AJAX dijawab JSON; form biasa tetap
            | di-redirect.
            |
            */

            return $this->trackingResponse(
                $request,
                $order,
                true,
                'Nomor resi berhasil disimpan: ' . $trackingNumber
                    . (
                        $resetSent
                            ? '. Resi berubah, klik "Cetak & Kirim Resi" untuk mengirim ulang ke pelanggan.'
                            : ''
                    )
            );

        } catch (ValidationException $e) {

            /*
            | Validasi untuk request JSON otomatis dijawab 422 (JSON) oleh
            | Laravel, jadi cukup dilempar ulang.
            */

            throw $e;

        } catch (\Throwable $e) {

            Log::error(
                'UPDATE TRACKING NUMBER ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'tracking_number' =>
                        $request->input(
                            'tracking_number'
                        ),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );

            return $this->trackingResponse(
                $request,
                $order,
                false,
                'Terjadi kesalahan saat menyimpan nomor resi.',
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KIRIM RESI KE PELANGGAN
    |--------------------------------------------------------------------------
    |
    | Dipanggil tombol "Cetak & Kirim Resi". Setelah dikirim, halaman pesanan
    | pelanggan menampilkan detail resi: nomor, barcode, kurir, layanan, dst.
    |
    | Membutuhkan kolom orders.tracking_sent_at (lihat file migration).
    |
    */
    public function sendTracking(
        Request $request,
        Order $order
    ) {
        try {
            if (!$this->paymentIsVerified($order)) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    $this->paymentNotVerifiedMessage(),
                    422
                );
            }

            if ($this->orderIsCancelled($order)) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    $this->cancelledOrderMessage(),
                    422
                );
            }

            if (!$this->orderHasTracking($order)) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    $this->trackingRequiredMessage(),
                    422
                );
            }

            if (!$this->hasTrackingSentColumn($order)) {
                return $this->trackingResponse(
                    $request,
                    $order,
                    false,
                    'Kolom tracking_sent_at belum ada di tabel pesanan. Jalankan "php artisan migrate" terlebih dahulu.',
                    500
                );
            }

            $order->tracking_sent_at = now();
            $order->save();

            return $this->trackingResponse(
                $request,
                $order,
                true,
                'Resi ' . $order->tracking_number . ' berhasil dikirim ke pelanggan.'
            );
        } catch (\Throwable $e) {
            Log::error(
                'SEND TRACKING ERROR',
                [
                    'order_id' => $order->id ?? null,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            return $this->trackingResponse(
                $request,
                $order,
                false,
                'Terjadi kesalahan saat mengirim resi ke pelanggan.',
                500
            );
        }
    }

    private function hasTrackingSentColumn(Order $order): bool
    {
        return Schema::hasColumn(
            $order->getTable(),
            'tracking_sent_at'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPON NOMOR RESI (JSON untuk AJAX, redirect untuk form biasa)
    |--------------------------------------------------------------------------
    */

    private function trackingResponse(
        Request $request,
        Order $order,
        bool $success,
        string $message,
        int $status = 200
    ) {
        if (
            $request->expectsJson()
            ||
            $request->ajax()
        ) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'order_id' => $order->id,
                'status' => $order->status,
                'shipping_status' => $order->shipping_status,
                'payment_status' => $order->payment_status,
                'tracking_number' => $order->tracking_number,
                'tracking_sent' => filled($order->tracking_number)
                    && filled($order->tracking_sent_at ?? null),
                'tracking_sent_at' => filled($order->tracking_sent_at ?? null)
                    ? \Illuminate\Support\Carbon::parse($order->tracking_sent_at)
                        ->translatedFormat('d M Y, H:i')
                    : null,
            ], $status);
        }

        return back()->with(
            $success ? 'success' : 'error',
            $message
        );
    }


    /*
|--------------------------------------------------------------------------
| DELETE ORDER HISTORY
|--------------------------------------------------------------------------
|
| Pesanan hanya boleh dihapus setelah benar-benar delivered.
|
*/

public function destroyHistory(Order $order)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | ONLY DELIVERED ORDERS
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(trim((string) $order->shipping_status)) !== 'delivered'
            ||
            strtolower(trim((string) $order->status)) !== 'delivered'
        ) {
            return back()->with(
                'error',
                'Pesanan hanya dapat dihapus setelah pesanan diterima pelanggan.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD RELATED DATA
        |--------------------------------------------------------------------------
        */

        $order->load([
            'items',
            'payment',
        ]);


        /*
        |--------------------------------------------------------------------------
        | DELETE ORDER ITEMS
        |--------------------------------------------------------------------------
        */

        /*
        | BUG FIX: item, pembayaran, dan order dihapus dalam satu transaksi.
        | Sebelumnya tiga DELETE dijalankan terpisah — kalau salah satu gagal
        | di tengah, sisa data yatim (mis. order terhapus tapi payment masih
        | ada, atau sebaliknya).
        */
        $orderNumber = $order->order_number;

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->payment()->delete();
            $order->delete();
        });


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Riwayat pesanan ' . $orderNumber . ' berhasil dihapus.'
        );

    } catch (\Throwable $e) {

        Log::error(
            'DELETE ORDER HISTORY ERROR',
            [
                'order_id' => $order->id ?? null,
                'order_number' => $order->order_number ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]
        );


        return back()->with(
            'error',
            'Terjadi kesalahan saat menghapus riwayat pesanan.'
        );
    }
}


    /*
    |--------------------------------------------------------------------------
    | PRINT RECEIPT
    |--------------------------------------------------------------------------
    |
    | Receipt hanya dapat dibuka untuk pesanan yang sudah paid.
    |
    */

    public function printReceipt(
        Order $order
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | PAYMENT VERIFICATION
            |--------------------------------------------------------------------------
            */

            if (
                !$this->paymentIsVerified($order)
            ) {

                return redirect()
                    ->route('admin.orders.index')
                    ->with(
                        'error',
                        'Nota pesanan belum tersedia karena pembayaran belum diverifikasi.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | LOAD RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $order->loadMissing([
                'items',
                'items.product',
                'items.variant',
                'user',
                'payment',
                'payment.method',
            ]);


            /*
            |--------------------------------------------------------------------------
            | RETURN RECEIPT VIEW
            |--------------------------------------------------------------------------
            */

            return view(
                'admin.orders.receipt',
                [
                    'order' =>
                        $order,
                ]
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | LOG ERROR
            |--------------------------------------------------------------------------
            */

            Log::error(
                'ADMIN PRINT RECEIPT ERROR',
                [
                    'order_id' =>
                        $order->id ?? null,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            /*
            | Saat APP_DEBUG=true, sertakan penyebab error di pesan supaya
            | mudah dilacak (kelas error, pesan, file:baris). Di production
            | (APP_DEBUG=false) pesan tetap singkat dan detail hanya di log.
            */
            $detail = config('app.debug')
                ? ' [' . class_basename($e) . ': ' . $e->getMessage()
                    . ' - ' . basename($e->getFile()) . ':' . $e->getLine() . ']'
                : '';

            return back()
                ->with(
                    'error',
                    'Terjadi kesalahan saat membuka resi pesanan.' . $detail
                );
        }
    }
}