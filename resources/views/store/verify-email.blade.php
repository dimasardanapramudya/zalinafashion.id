@extends('layouts.store')

@section('title', 'Verifikasi Email - Zalina Fashion')

@section('content')

<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gray-50">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">

            <div class="text-center mb-8">

                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#F3E7E9] flex items-center justify-center">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-8 h-8 text-[#6B1F2B]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <rect
                            x="3"
                            y="5"
                            width="18"
                            height="14"
                            rx="2"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 7l9 6 9-6"
                        />
                    </svg>

                </div>

                <h1 class="text-2xl font-semibold text-gray-900">
                    Verifikasi Email
                </h1>

                <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                    Masukkan kode verifikasi yang telah dikirim ke email Anda.
                </p>

            </div>

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

            @if(session('error'))

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

                        <p class="text-sm text-red-700 leading-relaxed">
                            {{ session('error') }}
                        </p>

                    </div>

                </div>

            @endif

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

            <div class="mb-6">

                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email yang diverifikasi
                </label>

                <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm text-gray-700 break-all">
                    {{ $email }}
                </div>

            </div>

            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-5">
                @csrf

                <div>

                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        Kode Verifikasi
                    </label>

                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                        placeholder="Masukkan 6 digit kode"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-center text-2xl tracking-[0.45em] text-gray-900 outline-none transition focus:border-[#6B1F2B] focus:ring-2 focus:ring-[#6B1F2B]/20"
                    >

                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-[#6B1F2B] px-4 py-3 font-semibold text-white transition hover:bg-[#501522]"
                >
                    Verifikasi Email
                </button>

            </form>

            <form method="POST" action="{{ route('verification.resend') }}" class="mt-4">
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-xl border border-[#6B1F2B] px-4 py-3 font-semibold text-[#6B1F2B] transition hover:bg-[#FDF3F6]"
                >
                    Kirim Ulang Kode
                </button>
            </form>

            <p class="mt-6 text-center text-xs leading-5 text-gray-500">
                Kode berlaku selama 10 menit. Periksa folder Spam atau Promosi jika email belum terlihat.
            </p>

        </div>

    </div>

</div>

@endsection
