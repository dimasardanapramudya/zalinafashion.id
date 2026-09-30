@extends('layouts.store')

@section('title', 'Tentang Kami — Zalina Fashion')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | ZALINA FASHION — ABOUT PAGE
    |--------------------------------------------------------------------------
    */

    $siteName = 'Zalina Fashion';

    if (isset($settings) && $settings) {
        if ($settings instanceof \Illuminate\Support\Collection) {
            $siteName = $settings->get('site_name', $siteName);
        } elseif (is_array($settings)) {
            $siteName = $settings['site_name'] ?? $siteName;
        }
    }

    $siteName = trim((string) $siteName) ?: 'Zalina Fashion';
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@400;500;600;700&display=swap" rel="stylesheet">

<div class="zf-about">

    {{-- ============================================================
         HERO
    ============================================================= --}}

    <section class="zf-about-hero">

        <div class="zf-about-hero-inner">

            <div class="zf-about-hero-top reveal">
                <div class="zf-eyebrow">
                    <span class="zf-eyebrow-line"></span>
                    <span>ZALINA FASHION</span>
                </div>

                <span class="zf-hero-index">01 / ABOUT</span>
            </div>

            <div class="zf-about-hero-content">

                <div class="zf-about-hero-copy reveal delay-1">

                    <p class="zf-small-label">
                        TENTANG KAMI
                    </p>

                    <h1>
                        Lebih dari
                        <em>sebuah penampilan.</em>
                    </h1>

                    <p class="zf-hero-description">
                        {{ $siteName }} hadir untuk menemani perempuan menemukan
                        gaya yang terasa nyaman, anggun, dan tetap menjadi dirinya sendiri.
                    </p>

                    <div class="zf-hero-actions">

                        <a href="#story" class="zf-button-primary">
                            <span>Kenali Kami</span>
                            <span class="zf-button-arrow">↓</span>
                        </a>

                        <a href="{{ route('shop') }}" class="zf-button-text">
                            Lihat Koleksi
                            <span>→</span>
                        </a>

                    </div>

                </div>

                <div class="zf-about-hero-art reveal delay-2">

                    <div class="zf-art-frame">

                        <div class="zf-art-inner">

                            <div class="zf-art-monogram">
                                ZF
                            </div>

                            <div class="zf-art-word">
                                <span>SOFT</span>
                                <span>TOUCH</span>
                            </div>

                            <div class="zf-art-line"></div>

                            <div class="zf-art-bottom">
                                <span>MODEST</span>
                                <span>FASHION</span>
                            </div>

                        </div>

                    </div>

                    <div class="zf-art-caption">
                        <span>EST.</span>
                        <strong>ZF</strong>
                        <span>COLLECTION</span>
                    </div>

                </div>

            </div>

            <div class="zf-hero-bottom reveal delay-3">

                <div class="zf-hero-note">
                    <span class="zf-note-dot"></span>
                    <span>Discover the story behind the brand</span>
                </div>

                <div class="zf-hero-scroll">
                    <span>SCROLL</span>
                    <span class="zf-scroll-line"></span>
                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         STORY
    ============================================================= --}}

    <section class="zf-story" id="story">

        <div class="zf-container">

            <div class="zf-section-heading reveal">

                <div class="zf-section-number">
                    02
                </div>

                <div>
                    <p class="zf-kicker">
                        OUR STORY
                    </p>

                    <h2>
                        Berawal dari hal sederhana,
                        <em>tumbuh menjadi sesuatu yang berarti.</em>
                    </h2>
                </div>

            </div>


            <div class="zf-story-grid">

                <div class="zf-story-intro reveal delay-1">

                    <span class="zf-story-quote-mark">“</span>

                    <p>
                        Kami percaya bahwa pakaian yang baik bukan hanya tentang
                        bagaimana seseorang terlihat, tetapi juga bagaimana ia
                        merasa ketika mengenakannya.
                    </p>

                </div>


                <div class="zf-story-content reveal delay-2">

                    <p>
                        {{ $siteName }} lahir dari kecintaan terhadap modest fashion
                        dan keinginan untuk menghadirkan hijab serta busana muslim
                        yang dapat menemani berbagai aktivitas perempuan Indonesia.
                    </p>

                    <p>
                        Bagi kami, kenyamanan dan keanggunan tidak perlu dipertentangkan.
                        Keduanya dapat hadir dalam satu pilihan yang sederhana,
                        mudah dikenakan, dan tetap memiliki karakter.
                    </p>

                    <p>
                        Karena itu, setiap koleksi dikembangkan dengan perhatian
                        terhadap detail, pilihan warna, material, dan desain yang
                        mudah dipadukan dengan gaya sehari-hari.
                    </p>

                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         PHILOSOPHY
    ============================================================= --}}

    <section class="zf-philosophy">

        <div class="zf-container">

            <div class="zf-philosophy-card reveal">

                <div class="zf-philosophy-top">

                    <span>03 / OUR PHILOSOPHY</span>

                    <span class="zf-philosophy-symbol">ZF</span>

                </div>

                <div class="zf-philosophy-main">

                    <p class="zf-kicker light">
                        THE ZALINA WAY
                    </p>

                    <h2>
                        Keanggunan tidak harus
                        <em>bersuara keras.</em>
                    </h2>

                    <p>
                        Kami memilih pendekatan yang lebih tenang:
                        warna yang mudah dikenakan, bentuk yang terasa natural,
                        dan detail yang cukup untuk membuat sebuah penampilan
                        terasa istimewa.
                    </p>

                </div>

                <div class="zf-philosophy-bottom">

                    <span>SOFTNESS</span>
                    <span>SIMPLICITY</span>
                    <span>CONFIDENCE</span>

                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         VALUES
    ============================================================= --}}

    <section class="zf-values">

        <div class="zf-container">

            <div class="zf-values-heading reveal">

                <div>

                    <p class="zf-kicker">
                        WHAT WE VALUE
                    </p>

                    <h2>
                        Hal-hal kecil yang
                        <em>kami jaga.</em>
                    </h2>

                </div>

                <p>
                    Setiap keputusan dalam sebuah koleksi berangkat
                    dari prinsip yang sama: membuat pengalaman berpakaian
                    terasa lebih nyaman dan bermakna.
                </p>

            </div>


            <div class="zf-values-grid">

                <article class="zf-value-card reveal">

                    <div class="zf-value-number">
                        01
                    </div>

                    <div class="zf-value-icon">
                        ✦
                    </div>

                    <h3>
                        Kualitas
                    </h3>

                    <p>
                        Memperhatikan material, detail, dan proses agar
                        setiap produk terasa layak menjadi bagian dari
                        keseharian.
                    </p>

                    <span class="zf-card-line"></span>

                </article>


                <article class="zf-value-card reveal delay-1">

                    <div class="zf-value-number">
                        02
                    </div>

                    <div class="zf-value-icon">
                        ◇
                    </div>

                    <h3>
                        Kenyamanan
                    </h3>

                    <p>
                        Desain tidak hanya dipandang dari tampilannya,
                        tetapi juga dari bagaimana ia digunakan dan
                        dirasakan sepanjang hari.
                    </p>

                    <span class="zf-card-line"></span>

                </article>


                <article class="zf-value-card reveal delay-2">

                    <div class="zf-value-number">
                        03
                    </div>

                    <div class="zf-value-icon">
                        ∿
                    </div>

                    <h3>
                        Kesederhanaan
                    </h3>

                    <p>
                        Kami menyukai desain yang tidak berlebihan,
                        mudah dipadukan, dan tetap memiliki karakter.
                    </p>

                    <span class="zf-card-line"></span>

                </article>


                <article class="zf-value-card reveal delay-3">

                    <div class="zf-value-number">
                        04
                    </div>

                    <div class="zf-value-icon">
                        ♡
                    </div>

                    <h3>
                        Kepercayaan
                    </h3>

                    <p>
                        Hubungan dengan pelanggan dibangun melalui
                        pengalaman yang jelas, perhatian, dan pelayanan
                        yang terus kami perbaiki.
                    </p>

                    <span class="zf-card-line"></span>

                </article>

            </div>

        </div>

    </section>


    {{-- ============================================================
         EXPERIENCE
    ============================================================= --}}

    <section class="zf-experience">

        <div class="zf-container">

            <div class="zf-experience-header reveal">

                <div class="zf-section-number">
                    04
                </div>

                <div>

                    <p class="zf-kicker">
                        THE EXPERIENCE
                    </p>

                    <h2>
                        Dari memilih hingga
                        <em>mengenakan.</em>
                    </h2>

                </div>

            </div>


            <div class="zf-experience-list">

                <div class="zf-experience-item reveal">

                    <div class="zf-experience-index">
                        01
                    </div>

                    <div class="zf-experience-title">
                        <span>DISCOVER</span>
                        <h3>Temukan</h3>
                    </div>

                    <p>
                        Jelajahi koleksi berdasarkan gaya, kebutuhan,
                        dan suasana yang ingin kamu hadirkan.
                    </p>

                    <span class="zf-experience-arrow">↗</span>

                </div>


                <div class="zf-experience-item reveal delay-1">

                    <div class="zf-experience-index">
                        02
                    </div>

                    <div class="zf-experience-title">
                        <span>CHOOSE</span>
                        <h3>Pilih</h3>
                    </div>

                    <p>
                        Temukan warna, material, dan desain yang
                        terasa paling sesuai dengan dirimu.
                    </p>

                    <span class="zf-experience-arrow">↗</span>

                </div>


                <div class="zf-experience-item reveal delay-2">

                    <div class="zf-experience-index">
                        03
                    </div>

                    <div class="zf-experience-title">
                        <span>ORDER</span>
                        <h3>Pesan</h3>
                    </div>

                    <p>
                        Nikmati proses pembelian yang sederhana,
                        jelas, dan nyaman dari awal hingga selesai.
                    </p>

                    <span class="zf-experience-arrow">↗</span>

                </div>


                <div class="zf-experience-item reveal delay-3">

                    <div class="zf-experience-index">
                        04
                    </div>

                    <div class="zf-experience-title">
                        <span>ENJOY</span>
                        <h3>Nikmati</h3>
                    </div>

                    <p>
                        Biarkan pilihanmu menjadi bagian dari
                        gaya dan keseharianmu sendiri.
                    </p>

                    <span class="zf-experience-arrow">↗</span>

                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         JOURNEY
    ============================================================= --}}

    <section class="zf-journey">

        <div class="zf-container">

            <div class="zf-journey-header reveal">

                <div class="zf-section-number">
                    05
                </div>

                <div>

                    <p class="zf-kicker">
                        OUR JOURNEY
                    </p>

                    <span class="zf-journey-status">
                        STILL IN MOTION
                    </span>

                    <h2>
                        Tumbuh,
                        <em>tanpa kehilangan arah.</em>
                    </h2>

                    <p class="zf-journey-description">
                        Perjalanan {{ $siteName }} tidak berhenti pada satu koleksi.
                        Kami terus belajar, memperbaiki proses, dan berkembang
                        bersama pelanggan.
                    </p>

                </div>

            </div>


            <div class="zf-journey-track">

                <div class="zf-journey-item reveal">

                    <div class="zf-journey-marker">
                        <span>01</span>
                    </div>

                    <div class="zf-journey-content">

                        <span class="zf-journey-label">
                            BEGIN
                        </span>

                        <h3>
                            Memulai dari rasa
                        </h3>

                        <p>
                            Berangkat dari ketertarikan terhadap modest fashion
                            dan keinginan menghadirkan pilihan yang terasa dekat
                            dengan keseharian.
                        </p>

                    </div>

                </div>


                <div class="zf-journey-item reveal delay-1">

                    <div class="zf-journey-marker">
                        <span>02</span>
                    </div>

                    <div class="zf-journey-content">

                        <span class="zf-journey-label">
                            BUILD
                        </span>

                        <h3>
                            Membangun karakter
                        </h3>

                        <p>
                            Mengembangkan identitas visual, koleksi, dan proses
                            agar setiap bagian dari Zalina Fashion terasa
                            konsisten.
                        </p>

                    </div>

                </div>


                <div class="zf-journey-item reveal delay-2">

                    <div class="zf-journey-marker">
                        <span>03</span>
                    </div>

                    <div class="zf-journey-content">

                        <span class="zf-journey-label">
                            CONNECT
                        </span>

                        <h3>
                            Bertumbuh bersama
                        </h3>

                        <p>
                            Mendengarkan pelanggan, memahami kebutuhan mereka,
                            dan menjadikan pengalaman nyata sebagai bagian
                            dari proses berkembang.
                        </p>

                    </div>

                </div>


                <div class="zf-journey-item reveal delay-3">

                    <div class="zf-journey-marker">
                        <span>04</span>
                    </div>

                    <div class="zf-journey-content">

                        <span class="zf-journey-label">
                            FUTURE
                        </span>

                        <h3>
                            Melangkah lebih jauh
                        </h3>

                        <p>
                            Terus belajar dan menghadirkan koleksi serta
                            pengalaman yang relevan tanpa kehilangan karakter
                            Zalina Fashion.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ============================================================
         PROMISE
    ============================================================= --}}

    <section class="zf-promise">

        <div class="zf-container">

            <div class="zf-promise-card reveal">

                <div class="zf-promise-decoration">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <p class="zf-kicker">
                    OUR PROMISE
                </p>

                <h2>
                    Pilihan yang indah,
                    <em>tanpa terasa berlebihan.</em>
                </h2>

                <p class="zf-promise-text">
                    Kami ingin setiap perempuan dapat menemukan sesuatu
                    yang terasa tepat untuk dirinya — nyaman dikenakan,
                    mudah dipadukan, dan tetap memiliki sentuhan personal.
                </p>

                <a href="{{ route('shop') }}" class="zf-promise-button">
                    <span>Jelajahi Koleksi</span>
                    <span>→</span>
                </a>

            </div>

        </div>

    </section>


    {{-- ============================================================
         CLOSING
    ============================================================= --}}

    <section class="zf-closing">

        <div class="zf-container">

            <div class="zf-closing-inner reveal">

                <div class="zf-closing-mark">
                    ZF
                </div>

                <p>
                    Soft touch, softly you.
                </p>

                <h2>
                    Terima kasih telah
                    menjadi bagian dari
                    <em>{{ $siteName }}.</em>
                </h2>

                <a href="{{ route('home') }}" class="zf-closing-link">
                    Kembali ke Beranda
                    <span>→</span>
                </a>

            </div>

        </div>

    </section>

