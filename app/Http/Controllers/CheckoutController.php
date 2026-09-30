<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Discount;
use App\Models\Setting;
use App\Services\WhatsAppService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Get Cart
    |--------------------------------------------------------------------------
    */

    private function cart(Request $request)
    {
        $sessionId = $request->session()->getId();

        $userId = session(
            'zalina_user_id'
        );


        /*
        |--------------------------------------------------------------------------
        | Logged In User Cart
        |--------------------------------------------------------------------------
        */

        if ($userId) {

            return Cart::firstOrCreate(

                [
                    'user_id' => $userId,
                ],

                [
                    'session_id' => $sessionId,
                ]

            );

        }


        /*
        |--------------------------------------------------------------------------
        | Guest Cart
        |--------------------------------------------------------------------------
        */

        return Cart::firstOrCreate(

            [
                'session_id' => $sessionId,

                'user_id' => null,
            ]

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Checkout Page
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Get Cart
        |--------------------------------------------------------------------------
        */

        $cart = $this->cart(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Load Cart Relations
        |--------------------------------------------------------------------------
        */

        $cart->load([

            'items.product',

            'items.variant',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Empty Cart
        |--------------------------------------------------------------------------
        */

        if (

            $cart->items->isEmpty()

        ) {

            return redirect()

                ->route(
                    'cart.index'
                )

                ->with(

                    'error',

                    'Keranjang masih kosong.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Payment Methods
        |--------------------------------------------------------------------------
        */

        $methods = PaymentMethod::where(

            'is_active',

            true

        )

            ->orderBy(
                'sort_order'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Return Checkout View
        |--------------------------------------------------------------------------
        */

        return view(

            'store.checkout',

            compact(

                'cart',

                'methods'

            )

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Create Order
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Get Cart
        |--------------------------------------------------------------------------
        */

        $cart = $this->cart(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Load Cart Relations
        |--------------------------------------------------------------------------
        */

        $cart->load([

            'items.product',

            'items.variant',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Cart
        |--------------------------------------------------------------------------
        */

        if (

            $cart->items->isEmpty()

        ) {

            return redirect()

                ->route(
                    'cart.index'
                )

                ->with(

                    'error',

                    'Keranjang kosong.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Checkout Request
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([


            /*
            |--------------------------------------------------------------------------
            | Customer Information
            |--------------------------------------------------------------------------
            */

            'customer_name' => [

                'required',

                'string',

                'max:100',

            ],


            'customer_email' => [

                'required',

                'email',

                'max:255',

            ],


            'customer_phone' => [

                'required',

                'string',

                'max:30',

            ],


            /*
            |--------------------------------------------------------------------------
            | Shipping Destination
            |--------------------------------------------------------------------------
            */

            'destination_id' => [

                'required',

                'integer',

            ],


            'destination_name' => [

                'required',

                'string',

                'max:255',

            ],


            /*
            |--------------------------------------------------------------------------
            | Shipping Address
            |--------------------------------------------------------------------------
            */

            'shipping_address' => [

                'required',

                'string',

                'max:1000',

            ],


            /*
            |--------------------------------------------------------------------------
            | Shipping Service
            |--------------------------------------------------------------------------
            */

            'shipping_courier' => [

                'required',

                'string',

                'max:50',

            ],


            'shipping_service' => [

                'required',

                'string',

                'max:100',

            ],


            'shipping_etd' => [

                'nullable',

                'string',

                'max:100',

            ],


            /*
            |--------------------------------------------------------------------------
            | Shipping Weight
            |--------------------------------------------------------------------------
            |
            | Nilai ini diterima untuk kompatibilitas form checkout,
            | tetapi perhitungan final tetap dilakukan dari cart di server.
            |
            */

            'shipping_weight' => [

                'nullable',

                'integer',

                'min:1',

            ],


            /*
            |--------------------------------------------------------------------------
            | Payment Method
            |--------------------------------------------------------------------------
            */

            'payment_method_id' => [

                'required',

                'exists:payment_methods,id',

            ],


            /*
            |--------------------------------------------------------------------------
            | Promo Code
            |--------------------------------------------------------------------------
            */

            'promo_code' => [

                'nullable',

                'string',

                'max:60',

            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Shipping Data
        |--------------------------------------------------------------------------
        */

        $data['destination_id'] =

            (int) $data['destination_id'];


        $data['destination_name'] =

            trim(
                $data['destination_name']
            );


        $data['shipping_courier'] =

            strtolower(

                trim(
                    $data['shipping_courier']
                )

            );


        $data['shipping_service'] =

            trim(
                $data['shipping_service']
            );


        $data['shipping_etd'] =

            isset(
                $data['shipping_etd']
            )

                ?

                trim(
                    $data['shipping_etd']
                )

                :

                null;


        /*
        |--------------------------------------------------------------------------
        | Get Active Payment Method
        |--------------------------------------------------------------------------
        */

        $method = PaymentMethod::where(

            'id',

            $data[
                'payment_method_id'
            ]

        )

            ->where(

                'is_active',

                true

            )

            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Calculate Cart Subtotal
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;


        foreach (

            $cart->items

            as

            $item

        ) {

            /*
            |--------------------------------------------------------------------------
            | Validate Product
            |--------------------------------------------------------------------------
            */

            if (

                !$item->product

            ) {

                return back()

                    ->withInput()

                    ->with(

                        'error',

                        'Salah satu produk di keranjang sudah tidak tersedia.'

                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Determine Current Price
            |--------------------------------------------------------------------------
            */

            $price =

                $item->variant?->price

                ?:

                $item->product->current_price;


            /*
            |--------------------------------------------------------------------------
            | Add Subtotal
            |--------------------------------------------------------------------------
            */

            $subtotal +=

                (float) $price

                *

                (int) $item->quantity;

        }


        /*
        |--------------------------------------------------------------------------
        | Promo / Discount
        |--------------------------------------------------------------------------
        */

        $discountTotal = 0;


        if (

            !empty(
                $data['promo_code']
            )

        ) {

            /*
            |--------------------------------------------------------------------------
            | Get Promo
            |--------------------------------------------------------------------------
            */

            $promo = Discount::where(

                'code',

                $data[
                    'promo_code'
                ]

            )

                ->where(

                    'is_active',

                    true

                )

                ->first();


            /*
            |--------------------------------------------------------------------------
            | Validate Promo
            |--------------------------------------------------------------------------
            */

            if (

                !$promo

                ||

                (

                    $promo->starts_at

                    &&

                    now()->lt(

                        $promo->starts_at

                    )

                )

                ||

                (

                    $promo->ends_at

                    &&

                    now()->gt(

                        $promo->ends_at

                    )

                )

                ||

                (

                    $subtotal

                    <

                    (float)

                    $promo->minimum_order

                )

            ) {

                return back()

                    ->withInput()

                    ->withErrors([

                        'promo_code' =>

                            'Kode promo tidak valid atau belum memenuhi minimum belanja.',

                    ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Promo Discount
            |--------------------------------------------------------------------------
            */

            if (

                $promo->type ===

                'percent'

            ) {

                $discountTotal =

                    $subtotal

                    *

                    (

                        (float)

                        $promo->value

                        /

                        100

                    );

            } else {

                $discountTotal =

                    (float)

                    $promo->value;

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Discount Greater Than Subtotal
            |--------------------------------------------------------------------------
            */

            $discountTotal = min(

                $discountTotal,

                $subtotal

            );

        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Total Shipping Weight
        |--------------------------------------------------------------------------
        |
        | Semua berat menggunakan satuan gram.
        |
        | Rumus:
        |
        | product_weight × quantity
        |
        |--------------------------------------------------------------------------
        */

        $totalWeight = 0;


        foreach (

            $cart->items

            as

            $item

        ) {

            /*
            |--------------------------------------------------------------------------
            | Product Weight
            |--------------------------------------------------------------------------
            */

            $productWeight =

                (int)

                (

                    $item
                        ->product
                        ->weight

                    ?? 0

                );


            /*
            |--------------------------------------------------------------------------
            | Add Total Weight
            |--------------------------------------------------------------------------
            */

            $totalWeight +=

                $productWeight

                *

                (int)

                $item->quantity;

        }


        /*
        |--------------------------------------------------------------------------
        | Minimum Safe Shipping Weight
        |--------------------------------------------------------------------------
        |
        | Jangan sampai order tersimpan dengan berat 0 jika produk
        | ternyata memiliki berat.
        |
        |--------------------------------------------------------------------------
        */

        if (

            $totalWeight < 1

        ) {

            return back()

                ->withInput()

                ->with(

                    'error',

                    'Berat produk belum tersedia. Silakan hubungi admin sebelum melanjutkan checkout.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Calculate Shipping Cost
        |--------------------------------------------------------------------------
        |
        | Untuk sementara sistem tetap menggunakan aturan ongkir
        | yang sudah digunakan Zalina:
        |
        | subtotal >= free_shipping_minimum
        |     => gratis
        |
        | subtotal < free_shipping_minimum
        |     => Rp15.000
        |
        | IMPORTANT:
        |
        | Nilai shipping_cost dari browser TIDAK dipercaya.
        | Ongkir final dihitung server.
        |
        |--------------------------------------------------------------------------
        */

        $freeShippingEnabled =

            (string) Setting::value(
                'free_shipping_enabled',
                '0'
            ) === '1';


        $shippingThreshold =

            (float)

            Setting::value(

                'free_shipping_minimum',

                250000

            );


        /*
        |--------------------------------------------------------------------------
        | Biaya ongkir reguler (bukan gratis)
        |--------------------------------------------------------------------------
        |
        | Sebelumnya nilai ini di-hardcode Rp15.000 dan TIDAK terhubung
        | ke pengaturan admin (Setting: shipping_cost). Sekarang diambil
        | dari Setting supaya kalau admin ganti ongkir reguler di panel
        | Setting, nilainya langsung berlaku saat checkout.
        |
        |--------------------------------------------------------------------------
        */

        $shippingCost =

            (float)

            Setting::value(

                'shipping_cost',

                15000

            );


        /*
        |--------------------------------------------------------------------------
        | Apakah pesanan ini berhak gratis ongkir?
        |--------------------------------------------------------------------------
        |
        | BUG FIX: sebelumnya toggle "free_shipping_enabled" dari admin
        | tidak pernah dicek sama sekali. Selama subtotal >= minimum,
        | ongkir SELALU digratiskan walau admin sudah mematikan fitur
        | gratis ongkir di panel Setting. Sekarang toggle ini menjadi
        | syarat utama sebelum threshold subtotal dicek.
        |
        |--------------------------------------------------------------------------
        */

        $isFreeShipping =

            $freeShippingEnabled

            &&

            $subtotal >= $shippingThreshold;


        $shipping =

            $isFreeShipping

                ?

                0

                :

                $shippingCost;


        /*
        |--------------------------------------------------------------------------
        | Create Order Transaction
        |--------------------------------------------------------------------------
        */

        try {

            $order = DB::transaction(

                function () use (

                    $cart,

                    $data,

                    $method,

                    $subtotal,

                    $discountTotal,

                    $shipping,

                    $totalWeight

                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | Calculate Grand Total
                    |--------------------------------------------------------------------------
                    */
                    /*
                    | Biaya admin dibaca dari Setting (key: admin_fee) supaya bisa
                    | diatur dari panel admin. Default Rp2.000 kalau belum diisi.
                    | Nilainya disimpan di orders.admin_fee sebagai snapshot.
                    */
                    $adminFee = max(
                        0,
                        (float) Setting::value('admin_fee', 2000)
                    );

$grandTotal =

    $subtotal

    -

    $discountTotal

    +

    $shipping

    +

    $adminFee;

                    /*
                    |--------------------------------------------------------------------------
                    | Generate Unique Order Number
                    |--------------------------------------------------------------------------
                    */

                    do {

                        $orderNumber =

                            'ZLH-'

                            .

                            now()->format(

                                'Ymd'

                            )

                            .

                            '-'

                            .

                            strtoupper(

                                Str::random(

                                    6

                                )

                            );

                    } while (

                        Order::where(

                            'order_number',

                            $orderNumber

                        )->exists()

                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Order
                    |--------------------------------------------------------------------------
                    */

                    $order = Order::create([


                        /*
                        |--------------------------------------------------------------------------
                        | User
                        |--------------------------------------------------------------------------
                        */

                        'user_id' =>

                            session(

                                'zalina_user_id'

                            ),


                        /*
                        |--------------------------------------------------------------------------
                        | Order Number
                        |--------------------------------------------------------------------------
                        */

                        'order_number' =>

                            $orderNumber,


                        /*
                        |--------------------------------------------------------------------------
                        | Customer Information
                        |--------------------------------------------------------------------------
                        */

                        'customer_name' =>

                            $data[
                                'customer_name'
                            ],


                        'customer_email' =>

                            $data[
                                'customer_email'
                            ],


                        'customer_phone' =>

                            $data[
                                'customer_phone'
                            ],


                        /*
                        |--------------------------------------------------------------------------
                        | Shipping Address
                        |--------------------------------------------------------------------------
                        */

                        'shipping_address' =>

                            $data[
                                'shipping_address'
                            ],


                        /*
                        |--------------------------------------------------------------------------
                        | Shipping Destination
                        |--------------------------------------------------------------------------
                        */

                        'destination_id' =>

                            $data[
                                'destination_id'
                            ],


                        'destination_name' =>

                            $data[
                                'destination_name'
                            ],


                        /*
                        |--------------------------------------------------------------------------
                        | Shipping Service
                        |--------------------------------------------------------------------------
                        */

                        'shipping_courier' =>

                            $data[
                                'shipping_courier'
                            ],


                        'shipping_service' =>

                            $data[
                                'shipping_service'
                            ],


                        'shipping_etd' =>

                            $data[
                                'shipping_etd'
                            ],


                        /*
                        |--------------------------------------------------------------------------
                        | Shipping Weight
                        |--------------------------------------------------------------------------
                        */

                        'shipping_weight' =>

                            $totalWeight,


                        /*
                        |--------------------------------------------------------------------------
                        | Tracking
                        |--------------------------------------------------------------------------
                        |
                        | AWB/resi belum tersedia pada saat checkout.
                        | Akan diisi setelah proses pengiriman berhasil.
                        |
                        |--------------------------------------------------------------------------
                        */

                        'tracking_number' =>

                            null,


                        /*
                        |--------------------------------------------------------------------------
                        | Order Price
                        |--------------------------------------------------------------------------
                        */

                        'subtotal' =>

                            $subtotal,


                        'discount_total' =>

                            $discountTotal,


                        'shipping_total' =>

                            $shipping,

                        'admin_fee' =>
                            $adminFee,


                        'grand_total' =>

                            $grandTotal,


                        /*
                        |--------------------------------------------------------------------------
                        | Main Order Status
                        |--------------------------------------------------------------------------
                        */

                        'status' =>

                            'pending',


                        /*
                        |--------------------------------------------------------------------------
                        | Shipping Status
                        |--------------------------------------------------------------------------
                        */

                        'shipping_status' =>

                            'waiting',


                        /*
                        |--------------------------------------------------------------------------
                        | Payment Status
                        |--------------------------------------------------------------------------
                        */

                        'payment_status' =>

                            'unpaid',

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Create Order Items
                    |--------------------------------------------------------------------------
                    */

                    foreach (

                        $cart->items

                        as

                        $item

                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | Get Product
                        |--------------------------------------------------------------------------
                        */

                        $product =

                            $item
                                ->product;


                        /*
                        |--------------------------------------------------------------------------
                        | Get Variant
                        |--------------------------------------------------------------------------
                        */

                        $variant =

                            $item
                                ->variant;


                        /*
                        |--------------------------------------------------------------------------
                        | Safety Check
                        |--------------------------------------------------------------------------
                        */

                        if (

                            !$product

                        ) {

                            throw new \RuntimeException(

                                'Produk tidak ditemukan saat proses checkout.'

                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Price Snapshot
                        |--------------------------------------------------------------------------
                        */

                        $price =

                            $variant?->price

                            ?:

                            $product
                                ->current_price;


                        /*
                        |--------------------------------------------------------------------------
                        | Variant Stock Validation & Reservation
                        |--------------------------------------------------------------------------
                        |
                        | BUG FIX: sebelumnya stok hanya divalidasi (dicek cukup
                        | atau tidak) tapi TIDAK PERNAH DIKURANGI setelah order
                        | dibuat. Akibatnya stok di halaman shop/produk tidak
                        | pernah berkurang meski sudah ada pesanan, dan dua
                        | pembeli bisa sama-sama "lolos" membeli unit stok
                        | terakhir yang sama (oversell).
                        |
                        | Baris ini sekarang:
                        | - Mengambil ulang baris produk/varian dengan
                        |   lockForUpdate DI DALAM transaksi, supaya dua
                        |   checkout yang berjalan bersamaan tidak membaca
                        |   angka stok yang sama-sama masih "lama".
                        | - Mengurangi stok pakai decrement begitu order item
                        |   berhasil divalidasi, jadi stok produk selalu
                        |   sinkron dengan pesanan yang benar-benar dibuat.
                        |
                        */

                        if (

                            $variant

                        ) {

                            $lockedVariant =
                                $item->variant()
                                    ->lockForUpdate()
                                    ->first();

                            if (
                                !$lockedVariant
                                ||
                                !$lockedVariant->is_active
                            ) {

                                throw new \RuntimeException(

                                    'Varian produk yang dipilih sudah tidak tersedia.'

                                );

                            }


                            if (

                                (int) $lockedVariant->stock

                                <

                                (int) $item->quantity

                            ) {

                                throw new \RuntimeException(

                                    'Stok varian '.$variant->name.' tidak mencukupi.'

                                );

                            }

                            $lockedVariant->decrement(
                                'stock',
                                (int) $item->quantity
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | BUG FIX: product.stock TIDAK IKUT TERSINKRON SETELAH CHECKOUT
                            |--------------------------------------------------------------------------
                            |
                            | decrement() melakukan UPDATE langsung ke database dan TIDAK
                            | memicu event Eloquent (updating/updated) — jadi observer/boot
                            | hook apa pun yang mengandalkan event model (mis. auto-sync di
                            | ProductVariant) tidak akan pernah terpanggil dari sini.
                            |
                            | Sebelumnya kolom stock produk induk sama sekali tidak
                            | disentuh setelah checkout, jadi begitu ada order lewat
                            | varian, product.stock langsung basi lagi walau baru saja
                            | disinkronkan lewat form admin/command sync. Baris ini
                            | memaksa product.stock ikut sinkron di titik yang sama saat
                            | stok varian benar-benar berkurang.
                            |
                            */

                            $product->syncStockFromVariants();

                        } else {

                            $lockedProduct =
                                $item->product()
                                    ->lockForUpdate()
                                    ->first();

                            /*
                            |--------------------------------------------------------------------------
                            | BUG FIX: Produk Nonaktif Ikut Lolos Checkout
                            |--------------------------------------------------------------------------
                            |
                            | Sebelumnya cabang ini (produk tanpa varian) hanya
                            | mengecek stok cukup atau tidak, TIDAK pernah
                            | mengecek is_active seperti yang sudah dilakukan
                            | untuk varian di atas. Akibatnya produk yang baru
                            | saja dinonaktifkan admin (mis. ditarik dari
                            | penjualan) masih bisa lolos checkout selama
                            | stoknya masih > 0 di database.
                            |
                            */

                            if (

                                !$lockedProduct
                                ||
                                !$lockedProduct->is_active

                            ) {

                                throw new \RuntimeException(

                                    'Produk '.$product->name.' sudah tidak tersedia.'

                                );

                            }

                            if (

                                (int) $lockedProduct->stock

                                <

                                (int) $item->quantity

                            ) {

                                throw new \RuntimeException(

                                    'Stok produk '.$product->name.' tidak mencukupi.'

                                );

                            }

                            $lockedProduct->decrement(
                                'stock',
                                (int) $item->quantity
                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Product Weight Snapshot
                        |--------------------------------------------------------------------------
                        |
                        | Berat per unit.
                        |
                        |--------------------------------------------------------------------------
                        */

                        $weight =

                            (int)

                            (

                                $product->weight

                                ?? 0

                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Variant Name Snapshot
                        |--------------------------------------------------------------------------
                        */

                        $variantName =

                            $variant?->name;


                        /*
                        |--------------------------------------------------------------------------
                        | SKU Snapshot
                        |--------------------------------------------------------------------------
                        */

                        $sku =

                            $variant?->sku

                            ?:

                            $product->sku;


                        /*
                        |--------------------------------------------------------------------------
                        | Create Order Item
                        |--------------------------------------------------------------------------
                        */

                        OrderItem::create([


                            /*
                            |--------------------------------------------------------------------------
                            | Order
                            |--------------------------------------------------------------------------
                            */

                            'order_id' =>

                                $order->id,


                            /*
                            |--------------------------------------------------------------------------
                            | Product
                            |--------------------------------------------------------------------------
                            */

                            'product_id' =>

                                $item
                                    ->product_id,


                            /*
                            |--------------------------------------------------------------------------
                            | Variant
                            |--------------------------------------------------------------------------
                            */

                            'variant_id' =>

                                $item
                                    ->variant_id,


                            /*
                            |--------------------------------------------------------------------------
                            | Product Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'product_name' =>

                                $product->name,


                            /*
                            |--------------------------------------------------------------------------
                            | Variant Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'variant_name' =>

                                $variantName,


                            /*
                            |--------------------------------------------------------------------------
                            | SKU Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'sku' =>

                                $sku,


                            /*
                            |--------------------------------------------------------------------------
                            | Price Snapshot
                            |--------------------------------------------------------------------------
                            */

                            'price' =>

                                $price,


                            /*
                            |--------------------------------------------------------------------------
                            | Quantity
                            |--------------------------------------------------------------------------
                            */

                            'quantity' =>

                                (int)

                                $item
                                    ->quantity,


                            /*
                            |--------------------------------------------------------------------------
                            | Item Subtotal
                            |--------------------------------------------------------------------------
                            */

                            'subtotal' =>

                                (float)

                                $price

                                *

                                (int)

                                $item
                                    ->quantity,


                            /*
                            |--------------------------------------------------------------------------
                            | Weight
                            |--------------------------------------------------------------------------
                            |
                            | Berat per unit.
                            |
                            |--------------------------------------------------------------------------
                            */

                            'weight' =>

                                $weight,

                        ]);

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create Payment
                    |--------------------------------------------------------------------------
                    */

                    Payment::create([

                        'order_id' =>

                            $order->id,


                        'payment_method_id' =>

                            $method->id,


                        'amount_expected' =>

                            $order->grand_total,


                        'status' =>

                            'pending',

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Clear Cart
                    |--------------------------------------------------------------------------
                    */

                    $cart

                        ->items()

                        ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | Return Order
                    |--------------------------------------------------------------------------
                    */

                    return $order;

                }

            );


        } catch (

            \Throwable

            $exception

        ) {


            /*
            |--------------------------------------------------------------------------
            | Log Error
            |--------------------------------------------------------------------------
            */

            Log::error(

                'Checkout gagal',

                [

                    'message' =>

                        $exception
                            ->getMessage(),


                    'trace' =>

                        $exception
                            ->getTraceAsString(),

                ]

            );


            return back()

                ->withInput()

                ->with(

                    'error',

                    'Pesanan gagal dibuat. Silakan coba kembali.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | WhatsApp Notification - New Order
        |--------------------------------------------------------------------------
        |
        | Mengirim notifikasi pesanan baru ke nomor admin yang ada
        | pada config/whatsapp.php.
        |
        | Notifikasi gagal tidak membatalkan pesanan yang sudah berhasil
        | dibuat. Kegagalan hanya dicatat ke log Laravel.
        |
        */
        try {
            // Daftar produk supaya admin langsung tahu apa yang harus disiapkan
            // (nama, varian/warna, jumlah, harga) tanpa membuka dashboard.
            // load() (bukan loadMissing) supaya item yang baru dibuat pasti ikut.
            $order->load('items');

            $whatsappProductLines = $order->items
                ->values()
                ->map(function ($item, $index) {
                    $quantity = (int) $item->quantity;
                    $price = (float) $item->price;
                    $lineTotal = (float) ($item->subtotal ?? ($price * $quantity));

                    $lines = [
                        ($index + 1) . '. ' . ($item->product_name ?: 'Produk'),
                        '   Varian/Warna: ' . ($item->variant_name ?: '-'),
                        '   Jumlah: ' . $quantity
                            . ' x Rp ' . number_format($price, 0, ',', '.')
                            . ' = Rp ' . number_format($lineTotal, 0, ',', '.'),
                    ];

                    if (filled($item->sku)) {
                        $lines[] = '   SKU: ' . $item->sku;
                    }

                    return implode("\n", $lines);
                })
                ->all();

            $whatsappMessage = implode("\n", [
                '🛍️ PESANAN BARU ZALINA FASHION',
                '',
                'Nomor Pesanan: ' . $order->order_number,
                'Nama: ' . $order->customer_name,
                'Telepon: ' . $order->customer_phone,
                'Email: ' . $order->customer_email,
                '',
                'Tujuan: ' . $order->destination_name,
                'Alamat: ' . $order->shipping_address,
                'Kurir: ' . strtoupper($order->shipping_courier),
                'Layanan: ' . $order->shipping_service,
                'Estimasi: ' . ($order->shipping_etd ?: '-'),
                '',
                'DAFTAR PRODUK (' . (int) $order->items->sum('quantity') . ' pcs)',
                implode("\n\n", $whatsappProductLines ?: ['-']),
                '',
                'Subtotal: Rp ' . number_format((float) $order->subtotal, 0, ',', '.'),
                'Diskon: Rp ' . number_format((float) $order->discount_total, 0, ',', '.'),
                'Ongkir: ' . (
                    (float) $order->shipping_total <= 0
                        ? 'GRATIS ONGKIR 🎉'
                        : 'Rp ' . number_format((float) $order->shipping_total, 0, ',', '.')
                ),
                'Biaya Admin: Rp ' . number_format((float) $order->admin_fee, 0, ',', '.'),
                'Total: Rp ' . number_format((float) $order->grand_total, 0, ',', '.'),
                '',
                'Status Pembayaran: ' . $order->payment_status,
                'Status Pesanan: ' . $order->status,
                '',
                'Silakan cek dashboard admin untuk detail pesanan.',
            ]);

            $whatsappResponse = app(WhatsAppService::class)
                ->sendToAdmin($whatsappMessage);

            Log::info('Notifikasi WhatsApp pesanan berhasil dikirim', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'whatsapp_status' => $whatsappResponse->status(),
                'whatsapp_response' => $whatsappResponse->json(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Notifikasi WhatsApp pesanan gagal dikirim', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $exception->getMessage(),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Save Guest Order ID To Session
        |--------------------------------------------------------------------------
        */

        $orderIds =

            $request

                ->session()

                ->get(

                    'zalina_order_ids',

                    []

                );


        $orderIds[] =

            $order->id;


        $request

            ->session()

            ->put(

                'zalina_order_ids',

                array_values(

                    array_unique(

                        $orderIds

                    )

                )

            );


        /*
        |--------------------------------------------------------------------------
        | Redirect To Payment
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Redirect Flash Message
        |--------------------------------------------------------------------------
        |
        | Sengaja memakai key 'info', BUKAN 'success'. Halaman pembayaran
        | menampilkan pop-up ceklist "berhasil" untuk key session('success'),
        | dan pop-up itu seharusnya hanya muncul setelah bukti pembayaran
        | BERHASIL diupload (lihat uploadProof()), bukan begitu order baru
        | dibuat/tombol "Buat Pesanan" diklik. Order tetap dibuat di sini
        | (supaya stok bisa langsung direservasi & halaman pembayaran bisa
        | dituju), tapi ceklist suksesnya ditahan sampai bukti pembayaran
        | benar-benar terkirim.
        |
        */

        return redirect()

            ->route(

                'payment.show',

                $order

            )

            ->with(

                'info',

                'Pesanan berhasil dibuat. Silakan lakukan pembayaran dan upload bukti.'

            );

    }


    /*
    |--------------------------------------------------------------------------
    | Payment Page
    |--------------------------------------------------------------------------
    */

    public function payment(
        Order $order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        $order->load([

            'items',

            'payment',

            'payment.method',

        ]);


        return view(

            'store.payment',

            compact(

                'order'

            )

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Customer Order Detail
    |--------------------------------------------------------------------------
    */

    public function detail(
        Order $order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        $order->load([

            'items',

            'payment',

            'payment.method',

        ]);


        return view(

            'store.order-detail',

            compact(

                'order'

            )

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Receipt HTML
    |--------------------------------------------------------------------------
    */

    public function receipt(
        Order $order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        $order->load([

            'items',

            'payment',

            'payment.method',

        ]);


        $payment =

            $order
                ->payment;


        /*
        |--------------------------------------------------------------------------
        | Receipt Only Available After Payment Verified
        |--------------------------------------------------------------------------
        */

        if (

            !$payment

            ||

            $payment->status !==

            'verified'

        ) {

            return redirect()

                ->route(

                    'payment.show',

                    $order

                )

                ->with(

                    'error',

                    'Receipt tersedia setelah pembayaran dikonfirmasi admin.'

                );

        }


        return view(

            'store.receipt',

            compact(

                'order',

                'payment'

            )

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Download Receipt PDF
    |--------------------------------------------------------------------------
    */

    public function downloadReceipt(
        Order $order
    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Load Relations
        |--------------------------------------------------------------------------
        */

        $order->load([

            'items',

            'payment',

            'payment.method',

        ]);


        $payment =

            $order
                ->payment;


        /*
        |--------------------------------------------------------------------------
        | Payment Not Found
        |--------------------------------------------------------------------------
        */

        if (

            !$payment

        ) {

            return redirect()

                ->route(

                    'payment.show',

                    $order

                )

                ->with(

                    'error',

                    'Data pembayaran tidak ditemukan.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Receipt Only Available After Verification
        |--------------------------------------------------------------------------
        */

        if (

            $payment->status !==

            'verified'

        ) {

            return redirect()

                ->route(

                    'payment.show',

                    $order

                )

                ->with(

                    'error',

                    'Receipt belum tersedia karena pembayaran belum dikonfirmasi admin.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        try {

            $pdf = Pdf::loadView(

                'store.receipt-pdf',

                [

                    'order' =>

                        $order,


                    'payment' =>

                        $payment,

                ]

            );


            /*
            |--------------------------------------------------------------------------
            | PDF Paper
            |--------------------------------------------------------------------------
            */

            $pdf->setPaper(

                'a4',

                'portrait'

            );


            /*
            |--------------------------------------------------------------------------
            | PDF Font
            |--------------------------------------------------------------------------
            */

            $pdf->setOption(

                'defaultFont',

                'DejaVu Sans'

            );


            /*
            |--------------------------------------------------------------------------
            | File Name
            |--------------------------------------------------------------------------
            */

            $fileName =

                'Receipt-'

                .

                $order->order_number

                .

                '.pdf';


            /*
            |--------------------------------------------------------------------------
            | Download PDF
            |--------------------------------------------------------------------------
            */

            return $pdf->download(

                $fileName

            );


        } catch (

            \Throwable

            $exception

        ) {


            /*
            |--------------------------------------------------------------------------
            | Log Error
            |--------------------------------------------------------------------------
            */

            Log::error(

                'PDF Receipt gagal dibuat',

                [

                    'order_id' =>

                        $order->id,


                    'order_number' =>

                        $order->order_number,


                    'message' =>

                        $exception
                            ->getMessage(),


                    'trace' =>

                        $exception
                            ->getTraceAsString(),

                ]

            );


            return redirect()

                ->route(

                    'receipt.show',

                    $order

                )

                ->with(

                    'error',

                    'PDF Receipt gagal dibuat. Silakan coba kembali.'

                );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Upload Payment Proof
    |--------------------------------------------------------------------------
    */

    public function upload(

        Request $request,

        Order $order

    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Get Payment
        |--------------------------------------------------------------------------
        */

        $payment =

            $order
                ->payment;


        /*
        |--------------------------------------------------------------------------
        | Payment Not Found
        |--------------------------------------------------------------------------
        */

        if (

            !$payment

        ) {

            abort(

                404,

                'Data pembayaran tidak ditemukan.'

            );

        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Upload After Verification
        |--------------------------------------------------------------------------
        */

        if (

            $payment->status ===

            'verified'

        ) {

            return back()

                ->with(

                    'error',

                    'Pembayaran sudah dikonfirmasi.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Payment Proof
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([


            /*
            |--------------------------------------------------------------------------
            | Sender Name
            |--------------------------------------------------------------------------
            */

            'sender_name' => [

                'required',

                'string',

                'max:100',

            ],


            /*
            |--------------------------------------------------------------------------
            | Sender Account
            |--------------------------------------------------------------------------
            */

            'sender_account' => [

                'nullable',

                'string',

                'max:100',

            ],


            /*
            |--------------------------------------------------------------------------
            | Paid Amount
            |--------------------------------------------------------------------------
            */

            'amount_paid' => [

                'required',

                'numeric',

                'min:1',

            ],


            /*
            |--------------------------------------------------------------------------
            | Payment Date
            |--------------------------------------------------------------------------
            */

            'paid_at' => [

                'required',

                'date',

            ],


            /*
            |--------------------------------------------------------------------------
            | Payment Proof
            |--------------------------------------------------------------------------
            */

            'proof' => [

                'required',

                'file',

                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',

                'max:10240',

            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Store Payment Proof
        |--------------------------------------------------------------------------
        */

        $path =

            $request

                ->file(

                    'proof'

                )

                ->store(

                    'payment-proofs',

                    'public'

                );


        /*
        |--------------------------------------------------------------------------
        | Update Payment
        |--------------------------------------------------------------------------
        */

        $payment->update([

            'sender_name' =>

                $data[
                    'sender_name'
                ],


            'sender_account' =>

                $data[
                    'sender_account'
                ],


            'amount_paid' =>

                $data[
                    'amount_paid'
                ],


            'paid_at' =>

                $data[
                    'paid_at'
                ],


            'proof_path' =>

                $path,


            'status' =>

                'under_review',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Update Order Payment Status
        |--------------------------------------------------------------------------
        */

        $order->update([

            'payment_status' =>

                'pending_verification',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return back()

            ->with(

                'success',

                'Bukti pembayaran berhasil dikirim. Menunggu konfirmasi admin.'

            );

    }


    /*
    |--------------------------------------------------------------------------
    | Confirm Order Delivered
    |--------------------------------------------------------------------------
    */

    public function confirmDelivered(

        Request $request,

        Order $order

    ) {

        /*
        |--------------------------------------------------------------------------
        | Ensure Order Owner
        |--------------------------------------------------------------------------
        */

        $this->ensureOwner(

            $order

        );


        /*
        |--------------------------------------------------------------------------
        | Already Delivered
        |--------------------------------------------------------------------------
        */

        if (

            $order->shipping_status ===

            'delivered'

        ) {

            return back()

                ->with(

                    'success',

                    'Pesanan ini sebelumnya sudah dikonfirmasi sampai.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Customer Can Confirm Only When Shipped
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [

            'handed_to_courier',

            'shipped',

        ];


        if (

            !in_array(

                $order->shipping_status,

                $allowedStatuses,

                true

            )

        ) {

            return back()

                ->with(

                    'error',

                    'Pesanan belum dalam status pengiriman.'

                );

        }


        /*
        |--------------------------------------------------------------------------
        | Update Order
        |--------------------------------------------------------------------------
        */

        $order->update([

            /*
            |--------------------------------------------------------------------------
            | Main Status
            |--------------------------------------------------------------------------
            */

            'status' =>

                'delivered',


            /*
            |--------------------------------------------------------------------------
            | Shipping Status
            |--------------------------------------------------------------------------
            */

            'shipping_status' =>

                'delivered',


            /*
            |--------------------------------------------------------------------------
            | Delivered Time
            |--------------------------------------------------------------------------
            */

            'delivered_at' =>

                now(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return back()

            ->with(

                'success',

                'Terima kasih. Pesanan telah berhasil dikonfirmasi sampai.'

            );

    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Order Owner
    |--------------------------------------------------------------------------
    */

    private function ensureOwner(

        Order $order

    ): void {

        /*
        |--------------------------------------------------------------------------
        | Get Logged In User
        |--------------------------------------------------------------------------
        */

        $userId =

            session(

                'zalina_user_id'

            );


        /*
        |--------------------------------------------------------------------------
        | Logged In User
        |--------------------------------------------------------------------------
        */

        if (

            $userId

        ) {

            if (

                (int)

                $order->user_id

                !==

                (int)

                $userId

            ) {

                abort(

                    403,

                    'Anda tidak memiliki akses ke pesanan ini.'

                );

            }


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Guest Order
        |--------------------------------------------------------------------------
        */

        $orderIds =

            session(

                'zalina_order_ids',

                []

            );


        /*
        |--------------------------------------------------------------------------
        | Check Guest Order Access
        |--------------------------------------------------------------------------
        */

        if (

            !in_array(

                (int) $order->id,

                array_map('intval', $orderIds),

                true

            )

        ) {

            if (is_null($order->user_id)) {

                return;

            }


            abort(

                403,

                'Anda tidak memiliki akses ke pesanan ini.'

            );

        }

    }
}