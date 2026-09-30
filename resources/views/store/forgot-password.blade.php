blade
@extends('layouts.store')

@section('title', 'Lupa Kata Sandi - Zalina Fashion')

@section('content')

<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">

    <div class="w-full max-w-md">

        {{-- ============================================================
             FORGOT PASSWORD CARD
             ============================================================ --}}

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            {{-- ========================================================
                 HEADER
                 ======================================================== --}}

            <div class="text-center mb-8">

                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#F3E7E9] flex items-center justify-center">

                    {{-- ==================================================
                         MAIL ICON
                         HANYA JIKA KODE SUDAH BENAR-BENAR DIKIRIM
                         ================================================== --}}

                    @if(session('password_reset_code_sent') && !session('password_reset_verified'))

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-8 h-8 text-[#6B1F2B]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 8l9 6 9-6"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                        </svg>

                    @else

                        {{-- LOCK / PASSWORD ICON --}}

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-8 h-8 text-[#6B1F2B]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"
                            />
                        </svg>

                    @endif

                </div>


                {{-- ====================================================
                     TITLE
                     ==================================================== --}}

                @if(session('password_reset_code_sent') && !session('password_reset_verified'))

                    <h1 class="text-2xl font-semibold text-gray-900">
                        Verifikasi Kode
                    </h1>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Masukkan kode verifikasi 6 digit yang telah kami
                        kirimkan ke email Anda.
                    </p>

                @else

                    <h1 class="text-2xl font-semibold text-gray-900">
                        Lupa Kata Sandi?
                    </h1>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Masukkan email akun Zalina Fashion Anda.
                        Kami akan mengirimkan kode verifikasi untuk
                        mengatur ulang kata sandi.
                    </p>

                @endif

            </div>


            {{-- ========================================================
                 SUCCESS MESSAGE
                 ======================================================== --}}

            @if(session('success'))

                <div class="mb-5 rounded-xl bg-green-50 border border-green-100 px-4 py-3">

                    <div class="flex items-start gap-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                        <p class="text-sm text-green-700 leading-relaxed">
                            {{ session('success') }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 ERROR MESSAGE
                 ======================================================== --}}

            @if($errors->any())

                <div class="mb-5 rounded-xl bg-red-50 border border-red-100 px-4 py-3">

                    <div class="flex items-start gap-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.5"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 16.5h.01"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.29 3.86l-7.82 13.5A2 2 0 004.2 20.36h15.6a2 2 0 001.73-3l-7.82-13.5a2 2 0 00-3.42 0z"
                            />
                        </svg>

                        <ul class="space-y-1">

                            @foreach($errors->all() as $error)

                                <li class="text-sm text-red-700 leading-relaxed">
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 STEP 1
                 KIRIM KODE VERIFIKASI
                 
                 PENTING:
                 JANGAN menggunakan password_reset_email sebagai kondisi.
                 OTP baru muncul setelah password_reset_code_sent = true.
                 ======================================================== --}}

            @if(!session('password_reset_code_sent') && !session('password_reset_verified'))

                <form
                    method="POST"
                    action="{{ route('password.send') }}"
                    class="space-y-5"
                >

                    @csrf


                    {{-- ==================================================
                         EMAIL
                         ================================================== --}}

                    <div>

                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $email ?? '') }}"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400 outline-none transition focus:border-[#6B1F2B] focus:ring-2 focus:ring-[#F3E7E9]"
                        >

                    </div>


                    {{-- ==================================================
                         SEND BUTTON
                         ================================================== --}}

                    <button
                        type="submit"
                        class="w-full py-3.5 rounded-xl bg-[#6B1F2B] hover:bg-[#551722] text-white font-medium transition shadow-sm"
                    >
                        Kirim Kode Verifikasi
                    </button>

                </form>


            {{-- ========================================================
                 STEP 2
                 VERIFIKASI OTP
                 
                 HANYA MUNCUL SETELAH:
                 password_reset_code_sent = true
                 ======================================================== --}}

            @elseif(session('password_reset_code_sent') && !session('password_reset_verified'))

                <form
                    method="POST"
                    action="{{ route('password.verify') }}"
                    class="space-y-5"
                >

                    @csrf


                    {{-- ==================================================
                         EMAIL YANG DIGUNAKAN
                         ================================================== --}}

                    <div>

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Kode dikirim ke
                        </label>

                        <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200">

                            <p class="text-sm font-medium text-gray-700 break-all">
                                {{ session('password_reset_email') }}
                            </p>

                        </div>

                    </div>


                    {{-- ==================================================
                         OTP INPUT
                         ================================================== --}}

                    <div>

                        <label
                            for="code"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Kode Verifikasi
                        </label>

                        <input
                            type="text"
                            id="code"
                            name="code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="000000"
                            required
                            autofocus
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 text-center text-2xl tracking-[0.5em] font-semibold placeholder-gray-300 outline-none transition focus:border-[#6B1F2B] focus:ring-2 focus:ring-[#F3E7E9]"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                        >

                        <p class="mt-2 text-xs text-gray-400 text-center">
                            Masukkan 6 digit kode yang dikirim ke email Anda.
                        </p>

                    </div>


                    {{-- ==================================================
                         VERIFY BUTTON
                         ================================================== --}}

                    <button
                        type="submit"
                        class="w-full py-3.5 rounded-xl bg-[#6B1F2B] hover:bg-[#551722] text-white font-medium transition shadow-sm"
                    >
                        Verifikasi Kode
                    </button>

                </form>


                {{-- ====================================================
                     CHANGE EMAIL
                     ==================================================== --}}

                <div class="mt-5 text-center">

                    <a
                        href="{{ route('password.forgot', ['new_email' => 1]) }}"
                        class="text-sm text-gray-500 hover:text-[#6B1F2B] transition"
                    >
                        ← Gunakan email lain
                    </a>

                </div>

            @endif


            {{-- ========================================================
                 SECURITY INFORMATION
                 HANYA MUNCUL SETELAH KODE BENAR-BENAR DIKIRIM
                 ======================================================== --}}

            @if(session('password_reset_code_sent') && !session('password_reset_verified'))

                <div class="mt-6 rounded-xl bg-[#F9F4F5] border border-[#F0E1E4] px-4 py-3">

                    <div class="flex items-start gap-3">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-[#6B1F2B] flex-shrink-0 mt-0.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 15v2"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 11V8a4 4 0 118 0v3"
                            />

                            <rect
                                x="5"
                                y="11"
                                width="14"
                                height="9"
                                rx="2"
                            />

                        </svg>

                        <p class="text-xs text-gray-500 leading-relaxed">
                            Demi keamanan, kode verifikasi hanya dapat digunakan
                            satu kali dan memiliki batas waktu berlaku.
                        </p>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 BACK TO LOGIN
                 ======================================================== --}}

            <div class="mt-6 text-center">

                <a
                    href="{{ route('profile') }}"
                    class="text-sm text-gray-500 hover:text-[#6B1F2B] transition"
                >
                    ← Kembali ke halaman login
                </a>

            </div>

        </div>

    </div>

</div>

@endsection