</div>


<style>

    /* ================================================================
       ZALINA FASHION — ABOUT
       LIGHT EDITORIAL / COLLECTION STYLE
    ================================================================= */

    .zf-about {
        --zf-cream: #fbf6f1;
        --zf-cream-deep: #f5ece3;
        --zf-maroon: #631f2b;
        --zf-maroon-dark: #4a1520;
        --zf-maroon-soft: #7c2d3a;
        --zf-gold: #b98a3d;
        --zf-gold-light: #f6dfaa;
        --zf-line: rgba(99, 31, 43, .14);
        --zf-line-gold: rgba(185, 138, 61, .34);
        --zf-text: #351b24;
        --zf-muted: #806b72;
        --zf-white: #fffdfb;

        position: relative;
        width: 100%;
        overflow: hidden;
        background: var(--zf-cream);
        color: var(--zf-text);
        font-family: 'DM Sans', sans-serif;
    }


    .zf-about *,
    .zf-about *::before,
    .zf-about *::after {
        box-sizing: border-box;
    }


    .zf-about a {
        text-decoration: none;
    }


    .zf-container {
        width: min(1180px, calc(100% - 48px));
        margin: 0 auto;
    }


    /* ================================================================
       TYPOGRAPHY
    ================================================================= */

    .zf-about h1,
    .zf-about h2,
    .zf-about h3 {
        font-family: 'Playfair Display', serif;
        font-weight: 500;
    }


    .zf-kicker {
        margin: 0;
        color: var(--zf-gold);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .24em;
        text-transform: uppercase;
    }


    .zf-kicker::before {
        content: '';
        display: inline-block;
        width: 28px;
        height: 1px;
        margin-right: 11px;
        vertical-align: middle;
        background: currentColor;
    }


    .zf-kicker.light {
        color: var(--zf-gold-light);
    }


    .zf-section-number {
        color: var(--zf-gold);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .18em;
    }


    /* ================================================================
       HERO
    ================================================================= */

    .zf-about-hero {
        position: relative;
        min-height: 720px;
        background:
            radial-gradient(
                circle at 88% 15%,
                rgba(185, 138, 61, .10),
                transparent 24%
            ),
            var(--zf-cream);
        border-bottom: 1px solid var(--zf-line);
    }


    .zf-about-hero-inner {
        width: min(1280px, calc(100% - 64px));
        min-height: 720px;
        margin: 0 auto;
        padding: 54px 0 34px;
        display: flex;
        flex-direction: column;
    }


    .zf-about-hero-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--zf-line);
        padding-bottom: 18px;
    }


    .zf-eyebrow {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--zf-maroon);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .22em;
    }


    .zf-eyebrow-line {
        width: 32px;
        height: 1px;
        background: var(--zf-gold);
    }


    .zf-hero-index {
        color: var(--zf-muted);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .18em;
    }


    .zf-about-hero-content {
        flex: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
        align-items: center;
        gap: 80px;
        padding: 70px 5%;
    }


    .zf-about-hero-copy {
        max-width: 650px;
    }


    .zf-small-label {
        margin: 0 0 20px;
        color: var(--zf-gold);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .22em;
    }


    .zf-about-hero-copy h1 {
        margin: 0;
        color: var(--zf-maroon);
        font-size: clamp(4rem, 7vw, 7rem);
        line-height: .92;
        letter-spacing: -.055em;
    }


    .zf-about-hero-copy h1 em {
        display: block;
        color: var(--zf-maroon-dark);
        font-weight: 400;
    }


    .zf-hero-description {
        max-width: 570px;
        margin: 34px 0 0;
        color: var(--zf-muted);
        font-size: 15px;
        line-height: 1.9;
    }


    .zf-hero-actions {
        display: flex;
        align-items: center;
        gap: 28px;
        margin-top: 38px;
    }


    .zf-button-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        min-height: 50px;
        padding: 0 23px;
        background: var(--zf-maroon);
        color: #fff;
        border: 1px solid var(--zf-maroon);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        transition:
            transform .25s ease,
            background .25s ease,
            box-shadow .25s ease;
    }


    .zf-button-primary:hover {
        transform: translateY(-2px);
        background: var(--zf-maroon-dark);
        box-shadow: 0 12px 28px rgba(74, 21, 32, .15);
    }


    .zf-button-arrow {
        color: var(--zf-gold-light);
        font-size: 17px;
        line-height: 1;
    }


    .zf-button-text {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: var(--zf-maroon);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }


    .zf-button-text span {
        color: var(--zf-gold);
        font-size: 17px;
        transition: transform .25s ease;
    }


    .zf-button-text:hover span {
        transform: translateX(5px);
    }


    /* HERO ART */

    .zf-about-hero-art {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }


    .zf-art-frame {
        position: relative;
        width: min(360px, 100%);
        aspect-ratio: 4 / 5;
        padding: 16px;
        border: 1px solid var(--zf-line-gold);
    }


    .zf-art-frame::before,
    .zf-art-frame::after {
        content: '';
        position: absolute;
        pointer-events: none;
    }


    .zf-art-frame::before {
        inset: 7px;
        border: 1px solid rgba(185, 138, 61, .13);
    }


    .zf-art-frame::after {
        width: 70px;
        height: 70px;
        right: -12px;
        bottom: -12px;
        border-right: 1px solid var(--zf-gold);
        border-bottom: 1px solid var(--zf-gold);
    }


    .zf-art-inner {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 32px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 253, 251, .96),
                rgba(245, 236, 227, .92)
            );
    }


    .zf-art-inner::before {
        content: '';
        position: absolute;
        width: 210px;
        height: 210px;
        border: 1px solid rgba(185, 138, 61, .18);
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }


    .zf-art-inner::after {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        border: 1px solid rgba(99, 31, 43, .10);
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }


    .zf-art-monogram {
        position: relative;
        z-index: 2;
        color: var(--zf-maroon);
        font-family: 'Playfair Display', serif;
        font-size: 25px;
        letter-spacing: .08em;
    }


    .zf-art-word {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        color: var(--zf-maroon);
        font-family: 'Playfair Display', serif;
        font-size: clamp(3rem, 6vw, 5rem);
        line-height: .78;
        letter-spacing: -.06em;
    }


    .zf-art-word span:last-child {
        color: var(--zf-gold);
        font-style: italic;
    }


    .zf-art-line {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 18%;
        width: 64%;
        height: 1px;
        background: var(--zf-line-gold);
    }


    .zf-art-bottom {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        color: var(--zf-muted);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .22em;
    }


    .zf-art-caption {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 18px;
        color: var(--zf-muted);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .18em;
    }


    .zf-art-caption strong {
        color: var(--zf-gold);
        font-family: 'Playfair Display', serif;
        font-size: 15px;
        font-weight: 500;
    }


    .zf-hero-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 5% 0;
        border-top: 1px solid var(--zf-line);
    }


    .zf-hero-note,
    .zf-hero-scroll {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--zf-muted);
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
    }


    .zf-note-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--zf-gold);
    }


    .zf-scroll-line {
        display: inline-block;
        width: 45px;
        height: 1px;
        margin-left: 5px;
        background: var(--zf-gold);
    }


    /* ================================================================
       STORY
    ================================================================= */

    .zf-story {
        padding: 120px 0;
        background: var(--zf-cream);
    }


    .zf-section-heading {
        display: grid;
        grid-template-columns: 70px minmax(0, 700px);
        gap: 32px;
        align-items: start;
    }


    .zf-section-heading h2 {
        max-width: 700px;
        margin: 17px 0 0;
        color: var(--zf-maroon);
        font-size: clamp(2.5rem, 5vw, 4.8rem);
        line-height: 1.04;
        letter-spacing: -.045em;
    }


    .zf-section-heading h2 em {
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-story-grid {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 100px;
        margin-top: 85px;
        padding-left: 102px;
    }


    .zf-story-intro {
        position: relative;
        padding: 32px 0 0 30px;
        border-left: 1px solid var(--zf-gold);
    }


    .zf-story-quote-mark {
        display: block;
        margin-bottom: 15px;
        color: var(--zf-gold);
        font-family: 'Playfair Display', serif;
        font-size: 55px;
        line-height: .6;
    }


    .zf-story-intro p {
        margin: 0;
        color: var(--zf-maroon);
        font-family: 'Playfair Display', serif;
        font-size: 25px;
        line-height: 1.45;
    }


    .zf-story-content {
        max-width: 610px;
    }


    .zf-story-content p {
        margin: 0 0 24px;
        color: var(--zf-muted);
        font-size: 14px;
        line-height: 1.95;
    }


    .zf-story-content p:last-child {
        margin-bottom: 0;
    }


    /* ================================================================
       PHILOSOPHY
    ================================================================= */

    .zf-philosophy {
        padding: 35px 0 120px;
        background: var(--zf-cream);
    }


    .zf-philosophy-card {
        position: relative;
        min-height: 530px;
        overflow: hidden;
        padding: 48px 55px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: var(--zf-maroon);
        color: #fff;
    }


    .zf-philosophy-card::before {
        content: '';
        position: absolute;
        width: 430px;
        height: 430px;
        border: 1px solid rgba(246, 223, 170, .17);
        border-radius: 50%;
        right: -110px;
        top: -130px;
    }


    .zf-philosophy-card::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border: 1px solid rgba(246, 223, 170, .12);
        border-radius: 50%;
        right: -40px;
        top: -65px;
    }


    .zf-philosophy-top {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        color: var(--zf-gold-light);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .2em;
    }


    .zf-philosophy-symbol {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        font-weight: 500;
    }


    .zf-philosophy-main {
        position: relative;
        z-index: 2;
        max-width: 790px;
        padding-left: 7%;
    }


    .zf-philosophy-main h2 {
        margin: 20px 0 25px;
        color: #fff;
        font-size: clamp(3rem, 6vw, 6rem);
        line-height: .98;
        letter-spacing: -.055em;
    }


    .zf-philosophy-main h2 em {
        display: block;
        color: var(--zf-gold-light);
        font-weight: 400;
    }


    .zf-philosophy-main > p:last-child {
        max-width: 580px;
        margin: 0;
        color: rgba(255, 255, 255, .72);
        font-size: 14px;
        line-height: 1.9;
    }


    .zf-philosophy-bottom {
        position: relative;
        z-index: 2;
        display: flex;
        gap: 45px;
        padding-left: 7%;
        color: rgba(246, 223, 170, .75);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .22em;
    }


    /* ================================================================
       VALUES
    ================================================================= */

    .zf-values {
        padding: 120px 0;
        background: var(--zf-cream-deep);
        border-top: 1px solid var(--zf-line);
        border-bottom: 1px solid var(--zf-line);
    }


    .zf-values-heading {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 80px;
        align-items: end;
    }


    .zf-values-heading h2 {
        margin: 18px 0 0;
        color: var(--zf-maroon);
        font-size: clamp(2.7rem, 5vw, 4.7rem);
        line-height: 1;
        letter-spacing: -.045em;
    }


    .zf-values-heading h2 em {
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-values-heading > p {
        margin: 0;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 1.85;
    }


    .zf-values-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 65px;
    }


    .zf-value-card {
        position: relative;
        min-height: 370px;
        padding: 27px;
        display: flex;
        flex-direction: column;
        background: rgba(255, 253, 251, .76);
        border: 1px solid rgba(99, 31, 43, .09);
        transition:
            transform .3s ease,
            border-color .3s ease,
            box-shadow .3s ease;
    }


    .zf-value-card:hover {
        transform: translateY(-7px);
        border-color: var(--zf-line-gold);
        box-shadow: 0 18px 40px rgba(74, 21, 32, .07);
    }


    .zf-value-number {
        color: var(--zf-gold);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .16em;
    }


    .zf-value-icon {
        width: 55px;
        height: 55px;
        margin-top: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zf-line-gold);
        color: var(--zf-gold);
        font-family: 'Playfair Display', serif;
        font-size: 25px;
    }


    .zf-value-card h3 {
        margin: 23px 0 10px;
        color: var(--zf-maroon);
        font-size: 25px;
    }


    .zf-value-card p {
        margin: 0;
        color: var(--zf-muted);
        font-size: 12px;
        line-height: 1.8;
    }


    .zf-card-line {
        width: 32px;
        height: 1px;
        margin-top: auto;
        background: var(--zf-gold);
    }


    /* ================================================================
       EXPERIENCE
    ================================================================= */

    .zf-experience {
        padding: 120px 0;
        background: var(--zf-cream);
    }


    .zf-experience-header {
        display: grid;
        grid-template-columns: 70px minmax(0, 700px);
        gap: 32px;
    }


    .zf-experience-header h2 {
        margin: 17px 0 0;
        color: var(--zf-maroon);
        font-size: clamp(2.7rem, 5vw, 4.8rem);
        line-height: 1;
        letter-spacing: -.045em;
    }


    .zf-experience-header h2 em {
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-experience-list {
        margin-top: 75px;
        border-top: 1px solid var(--zf-line);
    }


    .zf-experience-item {
        display: grid;
        grid-template-columns: 75px 230px 1fr 30px;
        align-items: center;
        gap: 30px;
        min-height: 125px;
        border-bottom: 1px solid var(--zf-line);
        transition: padding .3s ease, background .3s ease;
    }


    .zf-experience-item:hover {
        padding-left: 15px;
        background: rgba(245, 236, 227, .52);
    }


    .zf-experience-index {
        color: var(--zf-gold);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
    }


    .zf-experience-title span {
        color: var(--zf-gold);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .2em;
    }


    .zf-experience-title h3 {
        margin: 7px 0 0;
        color: var(--zf-maroon);
        font-size: 28px;
    }


    .zf-experience-item > p {
        max-width: 500px;
        margin: 0;
        color: var(--zf-muted);
        font-size: 12px;
        line-height: 1.8;
    }


    .zf-experience-arrow {
        color: var(--zf-gold);
        font-size: 20px;
        transition: transform .3s ease;
    }


    .zf-experience-item:hover .zf-experience-arrow {
        transform: translate(4px, -4px);
    }


    /* ================================================================
       JOURNEY
    ================================================================= */

    .zf-journey {
        padding: 120px 0;
        background: var(--zf-cream-deep);
        border-top: 1px solid var(--zf-line);
    }


    .zf-journey-header {
        display: grid;
        grid-template-columns: 70px minmax(0, 850px);
        gap: 32px;
    }


    .zf-journey-status {
        display: inline-block;
        margin-top: 20px;
        padding: 7px 11px;
        border: 1px solid var(--zf-line-gold);
        color: var(--zf-gold);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .18em;
    }


    .zf-journey-header h2 {
        margin: 22px 0 0;
        color: var(--zf-maroon);
        font-size: clamp(3rem, 6vw, 5.8rem);
        line-height: .98;
        letter-spacing: -.055em;
    }


    .zf-journey-header h2 em {
        display: block;
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-journey-description {
        max-width: 570px;
        margin: 28px 0 0;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 1.85;
    }


    .zf-journey-track {
        position: relative;
        margin-top: 90px;
        padding-left: 102px;
    }


    .zf-journey-track::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 22px;
        bottom: 40px;
        width: 1px;
        background: var(--zf-line-gold);
    }


    .zf-journey-item {
        position: relative;
        display: grid;
        grid-template-columns: 65px minmax(0, 600px);
        gap: 35px;
        padding-bottom: 70px;
    }


    .zf-journey-item:last-child {
        padding-bottom: 0;
    }


    .zf-journey-marker {
        position: relative;
        z-index: 2;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--zf-cream-deep);
        border: 1px solid var(--zf-gold);
    }


    .zf-journey-marker span {
        color: var(--zf-maroon);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .08em;
    }


    .zf-journey-content {
        padding-top: 3px;
    }


    .zf-journey-label {
        color: var(--zf-gold);
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .22em;
    }


    .zf-journey-content h3 {
        margin: 9px 0 11px;
        color: var(--zf-maroon);
        font-size: 29px;
    }


    .zf-journey-content p {
        max-width: 590px;
        margin: 0;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 1.85;
    }


    /* ================================================================
       PROMISE
    ================================================================= */

    .zf-promise {
        padding: 110px 0;
        background: var(--zf-cream);
    }


    .zf-promise-card {
        position: relative;
        overflow: hidden;
        padding: 90px 10%;
        text-align: center;
        background:
            linear-gradient(
                135deg,
                #f5ece3 0%,
                #fbf6f1 50%,
                #f5ece3 100%
            );
        border: 1px solid var(--zf-line-gold);
    }


    .zf-promise-card::before,
    .zf-promise-card::after {
        content: '';
        position: absolute;
        border: 1px solid rgba(185, 138, 61, .17);
        border-radius: 50%;
        pointer-events: none;
    }


    .zf-promise-card::before {
        width: 330px;
        height: 330px;
        left: -190px;
        top: -170px;
    }


    .zf-promise-card::after {
        width: 280px;
        height: 280px;
        right: -160px;
        bottom: -150px;
    }


    .zf-promise-decoration {
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
    }


    .zf-promise-decoration span {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--zf-gold);
    }


    .zf-promise-card h2 {
        position: relative;
        z-index: 2;
        max-width: 850px;
        margin: 20px auto 22px;
        color: var(--zf-maroon);
        font-size: clamp(2.8rem, 5vw, 5rem);
        line-height: 1;
        letter-spacing: -.045em;
    }


    .zf-promise-card h2 em {
        display: block;
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-promise-text {
        position: relative;
        z-index: 2;
        max-width: 590px;
        margin: 0 auto;
        color: var(--zf-muted);
        font-size: 13px;
        line-height: 1.9;
    }


    .zf-promise-button {
        position: relative;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 28px;
        margin-top: 32px;
        padding: 16px 23px;
        background: var(--zf-maroon);
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        transition:
            transform .25s ease,
            background .25s ease;
    }


    .zf-promise-button span:last-child {
        color: var(--zf-gold-light);
        font-size: 16px;
    }


    .zf-promise-button:hover {
        transform: translateY(-3px);
        background: var(--zf-maroon-dark);
    }


    /* ================================================================
       CLOSING
    ================================================================= */

    .zf-closing {
        padding: 115px 0 125px;
        background: var(--zf-cream);
        border-top: 1px solid var(--zf-line);
    }


    .zf-closing-inner {
        text-align: center;
    }


    .zf-closing-mark {
        width: 68px;
        height: 68px;
        margin: 0 auto 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--zf-gold);
        color: var(--zf-maroon);
        font-family: 'Playfair Display', serif;
        font-size: 22px;
    }


    .zf-closing-inner > p {
        margin: 0;
        color: var(--zf-gold);
        font-family: 'Playfair Display', serif;
        font-size: 17px;
        font-style: italic;
    }


    .zf-closing-inner h2 {
        max-width: 700px;
        margin: 22px auto 30px;
        color: var(--zf-maroon);
        font-size: clamp(2.5rem, 5vw, 4.5rem);
        line-height: 1.02;
        letter-spacing: -.045em;
    }


    .zf-closing-inner h2 em {
        color: var(--zf-gold);
        font-weight: 400;
    }


    .zf-closing-link {
        display: inline-flex;
        align-items: center;
        gap: 13px;
        color: var(--zf-maroon);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .15em;
        text-transform: uppercase;
    }


    .zf-closing-link span {
        color: var(--zf-gold);
        font-size: 17px;
        transition: transform .25s ease;
    }


    .zf-closing-link:hover span {
        transform: translateX(5px);
    }


    /* ================================================================
       REVEAL ANIMATION
    ================================================================= */

    .zf-about .reveal {
        opacity: 0;
        transform: translateY(22px);
        transition:
            opacity .7s ease,
            transform .7s cubic-bezier(.22, .61, .36, 1);
    }


    .zf-about .reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }


    .zf-about .delay-1 {
        transition-delay: .08s;
    }


    .zf-about .delay-2 {
        transition-delay: .16s;
    }


    .zf-about .delay-3 {
        transition-delay: .24s;
    }


    /* ================================================================
       TABLET
    ================================================================= */

    @media (max-width: 900px) {

        .zf-about-hero,
        .zf-about-hero-inner {
            min-height: auto;
        }


        .zf-about-hero-inner {
            padding-top: 38px;
        }


        .zf-about-hero-content {
            grid-template-columns: 1fr;
            gap: 55px;
            padding: 75px 4% 65px;
        }


        .zf-about-hero-copy h1 {
            font-size: clamp(3.6rem, 11vw, 6rem);
        }


        .zf-about-hero-art {
            max-width: 390px;
            margin: 0 auto;
        }


        .zf-hero-bottom {
            padding-left: 4%;
            padding-right: 4%;
        }


        .zf-story-grid {
            grid-template-columns: 1fr;
            gap: 50px;
            padding-left: 102px;
        }


        .zf-values-heading {
            grid-template-columns: 1fr;
            gap: 25px;
        }


        .zf-values-grid {
            grid-template-columns: repeat(2, 1fr);
        }


        .zf-experience-item {
            grid-template-columns: 60px 180px 1fr 25px;
            gap: 20px;
        }


        .zf-journey-track {
            padding-left: 65px;
        }


        .zf-journey-track::before {
            left: 20px;
        }

    }


    /* ================================================================
       MOBILE
    ================================================================= */

    @media (max-width: 640px) {

        .zf-container {
            width: min(100% - 30px, 1180px);
        }


        .zf-about-hero-inner {
            width: calc(100% - 30px);
            padding-top: 28px;
        }


        .zf-about-hero-top {
            padding-bottom: 14px;
        }


        .zf-eyebrow {
            font-size: 8px;
        }


        .zf-eyebrow-line {
            width: 22px;
        }


        .zf-hero-index {
            font-size: 7px;
        }


        .zf-about-hero-content {
            padding: 55px 2% 45px;
            gap: 48px;
        }


        .zf-small-label {
            margin-bottom: 17px;
            font-size: 8px;
        }


        .zf-about-hero-copy h1 {
            font-size: clamp(3rem, 15vw, 4.7rem);
        }


        .zf-hero-description {
            margin-top: 25px;
            font-size: 12px;
            line-height: 1.8;
        }


        .zf-hero-actions {
            align-items: flex-start;
            flex-direction: column;
            gap: 22px;
            margin-top: 30px;
        }


        .zf-button-primary {
            width: 100%;
        }


        .zf-about-hero-art {
            width: 100%;
        }


        .zf-art-frame {
            width: min(300px, 85vw);
        }


        .zf-art-inner {
            padding: 24px;
        }


        .zf-art-word {
            font-size: 3.4rem;
        }


        .zf-hero-bottom {
            padding: 17px 2% 0;
        }


        .zf-hero-note {
            max-width: 180px;
            font-size: 7px;
            line-height: 1.5;
        }


        .zf-hero-scroll {
            font-size: 7px;
        }


        .zf-story,
        .zf-values,
        .zf-experience,
        .zf-journey {
            padding: 78px 0;
        }


        .zf-philosophy {
            padding: 0 0 78px;
        }


        .zf-section-heading,
        .zf-experience-header,
        .zf-journey-header {
            grid-template-columns: 40px 1fr;
            gap: 18px;
        }


        .zf-section-heading h2,
        .zf-experience-header h2 {
            margin-top: 14px;
            font-size: 2.55rem;
        }


        .zf-story-grid {
            gap: 38px;
            margin-top: 55px;
            padding-left: 0;
        }


        .zf-story-intro {
            padding: 25px 0 0 20px;
        }


        .zf-story-intro p {
            font-size: 21px;
        }


        .zf-story-content p {
            font-size: 12px;
            line-height: 1.85;
        }


        .zf-philosophy-card {
            min-height: 520px;
            padding: 30px 25px;
        }


        .zf-philosophy-main {
            padding-left: 0;
        }


        .zf-philosophy-main h2 {
            font-size: 3.15rem;
        }


        .zf-philosophy-main > p:last-child {
            font-size: 12px;
        }


        .zf-philosophy-bottom {
            flex-wrap: wrap;
            gap: 15px 25px;
            padding-left: 0;
            font-size: 7px;
        }


        .zf-values-heading h2 {
            font-size: 2.8rem;
        }


        .zf-values-heading > p {
            font-size: 12px;
            line-height: 1.8;
        }


        .zf-values-grid {
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 40px;
        }


        .zf-value-card {
            min-height: 310px;
            padding: 24px;
        }


        .zf-value-icon {
            margin-top: 35px;
        }


        .zf-experience-list {
            margin-top: 48px;
        }


        .zf-experience-item {
            grid-template-columns: 38px 1fr 22px;
            gap: 13px;
            padding: 22px 0;
            min-height: auto;
        }


        .zf-experience-item:hover {
            padding-left: 0;
        }


        .zf-experience-title h3 {
            font-size: 23px;
        }


        .zf-experience-item > p {
            grid-column: 2 / 3;
            font-size: 11px;
            line-height: 1.75;
        }


        .zf-experience-arrow {
            grid-column: 3;
            grid-row: 1;
        }


        .zf-journey-status {
            font-size: 7px;
        }


        .zf-journey-header h2 {
            font-size: 3rem;
        }


        .zf-journey-description {
            font-size: 12px;
        }


        .zf-journey-track {
            margin-top: 60px;
            padding-left: 48px;
        }


        .zf-journey-track::before {
            left: 16px;
            top: 20px;
        }


        .zf-journey-item {
            grid-template-columns: 38px 1fr;
            gap: 22px;
            padding-bottom: 55px;
        }


        .zf-journey-marker {
            width: 34px;
            height: 34px;
        }


        .zf-journey-marker span {
            font-size: 8px;
        }


        .zf-journey-content h3 {
            font-size: 24px;
        }


        .zf-journey-content p {
            font-size: 11px;
            line-height: 1.8;
        }


        .zf-promise {
            padding: 60px 0;
        }


        .zf-promise-card {
            padding: 75px 24px;
        }


        .zf-promise-card h2 {
            font-size: 2.65rem;
        }


        .zf-promise-text {
            font-size: 11px;
            line-height: 1.8;
        }


        .zf-closing {
            padding: 80px 0;
        }


        .zf-closing-inner h2 {
            font-size: 2.65rem;
        }


        .zf-closing-inner > p {
            font-size: 15px;
        }

    }


    /* ================================================================
       REDUCED MOTION
    ================================================================= */

    @media (prefers-reduced-motion: reduce) {

        .zf-about .reveal,
        .zf-about .reveal.is-visible {
            opacity: 1;
            transform: none;
            transition: none;
        }


        .zf-about *,
        .zf-about *::before,
        .zf-about *::after {
            scroll-behavior: auto !important;
            animation: none !important;
            transition: none !important;
        }

    }

</style>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const page = document.querySelector('.zf-about');

    if (!page) {
        return;
    }


    const revealElements = page.querySelectorAll('.reveal');


    if ('IntersectionObserver' in window) {

        const observer = new IntersectionObserver(function (entries, observerInstance) {

            entries.forEach(function (entry) {

                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');

                observerInstance.unobserve(entry.target);

            });

        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -30px 0px'
        });


        revealElements.forEach(function (element) {
            observer.observe(element);
        });

    } else {

        revealElements.forEach(function (element) {
            element.classList.add('is-visible');
        });

    }

});

</script>

@endsection
