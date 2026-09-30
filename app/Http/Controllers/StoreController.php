<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Favorite;
use App\Models\HomeSlider;
use App\Models\Product;
use App\Models\Setting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    /**
     * ============================================================
     * HOMEPAGE
     * ============================================================
     */
    public function home()
    {
        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */

        $settings = $this->getSettings();

        /*
        |--------------------------------------------------------------------------
        | SLIDER
        |--------------------------------------------------------------------------
        */

        $sliders = $this->getActiveSliders();

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */

        $categories = $this->getCategories();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        $products = $this->getHomeProducts();

        /*
        |--------------------------------------------------------------------------
        | ACTIVE DISCOUNTS
        |--------------------------------------------------------------------------
        |
        | Semua promo aktif diambil menggunakan get().
        | Jangan menggunakan first() sebagai daftar promo utama karena
        | first() hanya mengambil satu promo.
        |
        */

        $discountsCollection = $this->getActiveDiscounts();

        /*
        |--------------------------------------------------------------------------
        | FEATURED DISCOUNT
        |--------------------------------------------------------------------------
        |
        | Ini hanya untuk promo unggulan atau promo pertama.
        | Daftar semua promo tetap menggunakan $discountsCollection.
        |
        */

        $featuredDiscount = $discountsCollection->first();

        /*
        |--------------------------------------------------------------------------
        | PROMO ALIAS
        |--------------------------------------------------------------------------
        |
        | Dipertahankan agar tetap kompatibel dengan Blade lama.
        |
        */

        $promo = $featuredDiscount;

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        |
        | Banyak alias disediakan agar cocok dengan Home Blade yang
        | mungkin menggunakan nama variabel berbeda.
        |
        */

        return view('store.home', [
            'settings' => $settings,

            'sliders' => $sliders,

            'categories' => $categories,
            'categoriesCollection' => $categories,

            'products' => $products,
            'productsCollection' => $products,

            'discounts' => $discountsCollection,
            'discountsCollection' => $discountsCollection,

            'featuredDiscount' => $featuredDiscount,
            'promo' => $promo,
        ]);
    }

    /**
     * ============================================================
     * SHOP / PRODUCT LIST
     * ============================================================
     */
    public function shop(Request $request)
    {
        $query = Product::query();

        /*
        |--------------------------------------------------------------------------
        | RELATIONSHIPS
        |--------------------------------------------------------------------------
        */

        $this->loadProductRelations($query);

        /*
        |--------------------------------------------------------------------------
        | ONLY ACTIVE PRODUCTS
        |--------------------------------------------------------------------------
        */

        $this->applyActiveProductFilter($query);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        // Menerima parameter "search" (nama resmi di seluruh aplikasi) dan
        // "q" (dipakai oleh kolom pencarian di header) supaya tidak putus
        // kalau salah satu sisi lupa disamakan lagi di kemudian hari.
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $this->applySearchFilter($query, $search);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY FILTER
        |--------------------------------------------------------------------------
        */

        $categorySlug = trim((string) $request->input('category', ''));

        if ($categorySlug !== '') {
            $query->whereHas('category', function (Builder $builder) use ($categorySlug) {
                $builder->where('slug', $categorySlug);
            });
        }
        /*
        |--------------------------------------------------------------------------
        | PRICE FILTER
        |--------------------------------------------------------------------------
        */

        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $hasSalePriceColumn = Schema::hasColumn('products', 'sale_price');

        if ($hasSalePriceColumn) {
            $effectivePrice = 'COALESCE(NULLIF(sale_price, 0), price)';
        } else {
            $effectivePrice = 'price';
        }

        if (
            $minPrice !== null &&
            $minPrice !== '' &&
            is_numeric($minPrice)
        ) {
            $query->whereRaw(
                "{$effectivePrice} >= ?",
                [(float) $minPrice]
            );
        }

        if (
            $maxPrice !== null &&
            $maxPrice !== '' &&
            is_numeric($maxPrice)
        ) {
            $query->whereRaw(
                "{$effectivePrice} <= ?",
                [(float) $maxPrice]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STOK BUG FIX: PRODUK HABIS TIDAK BOLEH MENUTUPI PRODUK YANG TERSEDIA
        |--------------------------------------------------------------------------
        |
        | Sebelumnya halaman /shop tidak memedulikan stok sama sekali (berbeda
        | dari homepage yang sudah mengurutkan stok lebih dulu), sehingga produk
        | yang stoknya sudah 0 bisa muncul di baris paling atas hanya karena
        | urutan "Terbaru". Di sini produk yang masih ada stoknya selalu
        | didahulukan, baru di dalamnya diurutkan sesuai pilihan user.
        |
        */

        if (Schema::hasColumn('products', 'stock')) {
            $query->orderByRaw('(stock > 0) desc');
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */

        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;

            case 'name':
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;

            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;

            case 'price_low':
                $query->orderByRaw(
                    "{$effectivePrice} ASC"
                );
                break;

            case 'price_high':
                $query->orderByRaw(
                    "{$effectivePrice} DESC"
                );
                break;

            case 'popular':
                if (Schema::hasColumn('products', 'sold_count')) {
                    $query->orderByDesc('sold_count');
                } elseif (Schema::hasColumn('products', 'views')) {
                    $query->orderByDesc('views');
                } else {
                    $query->latest();
                }
                break;

            case 'latest':
            default:
                $query->latest();
                break;
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->paginate(12)
            ->withQueryString();

        $categories = $this->getCategories();

        $discountsCollection = $this->getActiveDiscounts();

        $featuredDiscount = $discountsCollection->first();

        /*
        |--------------------------------------------------------------------------
        | PRICE RANGE (UNTUK SLIDER FILTER HARGA)
        |--------------------------------------------------------------------------
        |
        | globalMinPrice / globalMaxPrice: batas harga dari seluruh produk aktif.
        | selectedMinPrice / selectedMaxPrice: nilai yang sedang dipilih user,
        | default ke batas global kalau user belum memilih apa-apa.
        |
        */

        [$globalMinPrice, $globalMaxPrice] = $this->getGlobalPriceRange();

        $selectedMinPrice = ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice))
            ? (float) $minPrice
            : $globalMinPrice;

        $selectedMaxPrice = ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice))
            ? (float) $maxPrice
            : $globalMaxPrice;

        // Jaga-jaga supaya nilai selected tidak keluar dari batas global.
        $selectedMinPrice = max($globalMinPrice, min($selectedMinPrice, $globalMaxPrice));
        $selectedMaxPrice = max($globalMinPrice, min($selectedMaxPrice, $globalMaxPrice));

        $settings = $this->getSettings();

        $viewData = [
            'products' => $products,
            'productsCollection' => $products,

            'categories' => $categories,
            'categoriesCollection' => $categories,

            'discounts' => $discountsCollection,
            'discountsCollection' => $discountsCollection,

            'featuredDiscount' => $featuredDiscount,
            'promo' => $featuredDiscount,

            'settings' => $settings,

            'search' => $search,
            'categorySlug' => $categorySlug,
            'sort' => $sort,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,

            'globalMinPrice' => $globalMinPrice,
            'globalMaxPrice' => $globalMaxPrice,
            'selectedMinPrice' => $selectedMinPrice,
            'selectedMaxPrice' => $selectedMaxPrice,
        ];

        /*
        |--------------------------------------------------------------------------
        | RESPON AJAX (SLIDER HARGA, PENCARIAN, URUTKAN, PAGINATION)
        |--------------------------------------------------------------------------
        |
        | Kalau request datang dari fetch() di shop.blade.php (header
        | X-Requested-With: XMLHttpRequest), kirim balik HANYA fragment
        | hasil produk (toolbar + grid + pagination), bukan halaman penuh.
        | Ini yang membuat slider harga bisa memfilter tanpa reload.
        |
        */
        if ($request->ajax()) {
            return view('store.partials.shop-results', $viewData);
        }

        return view('store.shop', $viewData);
    }

    /**
     * ============================================================
     * CATEGORY LIST
     * ============================================================
     */
    public function categories()
    {
        $categories = $this->getCategories();

        $settings = $this->getSettings();

        return view('store.categories', [
            'categories' => $categories,
            'categoriesCollection' => $categories,

            'settings' => $settings,
        ]);
    }

    /**
     * ============================================================
     * SINGLE CATEGORY
     * ============================================================
     */
    public function category(string $slug)
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $query = Product::query();

        $this->loadProductRelations($query);

        $this->applyActiveProductFilter($query);

        $query->whereHas('category', function (Builder $builder) use ($category) {
            $builder->where('id', $category->id);
        });

        $products = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = $this->getCategories();

        $discountsCollection = $this->getActiveDiscounts();

        $featuredDiscount = $discountsCollection->first();

        [$globalMinPrice, $globalMaxPrice] = $this->getGlobalPriceRange();

        $settings = $this->getSettings();

        return view('store.category', [
            'category' => $category,

            'products' => $products,
            'productsCollection' => $products,

            'categories' => $categories,
            'categoriesCollection' => $categories,

            'discounts' => $discountsCollection,
            'discountsCollection' => $discountsCollection,

            'featuredDiscount' => $featuredDiscount,
            'promo' => $featuredDiscount,

            'settings' => $settings,

            'globalMinPrice' => $globalMinPrice,
            'globalMaxPrice' => $globalMaxPrice,
            'selectedMinPrice' => $globalMinPrice,
            'selectedMaxPrice' => $globalMaxPrice,
        ]);
    }

    /**
     * ============================================================
     * PRODUCT DETAIL
     * ============================================================
     */
    public function product(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | CHECK PRODUCT STATUS
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasColumn('products', 'is_active') &&
            !$product->is_active
        ) {
            abort(404);
        }

        if (
            Schema::hasColumn('products', 'active') &&
            !$product->active
        ) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONSHIPS
        |--------------------------------------------------------------------------
        */

        $product->load([
            'category',
            'variants',
            'images',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RELATED PRODUCTS
        |--------------------------------------------------------------------------
        */

        $relatedProducts = Product::query()
            ->where('id', '!=', $product->id)
            ->when(
                $product->category_id,
                function (Builder $query) use ($product) {
                    $query->where('category_id', $product->category_id);
                }
            );

        $this->applyActiveProductFilter($relatedProducts);

        $this->loadProductRelations($relatedProducts);

        $relatedProducts = $relatedProducts
            ->latest()
            ->limit(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES AND PROMOS
        |--------------------------------------------------------------------------
        */

        $categories = $this->getCategories();

        $discountsCollection = $this->getActiveDiscounts();

        $featuredDiscount = $discountsCollection->first();

        $settings = $this->getSettings();

        /*
        |--------------------------------------------------------------------------
        | FAVORITE STATUS
        |--------------------------------------------------------------------------
        |
        | Dihitung di controller (bukan langsung di Blade) supaya aman
        | menggunakan pengecekan Schema + try/catch seperti method lain,
        | dan tidak membuat seluruh halaman produk crash kalau tabel
        | favorites belum tersedia.
        |
        */

        $isFavorited = $this->isProductFavorited($product->id);

        return view('store.product', [
            'product' => $product,

            'relatedProducts' => $relatedProducts,

            'categories' => $categories,
            'categoriesCollection' => $categories,

            'discounts' => $discountsCollection,
            'discountsCollection' => $discountsCollection,

            'featuredDiscount' => $featuredDiscount,
            'promo' => $featuredDiscount,

            'settings' => $settings,

            'isFavorited' => $isFavorited,
        ]);
    }

    /**
     * ============================================================
     * TENTANG KAMI (ABOUT US)
     * ============================================================
     */
    public function about()
    {
        $settings = $this->getSettings();

        $categories = $this->getCategories();

        return view('store.about', [
            'settings' => $settings,

            'categories' => $categories,
            'categoriesCollection' => $categories,
        ]);
    }

    /**
     * ============================================================
     * GET GLOBAL PRICE RANGE
     * ============================================================
     |
     | Mengembalikan [min, max] dari harga efektif (sale_price kalau ada,
     | kalau tidak pakai price) di antara seluruh produk aktif.
     | Dipakai untuk slider filter harga di halaman shop.
     |
     */
    private function getGlobalPriceRange(): array
    {
        try {
            if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'price')) {
                return [0, 0];
            }

            $query = Product::query();

            $this->applyActiveProductFilter($query);

            $priceExpression = Schema::hasColumn('products', 'sale_price')
                ? 'COALESCE(NULLIF(sale_price, 0), price)'
                : 'price';

            $row = $query
                ->selectRaw(
                    "MIN({$priceExpression}) as min_price, MAX({$priceExpression}) as max_price"
                )
                ->first();

            $min = ($row && $row->min_price !== null) ? (float) $row->min_price : 0.0;
            $max = ($row && $row->max_price !== null) ? (float) $row->max_price : 0.0;

            if ($max < $min) {
                $max = $min;
            }

            return [$min, $max];
        } catch (\Throwable $exception) {
            return [0, 0];
        }
    }

    /**
     * ============================================================
     * GET SETTINGS
     * ============================================================
     */
    private function getSettings(): Collection
    {
        try {
            if (!Schema::hasTable('settings')) {
                return collect();
            }

            $settings = Setting::query()
                ->get()
                ->mapWithKeys(function ($setting) {
                    $key = $setting->key
                        ?? $setting->name
                        ?? null;

                    $value = $setting->value
                        ?? $setting->setting_value
                        ?? null;

                    return $key
                        ? [$key => $value]
                        : [];
                });

            return $settings;
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * ============================================================
     * GET ACTIVE SLIDERS
     * ============================================================
     */
    private function getActiveSliders()
    {
        try {
            if (!Schema::hasTable('home_sliders')) {
                return collect();
            }

            $query = HomeSlider::query();

            if (Schema::hasColumn('home_sliders', 'is_active')) {
                $query->where('is_active', true);
            } elseif (Schema::hasColumn('home_sliders', 'active')) {
                $query->where('active', true);
            }

            if (Schema::hasColumn('home_sliders', 'sort_order')) {
                $query->orderBy('sort_order');
            } elseif (Schema::hasColumn('home_sliders', 'position')) {
                $query->orderBy('position');
            } else {
                $query->latest();
            }

            return $query->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * ============================================================
     * GET CATEGORIES
     * ============================================================
     */
    private function getCategories()
    {
        try {
            if (!Schema::hasTable('categories')) {
                return collect();
            }

            $query = Category::query();

            if (Schema::hasColumn('categories', 'is_active')) {
                $query->where('is_active', true);
            } elseif (Schema::hasColumn('categories', 'active')) {
                $query->where('active', true);
            }

            if (Schema::hasColumn('categories', 'sort_order')) {
                $query->orderBy('sort_order');
            } else {
                $query->orderBy('name');
            }

            return $query->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * ============================================================
     * GET HOME PRODUCTS
     * ============================================================
     */
    private function getHomeProducts()
    {
        try {
            if (!Schema::hasTable('products')) {
                return collect();
            }

            $query = Product::query();

            $this->loadProductRelations($query);

            $this->applyActiveProductFilter($query);

            /*
            |--------------------------------------------------------------------------
            | FEATURED PRODUCTS FIRST
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('products', 'is_featured')) {
                $query->orderByDesc('is_featured');
            } elseif (Schema::hasColumn('products', 'featured')) {
                $query->orderByDesc('featured');
            }

            /*
            |--------------------------------------------------------------------------
            | STOCKED PRODUCTS FIRST
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('products', 'stock')) {
                $query->orderByDesc('stock');
            }

            $query->latest();

            return $query
                ->limit(12)
                ->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * ============================================================
     * GET ALL ACTIVE DISCOUNTS
     * ============================================================
     */
    private function getActiveDiscounts()
    {
        try {
            if (!Schema::hasTable('discounts')) {
                return collect();
            }

            $query = Discount::query();

            /*
            |--------------------------------------------------------------------------
            | ACTIVE STATUS
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('discounts', 'is_active')) {
                $query->where('is_active', true);
            } elseif (Schema::hasColumn('discounts', 'active')) {
                $query->where('active', true);
            }

            /*
            |--------------------------------------------------------------------------
            | START DATE
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('discounts', 'starts_at')) {
                $query->where(function (Builder $builder) {
                    $builder
                        ->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                });
            } elseif (Schema::hasColumn('discounts', 'start_date')) {
                $query->where(function (Builder $builder) {
                    $builder
                        ->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                });
            }

            /*
            |--------------------------------------------------------------------------
            | END DATE
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('discounts', 'ends_at')) {
                $query->where(function (Builder $builder) {
                    $builder
                        ->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                });
            } elseif (Schema::hasColumn('discounts', 'end_date')) {
                $query->where(function (Builder $builder) {
                    $builder
                        ->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                });
            }

            /*
            |--------------------------------------------------------------------------
            | SORTING
            |--------------------------------------------------------------------------
            */

            if (Schema::hasColumn('discounts', 'priority')) {
                $query->orderByDesc('priority');
            }

            if (Schema::hasColumn('discounts', 'sort_order')) {
                $query->orderBy('sort_order');
            }

            if (Schema::hasColumn('discounts', 'created_at')) {
                $query->latest();
            }

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Gunakan get(), bukan first(), supaya semua promo aktif
            | dikirim ke Home Blade.
            |
            */

            return $query->get();
        } catch (\Throwable $exception) {
            return collect();
        }
    }

    /**
     * ============================================================
     * CHECK FAVORITE STATUS
     * ============================================================
     */
    private function isProductFavorited(int $productId): bool
    {
        try {
            $userId = session('zalina_user_id');

            if (!$userId) {
                return false;
            }

            if (!Schema::hasTable('favorites')) {
                return false;
            }

            return Favorite::query()
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * ============================================================
     * LOAD PRODUCT RELATIONSHIPS SAFELY
     * ============================================================
     */
    private function loadProductRelations(Builder $query): void
    {
        try {
            $query->with([
                'category',
            ]);
        } catch (\Throwable $exception) {
            // Tidak menghentikan halaman jika relasi category belum tersedia.
        }
    }

    /**
     * ============================================================
     * SEARCH SUGGESTIONS (LIVE, SAAT USER MENGETIK)
     * ============================================================
     |
     | Dipanggil lewat fetch() dari kolom pencarian di header.
     | Selalu query langsung ke tabel products, jadi produk baru
     | apa pun otomatis ikut muncul tanpa perlu diubah manual.
     |
     */
    public function searchSuggest(Request $request)
    {
        $term = trim((string) $request->input('search', $request->input('q', '')));

        // Minimal 2 karakter supaya tidak menyapu seluruh katalog
        // hanya karena user baru mengetik satu huruf.
        if (mb_strlen($term) < 2) {
            return response()->json(['suggestions' => []]);
        }

        try {
            if (!Schema::hasTable('products')) {
                return response()->json(['suggestions' => []]);
            }

            $query = Product::query();

            $this->loadProductRelations($query);
            $this->applyActiveProductFilter($query);
            $this->applySearchFilter($query, $term);

            $hasSalePriceColumn = Schema::hasColumn('products', 'sale_price');

            $products = $query->limit(6)->get();

            $suggestions = $products->map(function (Product $product) use ($hasSalePriceColumn) {
                $price = (float) ($product->price ?? 0);
                $salePrice = $hasSalePriceColumn ? (float) ($product->sale_price ?? 0) : 0;
                $effectivePrice = $salePrice > 0 ? $salePrice : $price;

                return [
                    'id' => $product->id,
                    'name' => $product->name ?? 'Produk Zalina',
                    'image' => $this->resolveProductImage($product),
                    'price' => 'Rp' . number_format($effectivePrice, 0, ',', '.'),
                    'has_discount' => $salePrice > 0 && $salePrice < $price,
                    'original_price' => ($salePrice > 0 && $salePrice < $price)
                        ? 'Rp' . number_format($price, 0, ',', '.')
                        : null,
                    'url' => $this->resolveProductUrl($product),
                ];
            });

            return response()->json(['suggestions' => $suggestions]);
        } catch (\Throwable $exception) {
            return response()->json(['suggestions' => []]);
        }
    }

    /**
     * ============================================================
     * APPLY SEARCH FILTER (DIPAKAI shop() DAN searchSuggest())
     * ============================================================
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        // Escape wildcard LIKE (% dan _) supaya pencarian yang mengandung
        // karakter tersebut tidak menghasilkan query yang salah.
        $escapedSearch = addcslashes($search, '%_');

        $query->where(function (Builder $builder) use ($escapedSearch) {
            $builder
                ->where('name', 'like', '%' . $escapedSearch . '%')
                ->orWhere('description', 'like', '%' . $escapedSearch . '%')
                ->orWhere('sku', 'like', '%' . $escapedSearch . '%');
        });
    }

    /**
     * ============================================================
     * RESOLVE PRODUCT URL (SLUG KALAU ADA, FALLBACK KE ID)
     * ============================================================
     */
    private function resolveProductUrl(Product $product): string
    {
        try {
            if (Schema::hasColumn('products', 'slug') && $product->slug) {
                return route('product.show', $product->slug);
            }
        } catch (\Throwable $exception) {
            // Lanjut ke fallback di bawah.
        }

        return route('product.show', $product->id);
    }

    /**
     * ============================================================
     * RESOLVE PRODUCT IMAGE
     * ============================================================
     |
     | Sama seperti helper resolveImage() di Blade: coba beberapa
     | kemungkinan nama kolom, plus relasi "images" kalau ada,
     | supaya tetap jalan berapa pun skema gambar yang dipakai.
     |
     */
    private function resolveProductImage(Product $product): ?string
    {
        $candidateFields = [
            'image',
            'image_path',
            'thumbnail',
            'thumbnail_path',
            'photo',
            'photo_path',
            'cover',
            'cover_image',
        ];

        foreach ($candidateFields as $field) {
            $value = $product->{$field} ?? null;

            if ($value) {
                return $this->resolveImagePath((string) $value);
            }
        }

        // Fallback: coba relasi images (tabel gambar terpisah), ambil yang pertama.
        try {
            if (method_exists($product, 'images')) {
                $firstImage = $product->images()->first();

                if ($firstImage) {
                    foreach (array_merge($candidateFields, ['path', 'url']) as $field) {
                        $value = $firstImage->{$field} ?? null;

                        if ($value) {
                            return $this->resolveImagePath((string) $value);
                        }
                    }
                }
            }
        } catch (\Throwable $exception) {
            // Relasi tidak tersedia / tabel belum ada — abaikan, pakai placeholder.
        }

        return null;
    }

    /**
     * ============================================================
     * RESOLVE IMAGE PATH KE URL YANG BISA DIAKSES BROWSER
     * ============================================================
     */
    private function resolveImagePath(string $value): string
    {
        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:image'])) {
            return $value;
        }

        if (Str::startsWith($value, '/')) {
            return $value;
        }

        if (Str::startsWith($value, 'storage/')) {
            return asset($value);
        }

        if (Str::startsWith($value, 'public/')) {
            return asset(Str::after($value, 'public/'));
        }

        try {
            return Storage::url($value);
        } catch (\Throwable $exception) {
            return asset('storage/' . ltrim($value, '/'));
        }
    }

    /**
     * ============================================================
     * APPLY ACTIVE PRODUCT FILTER
     * ============================================================
     */
    private function applyActiveProductFilter(Builder $query): void
    {
        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('is_active', true);
        } elseif (Schema::hasColumn('products', 'active')) {
            $query->where('active', true);
        }
    }
}