<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesLedger;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | EAGER LOAD RELASI PRODUK
        |--------------------------------------------------------------------------
        |
        | Sebelumnya hanya 'order' dan 'method' yang di-load, sehingga
        | halaman admin.payments.index tidak bisa menampilkan gambar
        | produk yang dipesan (relasi items/product belum diambil dari
        | database sama sekali). Ditambahkan 'order.items.product' dan
        | 'order.items.variant' supaya thumbnail produk pada kolom
        | Pesanan bisa langsung dirender tanpa memicu lazy loading
        | (yang bisa menyebabkan crash kalau aplikasi mengaktifkan
        | Model::preventLazyLoading()).
        |
        */

        $payments = Payment::with([
            'order',
            'order.items.product',
            'order.items.variant',
            'method',
        ])
            ->latest()
            ->paginate(30);

        /*
        |--------------------------------------------------------------------------
        | HITUNG STATISTIK LINTAS SELURUH DATA
        |--------------------------------------------------------------------------
        |
        | Dihitung terpisah dari $payments (yang hanya berisi satu halaman)
        | supaya kartu statistik "Menunggu / Terverifikasi / Ditolak" akurat
        | untuk SELURUH data, bukan cuma halaman yang sedang dibuka.
        |
        */

        $paymentStatusCounts = Payment::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view(
            'admin.payments.index',
            [
                'payments' => $payments,
                'paymentStatusCounts' => $paymentStatusCounts,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFIKASI PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function verify(
        Payment $payment,
        WhatsAppService $whatsapp
    ) {
        $payment->loadMissing(
            'order'
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDASI ORDER
        |--------------------------------------------------------------------------
        */

        if (!$payment->order) {

            return back()->with(
                'error',
                'Order untuk pembayaran ini tidak ditemukan.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | SUDAH DIVERIFIKASI
        |--------------------------------------------------------------------------
        */

        if ($payment->status === 'verified') {

            return back()->with(
                'success',
                'Pembayaran ini sudah dikonfirmasi sebelumnya.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI BUKTI PEMBAYARAN
        |--------------------------------------------------------------------------
        */

        if (!$payment->proof_path) {

            return back()->with(
                'error',
                'Tidak dapat mengonfirmasi pembayaran tanpa bukti pembayaran.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT SAJA
        |--------------------------------------------------------------------------
        |
        | Verifikasi pembayaran hanya mengubah
        | status pembayaran.
        |
        | Progress pesanan TIDAK diubah di sini.
        |
        */

        $payment->update([
            'status' => 'verified',

            'verified_at' => now(),

            'verified_by' => session(
                'zalina_user_id'
            ),

            'rejection_reason' => null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS PEMBAYARAN ORDER
        |--------------------------------------------------------------------------
        |
        | Hanya payment_status yang diubah.
        |
        | Status order seperti:
        | pending
        | processing
        | shipped
        | delivered
        |
        | TIDAK disentuh oleh verifikasi pembayaran.
        |
        */

        $order = $payment->order;

        $order->update([
            'payment_status' => 'paid',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CATAT KE BUKU BESAR PENJUALAN (sales_ledger)
        |--------------------------------------------------------------------------
        |
        | Ini yang membuat rekap penjualan & laporan keuangan TIDAK pernah
        | bergantung pada tabel `orders`. Begitu pembayaran online
        | terverifikasi, satu baris permanen ditulis ke sales_ledger
        | berisi SALINAN (snapshot) semua angka & nama produk — bukan
        | referensi hidup. Kalau order ini nanti dihapus dari riwayat
        | pesanan, baris rekap ini tetap ada.
        |
        | Guard exists() mencegah baris dobel kalau tombol verify
        | entah bagaimana ter-submit dua kali (mis. double click
        | sebelum redirect selesai).
        |
        */

        if (!SalesLedger::where('order_id', $order->id)->exists()) {

            $order->loadMissing(['items.product', 'items.variant']);

            $itemsSnapshot = $order->items->map(function ($item) {
                return [
                    'name' => $item->product?->name ?? $item->product_name ?? 'Produk',
                    'variant' => $item->variant?->name ?? $item->variant_name ?? null,
                    'qty' => (int) ($item->quantity ?? 0),
                    'price' => (float) ($item->price ?? 0),
                    'subtotal' => (float) ($item->subtotal ?? ((float) ($item->price ?? 0) * (int) ($item->quantity ?? 0))),
                ];
            })->values()->toArray();

            $shippingTotal = (float) ($order->shipping_total ?? 0);
            $adminFee = (float) ($order->admin_fee ?? 0);
            $discountTotal = (float) ($order->discount_total ?? 0);
            $grandTotal = (float) ($order->grand_total ?? 0);

            /*
            | Ongkir "riil" (yang benar-benar dibayar toko ke kurir) belum
            | tentu sama dengan shipping_total (yang ditagihkan ke pembeli
            | — bisa 0 kalau gratis ongkir). Kalau order.shipping_cost_actual
            | ada, pakai itu; kalau tidak, asumsikan sama dengan shipping_total
            | (aman untuk kasus tanpa integrasi ongkir realtime).
            */
            $shippingActual = (float) ($order->shipping_cost_actual ?? $shippingTotal);

            SalesLedger::create([
                'entry_date' => now()->toDateString(),
                'channel' => 'online',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $payment->method?->name ?? '-',
                'items_snapshot' => $itemsSnapshot,
                'items_count' => count($itemsSnapshot),
                'quantity_total' => (int) $order->items->sum('quantity'),
                'subtotal' => (int) round((float) ($order->subtotal ?? 0)),
                'discount_total' => (int) round($discountTotal),
                'shipping_total' => (int) round($shippingTotal),
                'shipping_cost_actual' => (int) round($shippingActual),
                'admin_fee' => (int) round($adminFee),
                'grand_total' => (int) round($grandTotal),
                'net_revenue' => (int) round($grandTotal - $shippingActual),
                'customer_name' => $order->customer_name,
                'recorded_by' => null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | NOTIFIKASI WHATSAPP CUSTOMER
        |--------------------------------------------------------------------------
        */

        $order->loadMissing([
            'items.product',
            'items.variant',
            'payment.method',
        ]);

        $customerPhone = $order->customer_phone;

        if (
            is_string($customerPhone) &&
            trim($customerPhone) !== ''
        ) {
            $formatRupiah = static function ($value): string {
                return 'Rp ' . number_format(
                    (float) $value,
                    0,
                    ',',
                    '.'
                );
            };

            $formatDate = static function ($value): string {
                if (!$value) {
                    return '-';
                }

                try {
                    return \Illuminate\Support\Carbon::parse(
                        $value
                    )->translatedFormat('d F Y, H:i');
                } catch (\Throwable $exception) {
                    return (string) $value;
                }
            };

            $productLines = [];

            foreach ($order->items as $index => $item) {
                $productName = $item->product?->name
                    ?? $item->product?->title
                    ?? 'Produk';

                $variantName = null;

                if ($item->variant) {
                    $variantName = $item->variant->name
                        ?? $item->variant->title
                        ?? $item->variant->value
                        ?? null;
                }

                $quantity = (int) (
                    $item->quantity
                    ?? $item->qty
                    ?? 0
                );

                $unitPrice = $item->price
                    ?? $item->unit_price
                    ?? $item->selling_price
                    ?? 0;

                $itemSubtotal = $item->subtotal
                    ?? ((float) $unitPrice * $quantity);

                $productLine = ($index + 1) . '. ' . $productName;

                if (
                    is_string($variantName) &&
                    trim($variantName) !== ''
                ) {
                    $productLine .= "\n"
                        . '   Varian: '
                        . $variantName;
                }

                $productLine .= "\n"
                    . '   Jumlah: '
                    . $quantity
                    . ' pcs'
                    . "\n"
                    . '   Harga satuan: '
                    . $formatRupiah($unitPrice)
                    . "\n"
                    . '   Subtotal: '
                    . $formatRupiah($itemSubtotal);

                $productLines[] = $productLine;
            }

            $paymentMethod = $payment->method->name
                ?? $payment->method->title
                ?? $payment->method->method_name
                ?? '-';

            $shippingAddress = trim(
                (string) ($order->shipping_address ?? '-')
            );

            $destination = trim(
                (string) ($order->destination_name ?? '-')
            );

            $courier = strtoupper(
                trim((string) ($order->shipping_courier ?? '-'))
            );

            $shippingService = trim(
                (string) ($order->shipping_service ?? '-')
            );

            $shippingEtd = trim(
                (string) ($order->shipping_etd ?? '-')
            );

            $shippingWeight = (int) (
                $order->shipping_weight
                ?? $order->getCalculatedShippingWeight()
                ?? 0
            );

            $orderStatus = trim(
                (string) ($order->status ?? 'pending')
            );

            $shippingStatus = method_exists(
                $order,
                'getShippingStatusLabel'
            )
                ? $order->getShippingStatusLabel()
                : (
                    (string) ($order->shipping_status ?? 'pending')
                );

            $messageParts = [
                'ZALINA FASHION',
                '━━━━━━━━━━━━━━━━━━━━',
                'PEMBAYARAN BERHASIL DIKONFIRMASI',
                '━━━━━━━━━━━━━━━━━━━━',
                '',
                'Halo ' . ($order->customer_name ?: 'Kak') . ',',
                '',
                'Pembayaran untuk pesanan Anda telah kami terima dan dikonfirmasi oleh admin.',
                'Pesanan sedang diproses untuk tahap pengemasan.',
                '',
                'DETAIL PESANAN',
                'Nomor pesanan: #' . $order->order_number,
                'Tanggal pesanan: ' . $formatDate($order->created_at),
                'Status pembayaran: LUNAS',
                'Status pesanan: ' . $orderStatus,
                '',
                'DAFTAR PRODUK',
                implode("\n\n", $productLines ?: [
                    'Tidak ada detail produk.'
                ]),
                '',
                'RINCIAN PEMBAYARAN',
                'Subtotal produk: ' . $formatRupiah($order->subtotal),
                'Diskon: ' . $formatRupiah($order->discount_total),
                'Ongkos kirim: ' . (
                    (float) $order->shipping_total <= 0
                        ? 'GRATIS ONGKIR'
                        : $formatRupiah($order->shipping_total)
                ),
                'Biaya admin: ' . $formatRupiah($order->admin_fee ?? 0),
                'TOTAL DIBAYAR: ' . $formatRupiah($order->grand_total),
                'Metode pembayaran: ' . $paymentMethod,
                'Jumlah pembayaran: ' . $formatRupiah(
                    $payment->amount_paid
                    ?? $payment->amount_expected
                    ?? $order->grand_total
                ),
                'Waktu konfirmasi: ' . $formatDate(now()),
                '',
                'DETAIL PENGIRIMAN',
                'Nama penerima: ' . ($order->customer_name ?: '-'),
                'Nomor WhatsApp: ' . ($order->customer_phone ?: '-'),
                'Alamat: ' . $shippingAddress,
                'Tujuan: ' . $destination,
                'Kurir: ' . $courier,
                'Layanan: ' . $shippingService,
                'Estimasi pengiriman: ' . $shippingEtd,
                'Berat paket: ' . $shippingWeight . ' gram',
                'Status pengiriman: ' . $shippingStatus,
                'Nomor resi: ' . (
                    $order->tracking_number ?: 'Belum tersedia'
                ),
                '',
                'INFORMASI SELANJUTNYA',
                'Pesanan akan diproses dan dikemas oleh tim Zalina Fashion.',
                'Informasi nomor resi akan diberikan setelah pesanan diserahkan kepada kurir.',
                '',
                'Terima kasih telah berbelanja di Zalina Fashion.',
                'Mohon simpan pesan ini sebagai bukti konfirmasi pembayaran.',
            ];

            $message = implode("\n", $messageParts);

            try {
                $whatsapp->sendNotification(
                    $customerPhone,
                    $message
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with(
            'success',
            'Pembayaran berhasil dikonfirmasi.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TOLAK PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        Payment $payment
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOAD ORDER
        |--------------------------------------------------------------------------
        */

        $payment->loadMissing(
            'order.items'
        );


        /*
        |--------------------------------------------------------------------------
        | SUDAH DITOLAK SEBELUMNYA (IDEMPOTENT)
        |--------------------------------------------------------------------------
        |
        | BUG FIX: sebelumnya reject() tidak mengecek status lama sama
        | sekali. Kalau admin tidak sengaja klik "Tolak" dua kali pada
        | payment yang sama, stok akan dikembalikan dua kali padahal
        | order-nya cuma satu — stok jadi lebih banyak dari yang
        | seharusnya. Sekarang kalau payment ini memang sudah berstatus
        | rejected, permintaan diabaikan tanpa mengubah stok lagi.
        |
        */

        if ($payment->status === 'rejected') {

            return back()->with(
                'success',
                'Pembayaran ini sudah ditolak sebelumnya.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | TOLAK PEMBAYARAN + KEMBALIKAN STOK
        |--------------------------------------------------------------------------
        |
        | BUG FIX: stok untuk setiap item pesanan sudah dikurangi saat
        | customer checkout (lihat CheckoutController@store). Tapi
        | sebelumnya, ketika admin menolak pembayaran (misalnya bukti
        | transfer palsu/salah), stok yang sudah dikurangi itu TIDAK
        | PERNAH dikembalikan — sehingga produk itu hilang permanen
        | dari stok meski tidak pernah benar-benar terjual.
        |
        | Sekarang penolakan dan pengembalian stok dibungkus dalam satu
        | transaksi database (dengan lockForUpdate saat menambah stok)
        | supaya konsisten dan aman dari kondisi balapan (race condition)
        | jika ada proses lain yang mengubah stok produk yang sama pada
        | saat bersamaan.
        |
        */

        DB::transaction(function () use ($payment, $request) {

            $payment->update([
                'status' => 'rejected',

                'rejection_reason' => $request->reason,

                'verified_at' => null,

                'verified_by' => null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | UPDATE PAYMENT STATUS ORDER SAJA
            |--------------------------------------------------------------------------
            |
            | Status progress pesanan tidak diubah.
            |
            */

            if ($payment->order) {

                $payment->order->update([
                    'payment_status' => 'rejected',
                ]);

                $this->restoreStockForOrder($payment->order);

            }

        });


        return back()->with(
            'success',
            'Pembayaran berhasil ditolak dan stok produk sudah dikembalikan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | KEMBALIKAN STOK UNTUK SEMUA ITEM DALAM SATU ORDER
    |--------------------------------------------------------------------------
    |
    | Dipakai saat pembayaran ditolak. Mengunci baris produk/varian
    | (lockForUpdate) sebelum menambah stok, supaya nilai stok yang
    | ditambah selalu berdasarkan angka terbaru di database, bukan
    | angka yang mungkin sudah usang (stale) di memori.
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
    | HALAMAN METODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function methods()
    {
        $methods = PaymentMethod::orderBy(
            'sort_order',
            'asc'
        )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();


        return view(
            'admin.payment-methods.index',
            [
                'methods' => $methods,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TAMBAH METODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function storeMethod(
        Request $request
    ) {
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'type' => [
                'required',
                'string',
                'max:50',
            ],

            'account_name' => [
                'required',
                'string',
                'max:100',
            ],

            'account_number' => [
                'required',
                'string',
                'max:100',
            ],

            'instructions' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ]);


        $data['is_active'] = $request->boolean(
            'is_active'
        );


        $data['sort_order'] = $data['sort_order']
            ?? 0;


        PaymentMethod::create([

            'name' => $data['name'],

            'type' => $data['type'],

            'account_name' => $data['account_name'],

            'account_number' => $data['account_number'],

            'instructions' => $data['instructions']
                ?? null,

            'is_active' => $data['is_active'],

            'sort_order' => $data['sort_order'],

        ]);


        return back()->with(
            'success',
            'Metode pembayaran berhasil ditambahkan.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE METODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function updateMethod(
        Request $request,
        PaymentMethod $paymentMethod
    ) {
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'type' => [
                'required',
                'string',
                'max:50',
            ],

            'account_name' => [
                'required',
                'string',
                'max:100',
            ],

            'account_number' => [
                'required',
                'string',
                'max:100',
            ],

            'instructions' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

        ]);


        $data['is_active'] = $request->boolean(
            'is_active'
        );


        $data['sort_order'] = $data['sort_order']
            ?? 0;


        $paymentMethod->update([

            'name' => $data['name'],

            'type' => $data['type'],

            'account_name' => $data['account_name'],

            'account_number' => $data['account_number'],

            'instructions' => $data['instructions']
                ?? null,

            'is_active' => $data['is_active'],

            'sort_order' => $data['sort_order'],

        ]);


        return back()->with(
            'success',
            'Metode pembayaran berhasil diperbarui.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS METODE PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public function destroyMethod(
        PaymentMethod $paymentMethod
    ) {
        $paymentMethod->delete();


        return back()->with(
            'success',
            'Metode pembayaran berhasil dihapus.'
        );
    }
}