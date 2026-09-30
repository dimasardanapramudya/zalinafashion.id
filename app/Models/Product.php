<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock',
        'weight',
        'sku',
        'image',
        'is_active',
        'is_featured',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
            'weight' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Category Relationship
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Variants Relationship
    |--------------------------------------------------------------------------
    */

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Images Relationship
    |--------------------------------------------------------------------------
    */

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Current Price
    |--------------------------------------------------------------------------
    */

    public function getCurrentPriceAttribute(): float
    {
        return (float) (
            $this->sale_price !== null
                ? $this->sale_price
                : $this->price
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Formatted Current Price
    |--------------------------------------------------------------------------
    */

    public function getFormattedCurrentPriceAttribute(): string
    {
        return 'Rp ' . number_format(
            $this->current_price,
            0,
            ',',
            '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Image Path
    |--------------------------------------------------------------------------
    */

    public function getImagePathAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return ltrim($this->image, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | Image URL
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (!Storage::disk('public')->exists($this->image_path)) {
            return null;
        }

        return asset(
            'storage/' . $this->image_path
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Has Image
    |--------------------------------------------------------------------------
    */

    public function getHasImageAttribute(): bool
    {
        return !empty($this->image_url);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Helpers
    |--------------------------------------------------------------------------
    |
    | Dipakai di halaman shop/produk untuk menentukan kapan menampilkan
    | badge "Stok Habis" dan menonaktifkan tombol beli. Kalau produk ini
    | punya varian, ketersediaan sebenarnya ditentukan oleh stok
    | per-varian (lihat isSoldOut() di ProductVariant), bukan kolom
    | stock di tabel products.
    |
    */

    /**
     * Varian aktif saja (sudah di-reindex). Memakai relasi `variants`
     * (lazy-load kalau belum di-eager-load, jadi hasilnya selalu benar).
     * Di query listing sebaiknya tetap ->with('variants') supaya tidak N+1.
     */
    public function getActiveVariantsAttribute(): Collection
    {
        return $this->variants
            ->where('is_active', true)
            ->values();
    }

    /**
     * Total stok yang bisa dibeli sekarang ("stok general").
     *
     * - Produk dengan varian aktif  : jumlah stok semua varian aktif.
     * - Produk tanpa varian aktif   : kolom stock di tabel products.
     *
     * BUG FIX: sebelumnya hanya menghitung varian kalau relasi sudah
     * ter-load (relationLoaded). Kalau belum, hasilnya jatuh ke kolom
     * products.stock yang bisa basi/tidak sama dengan stok varian —
     * inilah penyebab angka di shop tidak cocok dengan halaman produk.
     */
    public function getAvailableStockAttribute(): int
    {
        $activeVariants = $this->active_variants;

        if ($activeVariants->isNotEmpty()) {
            return (int) $activeVariants->sum(
                fn ($variant) => max(0, (int) $variant->stock)
            );
        }

        return max(0, (int) $this->stock);
    }

    /**
     * True kalau produk sudah tidak bisa dibeli sama sekali:
     * produk dinonaktifkan admin, ATAU total stok yang tersedia 0
     * (semua varian aktif habis / stok produk tanpa varian habis).
     */
    public function getIsSoldOutAttribute(): bool
    {
        if (!$this->is_active) {
            return true;
        }

        return $this->available_stock <= 0;
    }

    /**
     * Label stok siap-tampil untuk kartu produk di halaman shop:
     * "Stok Habis", "Sisa N" (N <= 5), atau "Stok N".
     */
    public function getStockLabelAttribute(): string
    {
        if ($this->is_sold_out) {
            return 'Stok Habis';
        }

        $stock = $this->available_stock;

        return $stock <= 5
            ? "Sisa {$stock}"
            : "Stok {$stock}";
    }

    /**
     * Daftar varian aktif beserta stoknya, untuk halaman detail produk
     * (gaya Shopee: varian yang habis ditampilkan abu-abu & tidak bisa dipilih).
     *
     * @return array<int, array{id:int,name:string,stock:int,is_sold_out:bool,label:string}>
     */
    public function getVariantStockListAttribute(): array
    {
        return $this->active_variants
            ->map(function ($variant) {
                $stock = max(0, (int) $variant->stock);

                return [
                    'id' => (int) $variant->id,
                    'name' => (string) $variant->name,
                    'stock' => $stock,
                    'is_sold_out' => $stock <= 0,
                    'label' => match (true) {
                        $stock <= 0 => 'Habis',
                        $stock <= 5 => "Sisa {$stock}",
                        default => "Stok {$stock}",
                    },
                ];
            })
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Synchronization
    |--------------------------------------------------------------------------
    */

    /**
     * Hitung total stok dari semua varian aktif.
     *
     * Jika produk memiliki varian, stok produk dianggap sebagai jumlah stok
     * seluruh varian yang aktif.
     */
    public function getTotalVariantStock(): int
    {
        return (int) $this->variants()
            ->where('is_active', true)
            ->sum('stock');
    }

    /**
     * Sinkronkan stock produk dari total stock varian aktif.
     *
     * Catatan: method ini memakai transaksi + lock pada row product agar
     * perubahan stock product tidak saling menimpa ketika dipanggil bersamaan.
     */
    public function syncStockFromVariants(): void
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $product = self::query()
                ->whereKey($this->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $totalVariantStock = $product->getTotalVariantStock();
            $oldStock = (int) $product->stock;

            if ($oldStock !== $totalVariantStock) {
                $product->update([
                    'stock' => $totalVariantStock,
                ]);

                \Illuminate\Support\Facades\Log::info(
                    'Product stock synced from variants',
                    [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'old_stock' => $oldStock,
                        'new_stock' => $totalVariantStock,
                        'variant_count' => $product->variants()
                            ->where('is_active', true)
                            ->count(),
                    ]
                );
            }

                // Keep the current model instance consistent with the database.
                $this->setAttribute('stock', $totalVariantStock);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error(
                'Error syncing product stock from variants',
                [
                    'product_id' => $this->getKey(),
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]
            );

            throw $e;
        }
    }

    /**
     * Cek apakah stock produk sudah sama dengan total stock varian aktif.
     */
    public function isStockSynced(): bool
    {
        return (int) $this->stock === $this->getTotalVariantStock();
    }

    /**
     * Ambil informasi stock produk + breakdown setiap varian aktif.
     */
    public function getStockInfo(): array
    {
        $activeVariants = $this->variants()
            ->where('is_active', true)
            ->get();

        $variantTotal = (int) $activeVariants->sum('stock');

        return [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'product_stock' => (int) $this->stock,
            'variant_total' => $variantTotal,
            'is_synced' => (int) $this->stock === $variantTotal,
            'variant_count' => $activeVariants->count(),
            'variants' => $activeVariants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'stock' => (int) $variant->stock,
                    'is_active' => (bool) $variant->is_active,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Cek apakah quantity tertentu masih tersedia.
     *
     * Untuk produk dengan varian, pengecekan dilakukan terhadap varian.
     * Untuk produk tanpa varian, pengecekan dilakukan terhadap stock produk.
     */
    public function hasEnoughStock(int $quantity, ?ProductVariant $variant = null): bool
    {
        if ($quantity < 0) {
            return false;
        }

        if ($variant !== null) {
            return (bool) $variant->is_active && (int) $variant->stock >= $quantity;
        }

        return (int) $this->stock >= $quantity;
    }

    /**
     * Status stock untuk kebutuhan UI.
     */
    public function getStockStatus(): string
    {
        $stock = (int) $this->available_stock;

        if ($stock <= 0) {
            return 'out_of_stock';
        }

        return $stock <= 5 ? 'low' : 'available';
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Hanya Produk Yang Tersedia
    |--------------------------------------------------------------------------
    |
    | Dipakai di listing shop kalau mau langsung sembunyikan produk yang
    | sudah pasti habis+tidak punya varian tersisa, tanpa perlu load
    | variants untuk tiap baris.
    |
    */

    public function scopeInStock($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('stock', '>', 0)
                    ->orWhereHas('variants', function ($vq) {
                        $vq->where('is_active', true)
                            ->where('stock', '>', 0);
                    });
            });
    }

}