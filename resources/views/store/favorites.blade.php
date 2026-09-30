@extends('layouts.store')

@section('title', 'Favorit — Zalina Fashion')

@section('content')

<style>
    .zalina-favorites-page {
        position: relative;
        min-height: 100vh;
        overflow: hidden;
        background:
            radial-gradient(circle at 8% 8%, rgba(155, 118, 83, .13), transparent 28%),
            radial-gradient(circle at 92% 18%, rgba(111, 48, 66, .09), transparent 30%),
            linear-gradient(135deg, #fbf8f5 0%, #f8f1ed 48%, #f5ebe6 100%);
    }

    .zalina-favorites-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .35;
        background-image:
            linear-gradient(rgba(111, 48, 66, .025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(111, 48, 66, .025) 1px, transparent 1px);
        background-size: 42px 42px;
    }

    .zalina-favorites-shell {
        position: relative;
        z-index: 1;
        max-width: 1440px;
        margin: 0 auto;
        padding: 42px 20px 90px;
    }

    .zalina-favorites-hero {
        position: relative;
        overflow: hidden;
        min-height: 285px;
        display: flex;
        align-items: center;
        border: 1px solid rgba(155, 118, 83, .24);
        border-radius: 34px;
        padding: 42px;
        background:
            linear-gradient(120deg, rgba(74, 38, 48, .98), rgba(111, 48, 66, .96) 55%, rgba(91, 46, 58, .96));
        box-shadow:
            0 25px 70px rgba(74, 38, 48, .18),
            inset 0 1px 0 rgba(255, 255, 255, .12);
    }

    .zalina-favorites-hero::before {
        content: "";
        position: absolute;
        width: 420px;
        height: 420px;
        right: -130px;
        top: -170px;
        border: 1px solid rgba(235, 211, 177, .28);
        border-radius: 50%;
    }

    .zalina-favorites-hero::after {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        right: 30px;
        bottom: -220px;
        border: 1px solid rgba(235, 211, 177, .18);
        border-radius: 50%;
    }

    .zalina-favorites-hero-content {
        position: relative;
        z-index: 2;
        max-width: 680px;
    }

    .zalina-favorites-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #e6c99f;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .34em;
        text-transform: uppercase;
    }

    .zalina-favorites-eyebrow::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d6b27e;
    }

    .zalina-favorites-title {
        margin-top: 16px;
        color: #fffaf5;
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(34px, 5vw, 66px);
        font-weight: 500;
        line-height: 1.08;
        letter-spacing: -.045em;
    }

    .zalina-favorites-title em {
        color: #e6c99f;
        font-weight: 400;
    }

    .zalina-favorites-description {
        max-width: 570px;
        margin-top: 18px;
        color: rgba(255, 248, 241, .78);
        font-size: 14px;
        line-height: 1.9;
    }

    .zalina-favorites-stat {
        position: absolute;
        right: 44px;
        bottom: 38px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 12px 17px;
        border: 1px solid rgba(235, 211, 177, .3);
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        backdrop-filter: blur(12px);
        color: #f7dfbb;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .08em;
    }

    .zalina-favorites-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(235, 211, 177, .16);
        color: #f2d5a7;
        font-size: 18px;
    }

    .zalina-favorites-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 34px 2px 22px;
    }

    .zalina-favorites-section-label {
        color: #9b7653;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .28em;
        text-transform: uppercase;
    }

    .zalina-favorites-section-title {
        margin-top: 5px;
        color: #4a2630;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 27px;
        font-weight: 600;
    }

    .zalina-favorites-count {
        border: 1px solid rgba(111, 48, 66, .14);
        border-radius: 999px;
        padding: 9px 15px;
        color: #6f3042;
        background: rgba(255, 255, 255, .6);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
    }

    .zalina-favorites-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 24px;
    }

    .zalina-favorite-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(155, 118, 83, .17);
        border-radius: 25px;
        background: rgba(255, 255, 255, .86);
        box-shadow: 0 12px 35px rgba(74, 38, 48, .07);
        transition:
            transform .35s ease,
            box-shadow .35s ease,
            border-color .35s ease;
    }

    .zalina-favorite-card:hover {
        transform: translateY(-7px);
        border-color: rgba(155, 118, 83, .4);
        box-shadow: 0 22px 50px rgba(74, 38, 48, .15);
    }

    .zalina-favorite-image-wrap {
        position: relative;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        background: #f1e8e2;
    }

    .zalina-favorite-image-wrap::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(
            180deg,
            rgba(74, 38, 48, .02),
            transparent 55%,
            rgba(74, 38, 48, .16)
        );
    }

    .zalina-favorite-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .65s cubic-bezier(.2, .7, .2, 1);
    }

    .zalina-favorite-card:hover .zalina-favorite-image {
        transform: scale(1.07);
    }

    .zalina-favorite-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 3;
        border: 1px solid rgba(255, 255, 255, .4);
        border-radius: 999px;
        padding: 7px 11px;
        color: #fffaf5;
        background: rgba(74, 38, 48, .75);
        backdrop-filter: blur(12px);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .zalina-favorite-remove {
        position: absolute;
        top: 13px;
        right: 13px;
        z-index: 20;
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 42px !important;
        height: 42px !important;
        min-width: 42px;
        padding: 0;
        border: 1px solid rgba(111, 25, 51, .18);
        border-radius: 50%;
        background: #ffffff !important;
        color: #7b2440 !important;
        cursor: pointer;
        opacity: 1 !important;
        visibility: visible !important;
        box-shadow: 0 7px 20px rgba(74, 38, 48, .13);
        transition: all .25s ease;
    }

    .zalina-favorite-remove:hover {
        color: #fff;
        background: #6f3042;
        transform: scale(1.08);
    }

    .zalina-favorite-remove svg {
        width: 18px;
        height: 18px;
    }

    .zalina-favorite-content {
        padding: 20px 20px 21px;
    }

    .zalina-favorite-category {
        color: #b18b65;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .zalina-favorite-name {
        display: -webkit-box;
        overflow: hidden;
        margin-top: 8px;
        color: #4a2630;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 18px;
        font-weight: 600;
        line-height: 1.35;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .zalina-favorite-price {
        margin-top: 13px;
        color: #9b7653;
        font-size: 15px;
        font-weight: 800;
    }

    .zalina-favorite-view {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 19px;
        padding-top: 15px;
        border-top: 1px solid rgba(155, 118, 83, .16);
        color: #6f3042;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .zalina-favorite-view span:last-child {
        font-size: 17px;
        transition: transform .25s ease;
    }

    .zalina-favorite-card:hover .zalina-favorite-view span:last-child {
        transform: translateX(5px);
    }

    .zalina-favorites-empty {
        padding: 80px 25px;
        text-align: center;
        border: 1px solid rgba(155, 118, 83, .22);
        border-radius: 30px;
        background: rgba(255, 255, 255, .72);
        box-shadow: 0 20px 60px rgba(74, 38, 48, .07);
    }

    .zalina-favorites-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        height: 90px;
        margin: 0 auto;
        border: 1px solid rgba(155, 118, 83, .3);
        border-radius: 50%;
        color: #9b7653;
        background: #f5e8e2;
        font-family: Georgia, serif;
        font-size: 45px;
        box-shadow: 0 12px 35px rgba(155, 118, 83, .13);
    }

    .zalina-favorites-empty-title {
        margin-top: 24px;
        color: #4a2630;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 30px;
        font-weight: 600;
    }

    .zalina-favorites-empty-text {
        max-width: 450px;
        margin: 12px auto 0;
        color: #8a7771;
        font-size: 13px;
        line-height: 1.8;
    }

    .zalina-favorites-empty-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 28px;
        border-radius: 999px;
        padding: 14px 24px;
        color: #fffaf5;
        background: linear-gradient(135deg, #6f3042, #4a2630);
        box-shadow: 0 12px 25px rgba(111, 48, 66, .22);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        transition: all .3s ease;
    }

    .zalina-favorites-empty-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 17px 32px rgba(111, 48, 66, .3);
    }

    .zalina-favorites-empty-button span {
        font-size: 18px;
    }

    @media (max-width: 1100px) {
        .zalina-favorites-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .zalina-favorites-stat {
            right: 30px;
        }
    }

    @media (max-width: 760px) {
        .zalina-favorites-shell {
            padding: 25px 14px 60px;
        }

        .zalina-favorites-hero {
            min-height: 330px;
            padding: 30px 25px;
            border-radius: 26px;
        }

        .zalina-favorites-description {
            max-width: 100%;
            font-size: 13px;
        }

        .zalina-favorites-stat {
            right: 25px;
            bottom: 25px;
        }

        .zalina-favorites-toolbar {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
            margin-top: 28px;
        }

        .zalina-favorites-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
        }

        .zalina-favorite-card {
            border-radius: 19px;
        }

        .zalina-favorite-content {
            padding: 15px;
        }

        .zalina-favorite-name {
            font-size: 15px;
        }

        .zalina-favorite-price {
            font-size: 13px;
        }

        .zalina-favorite-view {
            font-size: 9px;
        }

        .zalina-favorite-remove {
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    min-width: 42px;
    border: 1px solid rgba(111, 25, 51, .18);
    border-radius: 999px;
    background: #ffffff !important;
    color: #7b2440 !important;
    cursor: pointer;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 20;
            width: 34px;
            height: 34px;
        }
    }

    @media (max-width: 380px) {
        .zalina-favorites-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="zalina-favorites-page">

    <div class="zalina-favorites-shell">

        <section class="zalina-favorites-hero">
            <div class="zalina-favorites-hero-content">
                <div class="zalina-favorites-eyebrow">
                    Zalina Fashion
                </div>

                <h1 class="zalina-favorites-title">
                    Your <em>Curated</em><br>
                    Collection
                </h1>

                <p class="zalina-favorites-description">
                    Simpan setiap pilihan yang mencerminkan keanggunanmu.
                    Koleksi favoritmu hadir dalam satu ruang eksklusif,
                    siap menemani setiap momen istimewa.
                </p>
            </div>

            <div class="zalina-favorites-stat">
                <span class="zalina-favorites-stat-icon">♡</span>
                <span>{{ $favorites->count() }} Produk Tersimpan</span>
            </div>
        </section>

        @if($favorites->isEmpty())

            <section class="zalina-favorites-empty mt-8">
                <div class="zalina-favorites-empty-icon">
                    ♡
                </div>

                <h2 class="zalina-favorites-empty-title">
                    Belum Ada Pilihan Favorit
                </h2>

                <p class="zalina-favorites-empty-text">
                    Temukan koleksi hijab yang paling sesuai dengan gayamu.
                    Tekan ikon hati untuk menyimpan produk pilihanmu di sini.
                </p>

                <a
                    href="{{ url('/') }}"
                    class="zalina-favorites-empty-button"
                >
                    Jelajahi Koleksi
                    <span>↗</span>
                </a>
            </section>

        @else

            <div class="zalina-favorites-toolbar">
                <div>
                    <div class="zalina-favorites-section-label">
                        Saved with love
                    </div>

                    <h2 class="zalina-favorites-section-title">
                        Koleksi Pilihanmu
                    </h2>
                </div>

                <div class="zalina-favorites-count">
                    {{ $favorites->count() }} FAVORIT
                </div>
            </div>

            <section class="zalina-favorites-grid">

                @foreach($favorites as $favorite)

                    @php
                        $product = $favorite->product;
                    @endphp

                    @if($product)

                        <article class="zalina-favorite-card">

                            <div class="zalina-favorite-image-wrap">

                                <span class="zalina-favorite-badge">
                                    Favorite
                                </span>

                                <button
                                    type="button"
                                    class="zalina-favorite-remove"
                                    onclick="removeFavorite({{ $product->id }}, this)"
                                    aria-label="Hapus {{ $product->name }} dari favorit"
                                    title="Hapus dari favorit"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path d="M12 21s-7.2-4.35-9.5-9.2C.1 7.6 2.1 3.5 6.2 3.1c2.2-.2 4.2 1 5.8 3 1.6-2 3.6-3.2 5.8-3 4.1.4 6.1 4.5 3.7 8.7C19.2 16.65 12 21 12 21z"/>
                                    </svg>
                                </button>

                                <a
                                    href="{{ route('product.show', $product) }}"
                                    class="block h-full"
                                >
                                    @if($product->image_url)

                                        <img
                                            src="{{ $product->image_url }}"
                                            alt="{{ $product->name }}"
                                            class="zalina-favorite-image"
                                            loading="lazy"
                                        >

                                    @else

                                        <div class="flex h-full items-center justify-center text-sm text-[#9b7653]">
                                            Tidak ada gambar
                                        </div>

                                    @endif
                                </a>

                            </div>

                            <div class="zalina-favorite-content">

                                <div class="zalina-favorite-category">
                                    Zalina Collection
                                </div>

                                <a
                                    href="{{ route('product.show', $product) }}"
                                    class="block"
                                >
                                    <h2 class="zalina-favorite-name">
                                        {{ $product->name }}
                                    </h2>

                                    <p class="zalina-favorite-price">
                                        Rp {{ number_format($product->current_price, 0, ',', '.') }}
                                    </p>

                                    <div class="zalina-favorite-view">
                                        <span>Lihat Produk</span>
                                        <span>→</span>
                                    </div>
                                </a>

                            </div>

                        </article>

                    @endif

                @endforeach

            </section>

        @endif

    </div>

</div>

<script>
async function removeFavorite(productId, button) {
    if (!button || button.disabled) return;

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.style.opacity = '0.6';

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

        const response = await fetch(`/favorit/${productId}/toggle`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Gagal menghapus favorit.');
        }

        const card = button.closest('.zalina-favorite-card');

        if (card) {
            card.style.transition = 'all .35s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateY(15px) scale(.97)';

            setTimeout(() => {
                card.remove();

                const remainingCards = document.querySelectorAll(
                    '.zalina-favorite-card'
                );

                if (remainingCards.length === 0) {
                    window.location.reload();
                }
            }, 350);
        }

    } catch (error) {
        console.error('Favorite removal error:', error);

        button.disabled = false;
        button.style.opacity = '1';
        button.innerHTML = originalHtml;

        alert('Favorit gagal dihapus. Silakan coba lagi.');
    }
}
</script>

@endsection