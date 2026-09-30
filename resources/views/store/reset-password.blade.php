blade
@extends('layouts.store')

@section('title', 'Buat Kata Sandi Baru - Zalina Fashion')

@section('content')

<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">

    <div class="w-full max-w-md">

        {{-- ============================================================
             RESET PASSWORD CARD
             ============================================================ --}}

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            {{-- ========================================================
                 HEADER
                 ======================================================== --}}

            <div class="text-center mb-8">

                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#F3E7E9] flex items-center justify-center">

                    {{-- LOCK ICON --}}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-8 h-8 text-[#6B1F2B]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect
                            x="5"
                            y="11"
                            width="14"
                            height="9"
                            rx="2"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 11V8a4 4 0 118 0v3"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 15v2"
                        />
                    </svg>

                </div>


                {{-- ====================================================
                     TITLE
                     ==================================================== --}}

                <h1 class="text-2xl font-semibold text-gray-900">
                    Buat Kata Sandi Baru
                </h1>

                <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                    Buat kata sandi baru untuk mengamankan kembali
                    akun Zalina Fashion Anda.
                </p>

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
                                d="M10.29 3.86l-7.82 13.5A2 2 0 004.2 20.36h15.6a2 2 0 001.73-3l-7.82-13.5a2 2 0 003.42 0z"
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
                 VERIFIED EMAIL
                 ======================================================== --}}

            @if(session('password_reset_email'))

                <div class="mb-6">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Akun yang diverifikasi
                    </label>

                    <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200">

                        <div class="flex items-center gap-3">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 text-[#6B1F2B] flex-shrink-0"
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

                            <p class="text-sm text-gray-700 break-all">
                                {{ session('password_reset_email') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                 RESET PASSWORD FORM
                 ======================================================== --}}

            <form
                method="POST"
                action="{{ route('password.reset') }}"
                class="space-y-5"
            >

                @csrf


                {{-- ====================================================
                     NEW PASSWORD
                     ==================================================== --}}

                <div>

                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Kata Sandi Baru
                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan kata sandi baru"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400 outline-none transition focus:border-[#6B1F2B] focus:ring-2 focus:ring-[#F3E7E9]"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password', 'password-eye')"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-[#6B1F2B] transition"
                            aria-label="Tampilkan kata sandi"
                        >

                            <svg
                                id="password-eye"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>

                        </button>

                    </div>

                    <p class="mt-2 text-xs text-gray-400">
                        Gunakan minimal 8 karakter untuk kata sandi baru.
                    </p>

                </div>


                {{-- ====================================================
                     CONFIRM PASSWORD
                     ==================================================== --}}

                <div>

                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Konfirmasi Kata Sandi Baru
                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi kata sandi baru"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 bg-white text-gray-900 placeholder-gray-400 outline-none transition focus:border-[#6B1F2B] focus:ring-2 focus:ring-[#F3E7E9]"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password_confirmation', 'confirmation-eye')"
                            class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-400 hover:text-[#6B1F2B] transition"
                            aria-label="Tampilkan konfirmasi kata sandi"
                        >

                            <svg
                                id="confirmation-eye"
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>

                        </button>

                    </div>

                </div>


                {{-- ====================================================
                     PASSWORD MATCH INDICATOR
                     ==================================================== --}}

                <div
                    id="password-match"
                    class="hidden rounded-xl px-4 py-3 text-sm"
                >
                </div>


                {{-- ====================================================
                     RESET BUTTON
                     ==================================================== --}}

                <button
                    type="submit"
                    class="w-full py-3.5 rounded-xl bg-[#6B1F2B] hover:bg-[#551722] text-white font-medium transition shadow-sm"
                >
                    Simpan Kata Sandi Baru
                </button>

            </form>


            {{-- ========================================================
                 SECURITY INFORMATION
                 ======================================================== --}}

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
                        Anda telah berhasil memverifikasi kode keamanan.
                        Kata sandi lama akan digantikan dengan kata sandi baru
                        setelah proses berhasil.
                    </p>

                </div>

            </div>


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


{{-- ============================================================
     PASSWORD JAVASCRIPT
     ============================================================ --}}

<script>

    // ============================================================
    // TOGGLE PASSWORD VISIBILITY
    // ============================================================

    function togglePassword(inputId, iconId) {

        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (!input || !icon) {
            return;
        }

        if (input.type === 'password') {

            input.type = 'text';

            icon.innerHTML = `
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 3l18 18"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M10.584 10.587a2 2 0 002.828 2.828"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.88 5.09A10.94 10.94 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.94 10.94 0 01-4.043 5.244"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6.61 6.61A10.94 10.94 0 002.458 12c1.274 4.057 5.065 7 9.542 7 1.61 0 3.12-.34 4.49-.95"
                />
            `;

        } else {

            input.type = 'password';

            icon.innerHTML = `
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
            `;

        }

    }


    // ============================================================
    // PASSWORD MATCH CHECK
    // ============================================================

    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('password_confirmation');
    const matchMessage = document.getElementById('password-match');


    function checkPasswordMatch() {

        if (!passwordInput || !confirmationInput || !matchMessage) {
            return;
        }

        const password = passwordInput.value;
        const confirmation = confirmationInput.value;


        // ========================================================
        // EMPTY CONFIRMATION
        // ========================================================

        if (confirmation.length === 0) {

            matchMessage.classList.add('hidden');

            return;

        }


        // ========================================================
        // PASSWORD MATCH
        // ========================================================

        if (password === confirmation) {

            matchMessage.className =
                'rounded-xl px-4 py-3 text-sm bg-green-50 border border-green-100 text-green-700';

            matchMessage.textContent =
                '✓ Kata sandi cocok.';

            matchMessage.classList.remove('hidden');

        }


        // ========================================================
        // PASSWORD NOT MATCH
        // ========================================================

        else {

            matchMessage.className =
                'rounded-xl px-4 py-3 text-sm bg-red-50 border border-red-100 text-red-700';

            matchMessage.textContent =
                'Kata sandi belum cocok.';

            matchMessage.classList.remove('hidden');

        }

    }


    // ============================================================
    // INPUT EVENTS
    // ============================================================

    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
    }

    if (confirmationInput) {
        confirmationInput.addEventListener('input', checkPasswordMatch);
    }

</script>

@endsection

