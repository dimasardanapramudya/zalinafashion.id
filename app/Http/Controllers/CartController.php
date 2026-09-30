<?php

namespace App\Http\Controllers;

use App\Models\{Cart, CartItem, Product, ProductVariant, Setting};
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Ambil / buat cart milik user atau guest.
     */
    private function cart(Request $r): Cart
    {
        $sid = $r->session()->getId();
        $uid = session('zalina_user_id');

        if ($uid) {
            $cart = Cart::firstOrCreate(
                ['user_id' => $uid],
                ['session_id' => $sid]
            );

            if ($cart->session_id !== $sid) {
                $cart->update([
                    'session_id' => $sid,
                ]);
            }

            return $cart;
        }

        return Cart::firstOrCreate([
            'session_id' => $sid,
            'user_id' => null,
        ]);
    }

    /**
     * Halaman keranjang.
     */
    public function index(Request $r)
    {
        $cart = $this->cart($r);

        $cart->load([
            'items.product',
            'items.variant',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Hitung Subtotal Cart
        |--------------------------------------------------------------------------
        |
        | Dihitung dengan cara yang sama seperti CheckoutController, supaya
        | angka progress gratis ongkir di halaman cart selalu konsisten
        | dengan subtotal yang nanti dipakai saat checkout.
        |
        |--------------------------------------------------------------------------
        */

        $cartSubtotal = 0;

        foreach ($cart->items as $item) {
            if (!$item->product) {
                continue;
            }

            $price = $item->variant?->price
                ?: $item->product->current_price;

            $cartSubtotal += (float) $price * (int) $item->quantity;
        }

        /*
        |--------------------------------------------------------------------------
        | Pengaturan Gratis Ongkir (dari Admin > Setting)
        |--------------------------------------------------------------------------
        |
        | BUG FIX: sebelumnya halaman cart tidak menerima data Setting
        | sama sekali, sehingga progress bar gratis ongkir di blade
        | terpaksa hardcode dan tidak nyambung ke panel admin. Sekarang
        | nilai-nilai ini dikirim ke view supaya progress bar, sisa
        | belanja menuju gratis ongkir, dan status aktif/nonaktifnya
        | selalu mengikuti Setting yang admin ubah.
        |
        |--------------------------------------------------------------------------
        */

        $freeShippingEnabled = (string) Setting::value(
            'free_shipping_enabled',
            '0'
        ) === '1';

        $freeShippingMinimum = (float) Setting::value(
            'free_shipping_minimum',
            250000
        );

        $shippingCost = (float) Setting::value(
            'shipping_cost',
            15000
        );

        $freeShippingRemaining = $freeShippingEnabled
            ? max($freeShippingMinimum - $cartSubtotal, 0)
            : null;

        $freeShippingProgress = ($freeShippingEnabled && $freeShippingMinimum > 0)
            ? min(100, (int) round(($cartSubtotal / $freeShippingMinimum) * 100))
            : 0;

        return view('store.cart', compact(
            'cart',
            'cartSubtotal',
            'freeShippingEnabled',
            'freeShippingMinimum',
            'shippingCost',
            'freeShippingRemaining',
            'freeShippingProgress'
        ));
    }

    /**
     * Tambahkan produk ke keranjang.
     *
     * Jika produk mempunyai variant/warna aktif,
     * maka variant_id wajib dipilih.
     */
    public function add(Request $r, Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi dasar
        |--------------------------------------------------------------------------
        */
        $d = $r->validate([
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Cek produk aktif
        |--------------------------------------------------------------------------
        */
        if (!$product->is_active) {
            return back()->with(
                'error',
                'Produk tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil variant aktif milik produk
        |--------------------------------------------------------------------------
        */
        $activeVariants = ProductVariant::where('product_id', $product->id)
            ->where('is_active', true)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Produk dengan variant wajib memilih warna
        |--------------------------------------------------------------------------
        */
        if ($activeVariants->isNotEmpty() && empty($d['variant_id'])) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Silakan pilih warna terlebih dahulu sebelum menambahkan produk ke keranjang.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Variant yang dipilih
        |--------------------------------------------------------------------------
        */
        $v = null;

        if (!empty($d['variant_id'])) {
            $v = ProductVariant::where('id', $d['variant_id'])
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if (!$v) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Warna / varian yang dipilih tidak valid.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Cek stok variant
            |--------------------------------------------------------------------------
            */
            if ($v->stock < 1) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Warna / varian yang dipilih sedang habis.'
                    );
            }

            if ($v->stock < $d['quantity']) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Stok warna / varian tidak mencukupi.'
                    );
            }
        } else {
            /*
            |--------------------------------------------------------------------------
            | Produk tanpa variant menggunakan stok utama
            |--------------------------------------------------------------------------
            */
            if ($product->stock < 1) {
                return back()->with(
                    'error',
                    'Stok produk tidak tersedia.'
                );
            }

            if ($product->stock < $d['quantity']) {
                return back()->with(
                    'error',
                    'Stok produk tidak mencukupi.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil cart
        |--------------------------------------------------------------------------
        */
        $cart = $this->cart($r);

        /*
        |--------------------------------------------------------------------------
        | Cari item berdasarkan produk + variant
        |--------------------------------------------------------------------------
        */
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $v?->id)
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Hitung quantity baru
        |--------------------------------------------------------------------------
        */
        $newQty = ($item?->quantity ?? 0) + $d['quantity'];

        /*
        |--------------------------------------------------------------------------
        | Tentukan stok tersedia
        |--------------------------------------------------------------------------
        */
        $available = $v?->stock ?? $product->stock;

        /*
        |--------------------------------------------------------------------------
        | Jangan sampai melebihi stok
        |--------------------------------------------------------------------------
        */
        if ($newQty > $available) {
            return back()->with(
                'error',
                'Jumlah melebihi stok tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update item lama atau buat item baru
        |--------------------------------------------------------------------------
        */
        if ($item) {
            $item->update([
                'quantity' => $newQty,
            ]);
        } else {
            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $v?->id,
                'quantity' => $d['quantity'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Notifikasi WhatsApp saat produk masuk keranjang
        |--------------------------------------------------------------------------
        */
        try {
            $price = $product->price
                ?? $product->selling_price
                ?? $product->sale_price
                ?? 0;

            $whatsappMessage = implode("\n", [
                '🛒 PRODUK MASUK KERANJANG ZALINA',
                '',
                'Nama Produk: ' . $product->name,
                'Jumlah Ditambahkan: ' . $d['quantity'],
                'Total Jumlah di Keranjang: ' . $newQty,
                'Warna / Varian: ' . ($v?->name ?? '-'),
                'Harga Satuan: Rp ' . number_format((float) $price, 0, ',', '.'),
                'Subtotal Item: Rp ' . number_format(
                    (float) $price * $newQty,
                    0,
                    ',',
                    '.'
                ),
                '',
                'Cart ID: ' . $cart->id,
                'Status: Produk berhasil masuk keranjang',
                '',
                'Silakan cek keranjang atau dashboard admin.',
            ]);

            $whatsappResponse = app(WhatsAppService::class)
                ->sendToAdmin($whatsappMessage);

            Log::info(
                'Notifikasi WhatsApp keranjang berhasil dikirim',
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_added' => $d['quantity'],
                    'quantity_total' => $newQty,
                    'whatsapp_status' => $whatsappResponse->status(),
                    'whatsapp_response' => $whatsappResponse->json(),
                ]
            );
        } catch (\Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Gagal WhatsApp tidak membatalkan proses keranjang
            |--------------------------------------------------------------------------
            */
            Log::warning(
                'Notifikasi WhatsApp keranjang gagal dikirim',
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'message' => $exception->getMessage(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Berhasil
        |--------------------------------------------------------------------------
        */
        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                $v
                    ? 'Produk dengan warna ' . $v->name . ' berhasil masuk ke keranjang.'
                    : 'Produk masuk ke keranjang.'
            );
    }

    /**
     * Update quantity item keranjang.
     */
    public function update(Request $r, CartItem $item)
    {
        abort_unless(
            $this->owns($r, $item),
            403
        );

        $d = $r->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ambil stok berdasarkan variant jika ada
        |--------------------------------------------------------------------------
        */
        $item->load([
            'product',
            'variant',
        ]);

        $available = $item->variant?->stock
            ?? $item->product?->stock
            ?? 0;

        if ($d['quantity'] > $available) {
            return back()->with(
                'error',
                'Jumlah melebihi stok.'
            );
        }

        $oldQty = $item->quantity;

        $item->update([
            'quantity' => $d['quantity'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notifikasi WhatsApp saat quantity diperbarui
        |--------------------------------------------------------------------------
        */
        try {
            $productName = $item->product?->name ?? 'Produk';
            $variantName = $item->variant?->name ?? '-';

            $whatsappMessage = implode("\n", [
                '🔄 QUANTITY KERANJANG DIPERBARUI',
                '',
                'Nama Produk: ' . $productName,
                'Warna / Varian: ' . $variantName,
                'Jumlah Sebelumnya: ' . $oldQty,
                'Jumlah Sekarang: ' . $d['quantity'],
                'Cart ID: ' . $item->cart_id,
                '',
                'Silakan cek keranjang atau dashboard admin.',
            ]);

            $whatsappResponse = app(WhatsAppService::class)
                ->sendToAdmin($whatsappMessage);

            Log::info(
                'Notifikasi WhatsApp update keranjang berhasil dikirim',
                [
                    'cart_id' => $item->cart_id,
                    'cart_item_id' => $item->id,
                    'whatsapp_status' => $whatsappResponse->status(),
                    'whatsapp_response' => $whatsappResponse->json(),
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning(
                'Notifikasi WhatsApp update keranjang gagal dikirim',
                [
                    'cart_id' => $item->cart_id,
                    'cart_item_id' => $item->id,
                    'message' => $exception->getMessage(),
                ]
            );
        }

        return back()->with(
            'success',
            'Keranjang diperbarui.'
        );
    }

    /**
     * Hapus item dari keranjang.
     */
    public function destroy(Request $r, CartItem $item)
    {
        abort_unless(
            $this->owns($r, $item),
            403
        );

        $item->load([
            'product',
            'variant',
        ]);

        $productName = $item->product?->name ?? 'Produk';
        $variantName = $item->variant?->name ?? '-';
        $deletedQty = $item->quantity;
        $cartId = $item->cart_id;

        $item->delete();

        /*
        |--------------------------------------------------------------------------
        | Notifikasi WhatsApp saat item dihapus
        |--------------------------------------------------------------------------
        */
        try {
            $whatsappMessage = implode("\n", [
                '🗑️ PRODUK DIHAPUS DARI KERANJANG',
                '',
                'Nama Produk: ' . $productName,
                'Warna / Varian: ' . $variantName,
                'Jumlah Dihapus: ' . $deletedQty,
                'Cart ID: ' . $cartId,
                '',
                'Silakan cek keranjang atau dashboard admin.',
            ]);

            $whatsappResponse = app(WhatsAppService::class)
                ->sendToAdmin($whatsappMessage);

            Log::info(
                'Notifikasi WhatsApp hapus keranjang berhasil dikirim',
                [
                    'cart_id' => $cartId,
                    'cart_item_id' => $item->id,
                    'whatsapp_status' => $whatsappResponse->status(),
                    'whatsapp_response' => $whatsappResponse->json(),
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning(
                'Notifikasi WhatsApp hapus keranjang gagal dikirim',
                [
                    'cart_id' => $cartId,
                    'cart_item_id' => $item->id,
                    'message' => $exception->getMessage(),
                ]
            );
        }

        return back()->with(
            'success',
            'Produk dihapus dari keranjang.'
        );
    }

    /**
     * Pastikan CartItem memang milik cart user / guest saat ini.
     */
    private function owns(Request $r, CartItem $item): bool
    {
        $cart = $this->cart($r);

        return (int) $item->cart_id === (int) $cart->id;
    }
}