@extends('layouts.store')

@section('title', 'Daftar Akun — Zalina Fashion')

@section('content')

<div class="min-h-[calc(100vh-4rem)] bg-neutral-50">

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">

        <div class="grid lg:grid-cols-5 rounded-2xl sm:rounded-3xl overflow-hidden border border-maroon-100 bg-white shadow-sm shadow-maroon-900/5">

            {{-- ================================================================= --}}
            {{-- LEFT — BRAND PANEL (light, no dark background) --}}
            {{-- ================================================================= --}}

            <div class="relative flex flex-col justify-between bg-maroon-50/60 text-maroon-900 px-6 sm:px-8 lg:px-10 xl:px-12 py-8 sm:py-10 lg:py-12 lg:col-span-2 border-b lg:border-b-0 lg:border-r border-maroon-100 overflow-hidden">

                <div class="pointer-events-none absolute -bottom-20 -right-20 w-64 h-64 rounded-full border border-maroon-200/60 hidden lg:block"></div>
                <div class="pointer-events-none absolute -bottom-6 -right-6 w-64 h-64 rounded-full border border-maroon-200/60 hidden lg:block"></div>

                <a href="{{ route('home') }}" class="relative inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-maroon-700 hover:text-maroon-900 transition mb-6 lg:mb-0">
                    Zalina Fashion
                </a>

                <div class="relative max-w-sm">
                    <span class="inline-block text-[11px] font-bold uppercase tracking-[0.3em] text-maroon-500 mb-3 lg:mb-5">
                        New Customer
                    </span>

                    <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl xl:text-[2.75rem] leading-[1.15] text-maroon-950 mb-4 lg:mb-6">
                        Satu akun, segala kemudahan.
                    </h1>

                    <ul class="space-y-2.5 lg:space-y-3 text-sm text-maroon-700/80">
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-maroon-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            Riwayat pesanan tersimpan rapi
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-maroon-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            Pantau status pembayaran &amp; pengiriman
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-maroon-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            Akses receipt kapan saja
                        </li>
                    </ul>
                </div>

                <p class="relative text-[11px] text-maroon-400 tracking-wide mt-6 lg:mt-0 hidden lg:block">
                    &copy; {{ date('Y') }} Zalina Fashion. Seluruh hak cipta dilindungi.
                </p>

            </div>


            {{-- ================================================================= --}}
            {{-- RIGHT — FORM PANEL --}}
            {{-- ================================================================= --}}

            <div class="lg:col-span-3 flex items-center justify-center bg-white px-5 sm:px-10 lg:px-14 py-10 sm:py-14">

                <div class="w-full max-w-sm">

                    <div class="mb-8">
                        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-maroon-400 mb-2">
                            New Customer
                        </p>
                        <h2 class="font-serif text-3xl text-maroon-950">
                            Buat Akun
                        </h2>
                    </div>


                    {{-- ============================================= --}}
                    {{-- FLASH MESSAGES --}}
                    {{-- ============================================= --}}

                    @if (session('error'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-maroon-200 bg-maroon-50 px-4 py-3">
                            <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-maroon-700 shrink-0"></span>
                            <p class="text-sm text-maroon-800 leading-5">{{ session('error') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-maroon-200 bg-maroon-50 px-4 py-3">
                            <ul class="text-sm text-maroon-800 leading-6 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    {{-- ============================================= --}}
                    {{-- GOOGLE REGISTER --}}
                    {{-- ============================================= --}}

                    <a
                        href="{{ route('google.login') }}"
                        class="w-full flex items-center justify-center gap-3 border border-neutral-200 hover:border-maroon-200 hover:bg-maroon-50/40 py-3.5 rounded-xl font-medium text-neutral-700 transition mb-6"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.09-1.93 3.27-4.78 3.27-8.1z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.65l-3.57-2.77c-.98.66-2.24 1.05-3.71 1.05-2.87 0-5.3-1.94-6.17-4.55H2.14v2.84A11 11 0 0012 23z"/>
                            <path fill="#FBBC05" d="M5.83 14.08A6.62 6.62 0 015.5 12c0-.72.12-1.42.33-2.08V7.08H2.14A11 11 0 001 12c0 1.77.42 3.45 1.14 4.92l3.69-2.84z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.2 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 002.14 7.08l3.69 2.84C6.7 7.31 9.13 5.38 12 5.38z"/>
                        </svg>
                        Daftar dengan Google
                    </a>

                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex-1 h-px bg-neutral-200"></div>
                        <span class="text-[11px] uppercase tracking-widest text-neutral-400 whitespace-nowrap">atau dengan email</span>
                        <div class="flex-1 h-px bg-neutral-200"></div>
                    </div>


                    {{-- ============================================= --}}
                    {{-- REGISTER FORM --}}
                    {{-- ============================================= --}}

                    <form method="POST" action="{{ route('register') }}" class="space-y-5">

                        @csrf

                        <div>
                            <label for="register-name" class="block text-xs font-semibold text-neutral-700 mb-2">
                                Nama Lengkap
                            </label>
                            <input
                                id="register-name"
                                required
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                placeholder="Nama lengkap"
                                class="w-full border border-neutral-200 rounded-xl px-4 py-3.5 sm:py-3 text-base sm:text-sm outline-none focus:border-maroon-400 focus:ring-1 focus:ring-maroon-300 transition"
                            >
                        </div>

                        <div>
                            <label for="register-email" class="block text-xs font-semibold text-neutral-700 mb-2">
                                Email
                            </label>
                            <input
                                id="register-email"
                                required
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                inputmode="email"
                                placeholder="nama@email.com"
                                class="w-full border border-neutral-200 rounded-xl px-4 py-3.5 sm:py-3 text-base sm:text-sm outline-none focus:border-maroon-400 focus:ring-1 focus:ring-maroon-300 transition"
                            >
                        </div>

                        <div>
                            <label for="register-password" class="block text-xs font-semibold text-neutral-700 mb-2">
                                Password
                            </label>

                            <div class="relative">
                                <input
                                    id="register-password"
                                    required
                                    type="password"
                                    name="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="Password minimal 8 karakter"
                                    class="w-full border border-neutral-200 rounded-xl px-4 py-3.5 sm:py-3 pr-11 text-base sm:text-sm outline-none focus:border-maroon-400 focus:ring-1 focus:ring-maroon-300 transition"
                                >

                                <button
                                    type="button"
                                    class="zalina-toggle-pw absolute inset-y-0 right-0 flex items-center px-3.5 text-neutral-400 hover:text-maroon-700 transition"
                                    data-target="register-password"
                                    aria-label="Tampilkan password"
                                >
                                    <svg class="zalina-eye-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                    </svg>
                                </button>
                            </div>

                            <p class="text-[11px] text-neutral-400 mt-1.5">
                                Gunakan minimal 8 karakter agar akun lebih aman.
                            </p>
                        </div>

                        <div>
                            <label for="register-password-confirmation" class="block text-xs font-semibold text-neutral-700 mb-2">
                                Konfirmasi Password
                            </label>

                            <div class="relative">
                                <input
                                    id="register-password-confirmation"
                                    required
                                    type="password"
                                    name="password_confirmation"
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                    class="w-full border border-neutral-200 rounded-xl px-4 py-3.5 sm:py-3 pr-11 text-base sm:text-sm outline-none focus:border-maroon-400 focus:ring-1 focus:ring-maroon-300 transition"
                                >

                                <button
                                    type="button"
                                    class="zalina-toggle-pw absolute inset-y-0 right-0 flex items-center px-3.5 text-neutral-400 hover:text-maroon-700 transition"
                                    data-target="register-password-confirmation"
                                    aria-label="Tampilkan password"
                                >
                                    <svg class="zalina-eye-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <script>
                        (function () {
                            var buttons = document.querySelectorAll('.zalina-toggle-pw');

                            buttons.forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    var input = document.getElementById(btn.getAttribute('data-target'));
                                    var icon = btn.querySelector('.zalina-eye-icon');
                                    if (!input) { return; }

                                    var isHidden = input.type === 'password';
                                    input.type = isHidden ? 'text' : 'password';

                                    icon.innerHTML = isHidden
                                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.5a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.243L9.88 9.88"/>'
                                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>';
                                });
                            });
                        })();
                        </script>


                        {{-- ================================================= --}}
                        {{-- CONSENT --}}
                        {{-- ================================================= --}}

                        <label for="register-consent" class="flex items-start gap-3 rounded-xl border border-neutral-200 px-4 py-3.5 cursor-pointer hover:border-maroon-200 transition">
                            <input
                                id="register-consent"
                                type="checkbox"
                                name="consent"
                                value="1"
                                required
                                class="mt-0.5 w-4 h-4 shrink-0 cursor-pointer accent-maroon-800"
                            >
                            <span class="text-xs leading-5 text-neutral-500">
                                Saya telah membaca dan menyetujui
                                <a href="/privacy-policy.html" target="_blank" rel="noopener noreferrer" class="font-semibold text-maroon-700 underline hover:text-maroon-900">Kebijakan Privasi</a>
                                serta
                                <a href="/terms.html" target="_blank" rel="noopener noreferrer" class="font-semibold text-maroon-700 underline hover:text-maroon-900">Syarat &amp; Ketentuan</a>
                                Zalina Fashion.
                            </span>
                        </label>


                        <button
                            type="submit"
                            class="w-full bg-maroon-800 hover:bg-maroon-900 text-white py-3.5 rounded-xl text-sm font-semibold tracking-wide transition shadow-sm shadow-maroon-900/20"
                        >
                            Daftar Customer
                        </button>

                    </form>


                    <p class="mt-8 text-center text-sm text-neutral-500">
                        Sudah punya akun?
                        <a href="{{ route('login.form') }}" class="font-semibold text-maroon-700 hover:text-maroon-900 hover:underline">
                            Masuk di sini
                        </a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection