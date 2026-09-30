<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        /*
        |--------------------------------------------------------------------------
        | Generate slug unik
        |--------------------------------------------------------------------------
        */

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $data['slug'] = $slug;

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        /*
        |--------------------------------------------------------------------------
        | Simpan gambar utama
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if (!$image->isValid()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'Gambar gagal diunggah. Silakan pilih gambar lain.',
                    ]);
            }

            $data['image'] = $image->store('products', 'public');

            Log::info('PRODUCT IMAGE STORED', [
                'filename' => $image->getClientOriginalName(),
                'path' => $data['image'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan produk utama
        |--------------------------------------------------------------------------
        */

        $product = Product::create($data);

        /*
        |--------------------------------------------------------------------------
        | Simpan varian produk
        |--------------------------------------------------------------------------
        */

        $variants = $request->input('variants', []);

        foreach ($variants as $variantKey => $variantData) {
            $color = trim($variantData['color'] ?? '');

            if ($color === '') {
                continue;
            }

            $product->variants()->create([
                'image' => $this->resolveVariantImage($request, $variantKey),
                'name' => $color,
                'sku' => $variantData['sku'] ?? null,
                'color' => $color,
                'price' => ($variantData['price'] ?? '') !== ''
                    ? $variantData['price']
                    : null,
                'stock' => max(0, (int) ($variantData['stock'] ?? 0)),
                'is_active' => !empty($variantData['is_active']),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sinkronkan stok produk induk dengan total stok varian aktif
        |--------------------------------------------------------------------------
        |
        | BUG FIX: kolom stock di tabel products diisi manual lewat form
        | (field "Stok Produk"), terpisah total dari field stok tiap varian.
        | Kalau produk punya varian, kolom ini jadi tidak sinkron dan bisa
        | menyesatkan setiap kode yang masih baca $product->stock langsung
        | (listing/kartu produk, laporan, dsb) — bisa saja menampilkan
        | "sisa 1" padahal semua varian sebenarnya sudah 0.
        |
        | Sekarang: kalau produk punya varian, stock produk SELALU disamakan
        | dengan total stok varian aktifnya, supaya satu-satunya sumber
        | kebenaran tetap stok per varian. Kalau produk tidak punya varian,
        | nilai dari form tetap dipakai apa adanya.
        |
        */

        $this->syncProductStockFromVariants($product);

        /*
        |--------------------------------------------------------------------------
        | Simpan gallery produk
        |--------------------------------------------------------------------------
        */

        Log::info('PRODUCT GALLERY REQUEST', [
            'has_images' => $request->hasFile('images'),
            'image_count' => count($request->file('images', [])),
        ]);

        if ($request->hasFile('images')) {
            foreach (array_slice($request->file('images'), 0, 10) as $index => $image) {
                if ($image && $image->isValid()) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $image->store('products/gallery', 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product->load('variants'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        Log::info('PRODUCT IMAGE DEBUG', [
            'product_id' => $product->id,
            'has_file' => $request->hasFile('image'),
            'file_name' => $request->file('image')?->getClientOriginalName(),
            'file_size' => $request->file('image')?->getSize(),
            'file_error' => $request->file('image')?->getError(),
            'content_length' => $request->header('Content-Length'),
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            if (!$image->isValid()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'Gambar gagal diunggah. Silakan pilih gambar lain.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Hapus gambar lama
            |--------------------------------------------------------------------------
            */
            if (!empty($product->image)) {
                $oldImage = ltrim($product->image, '/');

                if (Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan gambar baru
            |--------------------------------------------------------------------------
            */
            $newImagePath = $image->store('products', 'public');

            if (!$newImagePath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'Gambar tidak berhasil disimpan ke storage.',
                    ]);
            }

            $data['image'] = $newImagePath;

            Log::info('PRODUCT IMAGE UPDATED', [
                'product_id' => $product->id,
                'filename' => $image->getClientOriginalName(),
                'path' => $newImagePath,
            ]);
        }

        $product->update($data);

        if ($request->has('variants')) {
            $variants = $request->input('variants', []);

            $keptVariantIds = [];

            foreach ($variants as $variantKey => $variantData) {
                $color = trim($variantData['color'] ?? '');

                if ($color === '') {
                    continue;
                }

                $variantId = $variantData['id'] ?? null;

                // Varian lama (kalau ada) dicari dulu supaya gambar lamanya
                // bisa diganti / dihapus dengan benar.
                $variant = $variantId
                    ? $product->variants()->whereKey($variantId)->first()
                    : null;

                // ID dikirim tapi bukan milik produk ini -> abaikan (jangan
                // simpan file yatim).
                if ($variantId && !$variant) {
                    continue;
                }

                $variantPayload = [
                    'name' => $color,
                    'sku' => $variantData['sku'] ?? null,
                    'color' => $color,
                    'price' => ($variantData['price'] ?? '') !== ''
                        ? $variantData['price']
                        : null,
                    'stock' => max(0, (int) ($variantData['stock'] ?? 0)),
                    'is_active' => !empty($variantData['is_active']),
                    'image' => $this->resolveVariantImage($request, $variantKey, $variant),
                ];

                if ($variant) {
                    $variant->update($variantPayload);
                    $keptVariantIds[] = $variant->id;
                } else {
                    $variant = $product->variants()->create($variantPayload);
                    $keptVariantIds[] = $variant->id;
                }
            }

            // Varian yang dihapus admin: hapus juga file gambarnya.
            $removedVariants = $product->variants()
                ->when(count($keptVariantIds) > 0, function ($query) use ($keptVariantIds) {
                    $query->whereNotIn('id', $keptVariantIds);
                })
                ->get();

            foreach ($removedVariants as $removedVariant) {
                $this->deleteVariantImageFile($removedVariant->image);
                $removedVariant->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sinkronkan stok produk induk dengan total stok varian aktif
        |--------------------------------------------------------------------------
        |
        | Dijalankan di luar blok "if ($request->has('variants'))" di atas
        | dengan sengaja: kalau produk ini SUDAH punya varian dari
        | sebelumnya tapi request kali ini tidak mengirim field variants
        | sama sekali, stock produk tetap harus konsisten dengan varian
        | yang ada, bukan malah dibiarkan pakai angka lama yang sudah basi.
        |
        */

        $this->syncProductStockFromVariants($product);

        if ($request->hasFile('images')) {
            $currentCount = $product->images()->count();
            $remainingSlots = max(0, 10 - $currentCount);

            foreach (array_slice($request->file('images'), 0, $remainingSlots) as $index => $image) {
                if (!$image || !$image->isValid()) {
                    continue;
                }

                $galleryPath = $image->store('products/gallery', 'public');

                if (!$galleryPath) {
                    continue;
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $galleryPath,
                    'sort_order' => $currentCount + $index,
                ]);
            }
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if (!empty($product->image)) {
            $imagePath = ltrim($product->image, '/');

            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        // Bersihkan file gambar semua varian sebelum produk dihapus.
        foreach ($product->variants as $variant) {
            $this->deleteVariantImageFile($variant->image);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | Sinkronkan stock produk dengan total stok varian aktif
    |--------------------------------------------------------------------------
    |
    | Dipanggil setelah varian disimpan/diupdate/dihapus di store() & update().
    | Kalau produk tidak punya varian aktif sama sekali, kolom stock produk
    | dibiarkan seperti yang diisi admin lewat form (dipakai apa adanya untuk
    | produk tanpa varian).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Gambar varian
    |--------------------------------------------------------------------------
    |
    | Form mengirim per varian:
    |   variants[i][image]         -> file baru (opsional)
    |   variants[i][remove_image]  -> "1" bila admin mencentang "Hapus gambar"
    |
    | Mengembalikan path yang harus disimpan di kolom product_variants.image
    | (null = tidak ada gambar).
    |
    */

    private function resolveVariantImage(
        Request $request,
        int|string $key,
        ?ProductVariant $variant = null
    ): ?string {
        $path = $variant?->image;

        if ($path && $request->boolean("variants.{$key}.remove_image")) {
            $this->deleteVariantImageFile($path);
            $path = null;
        }

        $file = $request->file("variants.{$key}.image");

        if ($file && $file->isValid()) {
            $newPath = $file->store('products/variants', 'public');

            if ($newPath) {
                // Gambar lama diganti -> hapus file lamanya.
                $this->deleteVariantImageFile($path);
                $path = $newPath;
            }
        }

        return $path;
    }

    private function deleteVariantImageFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $path = ltrim($path, '/');

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function syncProductStockFromVariants(Product $product): void
    {
        $totalVariantStock = $product->variants()
            ->where('is_active', true)
            ->sum('stock');

        if ($product->variants()->where('is_active', true)->exists()) {
            $product->update([
                'stock' => (int) $totalVariantStock,
            ]);
        }
    }


    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'weight' => [
                'required',
                'integer',
                'min:1',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*.id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],

            'variants.*.name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'variants.*.sku' => [
                'nullable',
                'string',
                'max:100',
            ],

            'variants.*.color' => [
                'nullable',
                'string',
                'max:100',
            ],

            'variants.*.price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'variants.*.image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'variants.*.remove_image' => [
                'nullable',
                'boolean',
            ],

            'images' => [
                'nullable',
                'array',
                'max:10',
            ],

            'images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:51200',
            ],

            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:51200',
            ],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 255 karakter.',

            'price.required' => 'Harga produk wajib diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'price.min' => 'Harga produk tidak boleh kurang dari 0.',

            'sale_price.numeric' => 'Harga diskon harus berupa angka.',
            'sale_price.min' => 'Harga diskon tidak boleh kurang dari 0.',

            'stock.required' => 'Stok produk wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh kurang dari 0.',

            'weight.required' => 'Berat produk wajib diisi (dipakai untuk menghitung ongkir).',
            'weight.integer' => 'Berat produk harus berupa angka bulat (gram).',
            'weight.min' => 'Berat produk minimal 1 gram.',

            'variants.*.image.image' => 'Gambar varian harus berupa gambar.',
            'variants.*.image.mimes' => 'Gambar varian harus berformat JPG, JPEG, PNG, atau WEBP.',
            'variants.*.image.max' => 'Ukuran gambar varian maksimal 5 MB.',

            'image.file' => 'File gambar tidak valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 50 MB.',
        ]);
    }
}