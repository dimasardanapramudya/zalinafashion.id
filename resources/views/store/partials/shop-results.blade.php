@php
    // File ini dipakai 2 cara: (1) di-include dari store/shop.blade.php
    // untuk render awal, (2) dikirim langsung oleh StoreController::shop()
    // sebagai balasan AJAX saat filter/slider harga digeser — makanya file
    // ini tidak boleh bergantung pada variabel dari view induk.
    //
    // STOK: semua angka stok diambil dari accessor Product
    // (available_stock, is_sold_out) yang menghitung TOTAL stok varian aktif
    // — sama persis dengan halaman detail produk. Jangan baca $product->stock
    // langsung: kolom itu stok produk induk dan bisa basi.
    //
    // loadMissing('variants') memuat varian sekali untuk seluruh produk di
    // halaman ini, jadi tidak terjadi N+1 query walau controller belum
    // memakai ->with('variants').
    $products->loadMissing('variants');
@endphp

                    {{-- TOOLBAR --}}
                    <div class="shop-toolbar">
                        <p class="shop-toolbar-count">
                            @if(request('search'))
                                Hasil pencarian: <strong>"{{ request('search') }}"</strong>
                            @else
                                Menampilkan <strong>{{ $products->count() }}</strong>
                                dari <strong>{{ $products->total() }}</strong> produk
                            @endif
                        </p>

                        <form
                            action="{{ route('shop') }}"
                            method="GET"
                            class="shop-sort-form"
                            @submit.prevent="submitFilterForm($event)"
                        >
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            @if(request('min_price'))
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            @endif

                            @if(request('max_price'))
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            @endif

                            <label for="shop-sort" class="shop-sort-label">Urutkan</label>

                            <select
                                id="shop-sort"
                                name="sort"
                                onchange="this.form.requestSubmit()"
                                class="shop-sort-select"
                            >
                                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>
                                    Terbaru
                                </option>

                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>
                                    Harga terendah
                                </option>

                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>
                                    Harga tertinggi
                                </option>

                                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>
                                    Nama A–Z
                                </option>
                            </select>
                        </form>
                    </div>

                    {{-- ACTIVE FILTER CHIPS (bisa dihapus satu per satu) --}}
                    @if(request('search') || request('min_price') || request('max_price'))
                        <div class="shop-chips">
                            @if(request('search'))
                                <a href="{{ route('shop', request()->except('search')) }}" class="shop-chip">
                                    Pencarian: {{ request('search') }} <span>×</span>
                                </a>
                            @endif

                            @if(request('min_price') || request('max_price'))
                                <a href="{{ route('shop', request()->except(['min_price', 'max_price'])) }}" class="shop-chip">
                                    Rp {{ number_format(request('min_price', $globalMinPrice), 0, ',', '.') }}
                                    – Rp {{ number_format(request('max_price', $globalMaxPrice), 0, ',', '.') }}
                                    <span>×</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    {{-- PRODUCTS --}}
                    @if($products->count())

                        <div class="shop-products-grid">
                            @foreach($products as $p)
                                @php
                                    // Stok general = total stok semua varian aktif
                                    // (atau stok produk kalau tidak punya varian).
                                    $stock = $p->available_stock;
                                    $isSoldOut = $p->is_sold_out;
                                @endphp

                                <a
                                    href="{{ route('product.show', $p) }}"
                                    class="shop-product-card group {{ $isSoldOut ? 'is-soldout' : '' }}"
                                >
                                    <div
                                        class="shop-product-image love-product-area"
                                        data-product-id="{{ $p->id }}"
                                    >

                                        @if($p->image)
                                            <img
                                                src="{{ asset('storage/' . $p->image) }}"
                                                alt="{{ $p->name }}"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="shop-image-fallback">
                                                <div>
                                                    <div class="shop-fallback-circle">
                                                        {{ mb_substr($p->name, 0, 1) }}
                                                    </div>

                                                    <div class="shop-fallback-caption">
                                                        Zalina Fashion
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="double-tap-love"
                                            data-double-love="{{ $p->id }}"
                                        >
                                            <span class="double-love-heart heart-one">♥</span>
                                            <span class="double-love-heart heart-two">♥</span>
                                        </div>

                                        @if(!$isSoldOut && $p->sale_price)
                                            <span class="shop-badge shop-badge-sale">
                                                Diskon
                                            </span>
                                        @endif

                                        @if(!$isSoldOut && $p->is_featured)
                                            <span class="shop-badge shop-badge-featured">
                                                Favorit
                                            </span>
                                        @endif

                                        @if($isSoldOut)
                                            <div class="shop-soldout-plate">
                                                <span>Stok Habis</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="shop-product-info">
                                        <div class="shop-product-meta">
                                            <span class="shop-product-category">
                                                {{ $p->category?->name ?? 'Zalina' }}
                                            </span>

                                            {{-- Stok general (rincian per varian ada di halaman detail).
                                                 Kalau habis, cukup plate "Stok Habis" di atas gambar. --}}
                                            @if(!$isSoldOut)
                                                @if($stock <= 5)
                                                    <span class="shop-low-stock">
                                                        Sisa {{ $stock }}
                                                    </span>
                                                @else
                                                    <span class="shop-stock-badge">
                                                        Stok {{ $stock }}
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="shop-product-love-row">
                                            <button
                                                type="button"
                                                class="product-love-button"
                                                data-love-button="{{ $p->id }}"
                                                data-loved="@json(
                                                    session('zalina_user_id')
                                                        ? \App\Models\Favorite::where('user_id', session('zalina_user_id'))
                                                            ->where('product_id', $p->id)
                                                            ->exists()
                                                        : false
                                                )"
                                                aria-label="Sukai {{ $p->name }}"
                                            >
                                                <span class="love-icon">♡</span>
                                                <span class="love-label">Suka</span>
                                            </button>
                                        </div>

                                        <h3 class="shop-product-name">
                                            {{ $p->name }}
                                        </h3>

                                        <div class="shop-product-bottom">
                                            <div>
                                                <div class="shop-product-price">
                                                    Rp {{ number_format($p->current_price, 0, ',', '.') }}
                                                </div>

                                                @if($p->sale_price)
                                                    <div class="shop-product-old-price">
                                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>

                                            <span class="shop-product-cta">
                                                {{ $isSoldOut ? 'Lihat produk' : 'Lihat detail' }}
                                            </span>
                                        </div>
                                    </div>
                                </a>

                            @endforeach
                        </div>

                        {{-- PAGINATION --}}
                        <div class="mt-10">
                            {{ $products->withQueryString()->links() }}
                        </div>

                    @else

                        {{-- EMPTY STATE --}}
                        <div class="shop-empty">
                            <h3>Belum menemukan koleksi</h3>

                            <p>
                                Produk dengan pencarian atau rentang harga tersebut
                                belum tersedia. Coba gunakan kata kunci lain atau
                                perluas rentang harga.
                            </p>

                            <a href="{{ route('shop') }}">
                                Lihat semua koleksi
                            </a>
                        </div>

                    @endif