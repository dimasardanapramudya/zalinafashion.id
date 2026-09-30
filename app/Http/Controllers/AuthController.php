<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\EmailVerificationCode;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
|
| Menampilkan profile user yang sedang login.
|
| Badge profile menghitung pesanan yang masih aktif:
|
| - Belum dibayar
| - Bukti pembayaran menunggu verifikasi
| - Sudah dibayar
| - Sudah dikonfirmasi admin
| - Sedang diproses
| - Sudah diserahkan ke kurir
| - Sedang dikirim
|
*/

public function profile()
{
    /*
    |--------------------------------------------------------------------------
    | Ambil User Dari Session
    |--------------------------------------------------------------------------
    */

    $userId = session('zalina_user_id');

    $user = $userId
        ? User::find($userId)
        : null;


    /*
    |--------------------------------------------------------------------------
    | Bersihkan Session Jika User Sudah Tidak Ada
    |--------------------------------------------------------------------------
    |
    | Jika akun sudah dihapus dari database tetapi session masih ada,
    | session user akan dibersihkan.
    |
    */

    if ($userId && !$user) {

        session()->forget('zalina_user_id');

    }


    /*
    |--------------------------------------------------------------------------
    | Belum Login -> Arahkan Ke Halaman Login
    |--------------------------------------------------------------------------
    |
    | Login dan Register sekarang memiliki halaman blade tersendiri
    | (auth.login dan auth.register), jadi /profile hanya menampilkan
    | dashboard untuk user yang sudah login.
    |
    */

    if (!$user) {

        return redirect()->route('login.form');

    }


    /*
    |--------------------------------------------------------------------------
    | Ambil Pesanan Customer
    |--------------------------------------------------------------------------
    |
    | Admin tidak membutuhkan daftar pesanan customer pada profile.
    |
    */

    $orders = $user && $user->role === 'customer'

        ? Order::query()

            ->with([

                'payment',

                'payment.method',

                'items',

                'items.product',

                'items.variant',

            ])

            ->where(

                'user_id',

                $user->id

            )

            ->latest()

            ->get()

        : collect();


    /*
    |--------------------------------------------------------------------------
    | Jumlah Seluruh Pesanan
    |--------------------------------------------------------------------------
    |
    | Menghitung semua pesanan customer, termasuk:
    |
    | - Pesanan belum dibayar
    | - Pesanan menunggu verifikasi
    | - Pesanan diproses
    | - Pesanan dikirim
    | - Pesanan selesai
    | - Pesanan dibatalkan
    |
    */

    $orderCount = $orders->count();


    /*
    |--------------------------------------------------------------------------
    | Daftar Status Pesanan Aktif
    |--------------------------------------------------------------------------
    |
    | Pesanan dengan status berikut masih dianggap aktif.
    |
    */

    $activeOrderStatuses = [

        'pending',

        'processing',

        'confirmed',

        'paid',

        'packed',

        'ready_to_ship',

        'handed_to_courier',

        'shipped',

        'waiting',

    ];


    /*
    |--------------------------------------------------------------------------
    | Daftar Status Pembayaran Aktif
    |--------------------------------------------------------------------------
    |
    | Pesanan dengan status pembayaran berikut masih membutuhkan
    | perhatian user atau masih dalam proses.
    |
    */

    $activePaymentStatuses = [

        'unpaid',

        'pending_verification',

        'paid',

    ];


    /*
    |--------------------------------------------------------------------------
    | Hitung Pesanan Aktif Untuk Badge Profile
    |--------------------------------------------------------------------------
    |
    | Badge akan bertambah ketika:
    |
    | 1. User membuat pesanan baru.
    | 2. Pesanan belum dibayar.
    | 3. User mengupload bukti pembayaran.
    | 4. Admin memverifikasi pembayaran.
    | 5. Pesanan dikonfirmasi admin.
    | 6. Pesanan sedang diproses.
    | 7. Pesanan diserahkan ke kurir.
    | 8. Pesanan sedang dikirim.
    |
    | Badge tidak menghitung:
    |
    | - Pesanan selesai.
    | - Pesanan delivered.
    | - Pesanan dibatalkan.
    |
    */

    $activeOrderCount = $orders

        ->filter(function ($order) use (
            $activeOrderStatuses,
            $activePaymentStatuses
        ) {

            /*
            |--------------------------------------------------------------------------
            | Status Pesanan
            |--------------------------------------------------------------------------
            */

            $orderStatus = strtolower(

                trim(

                    (string) $order->status

                )

            );


            /*
            |--------------------------------------------------------------------------
            | Status Pembayaran
            |--------------------------------------------------------------------------
            */

            $paymentStatus = strtolower(

                trim(

                    (string) $order->payment_status

                )

            );


            /*
            |--------------------------------------------------------------------------
            | Status Pesanan Selesai
            |--------------------------------------------------------------------------
            */

            $finishedStatuses = [

                'completed',

                'complete',

                'delivered',

                'finished',

                'success',

            ];


            /*
            |--------------------------------------------------------------------------
            | Status Pesanan Dibatalkan
            |--------------------------------------------------------------------------
            */

            $cancelledStatuses = [

                'cancelled',

                'canceled',

                'rejected',

                'failed',

            ];


            /*
            |--------------------------------------------------------------------------
            | Jangan Hitung Pesanan Selesai
            |--------------------------------------------------------------------------
            */

            if (

                in_array(

                    $orderStatus,

                    $finishedStatuses,

                    true

                )

            ) {

                return false;

            }


            /*
            |--------------------------------------------------------------------------
            | Jangan Hitung Pesanan Dibatalkan
            |--------------------------------------------------------------------------
            */

            if (

                in_array(

                    $orderStatus,

                    $cancelledStatuses,

                    true

                )

            ) {

                return false;

            }


            /*
            |--------------------------------------------------------------------------
            | Cek Status Pesanan Aktif
            |--------------------------------------------------------------------------
            */

            $isActiveOrder = in_array(

                $orderStatus,

                $activeOrderStatuses,

                true

            );


            /*
            |--------------------------------------------------------------------------
            | Cek Status Pembayaran Aktif
            |--------------------------------------------------------------------------
            */

            $isActivePayment = in_array(

                $paymentStatus,

                $activePaymentStatuses,

                true

            );


            /*
            |--------------------------------------------------------------------------
            | Pesanan Aktif Jika Salah Satu Terpenuhi
            |--------------------------------------------------------------------------
            */

            return $isActiveOrder || $isActivePayment;

        })

        ->count();


    /*
    |--------------------------------------------------------------------------
    | Hitung Pesanan Belum Dibayar
    |--------------------------------------------------------------------------
    |
    | Badge tambahan untuk mengetahui jumlah pesanan yang belum dibayar.
    |
    */

    $unpaidOrderCount = $orders

        ->filter(function ($order) {

            return strtolower(

                trim(

                    (string) $order->payment_status

                )

            ) === 'unpaid';

        })

        ->count();


    /*
    |--------------------------------------------------------------------------
    | Hitung Pesanan Menunggu Verifikasi Admin
    |--------------------------------------------------------------------------
    |
    | Badge tambahan untuk pesanan yang bukti pembayarannya sudah
    | dikirim tetapi belum diverifikasi admin.
    |
    */

    $waitingVerificationCount = $orders

        ->filter(function ($order) {

            return strtolower(

                trim(

                    (string) $order->payment_status

                )

            ) === 'pending_verification';

        })

        ->count();


    /*
    |--------------------------------------------------------------------------
    | Hitung Pesanan Yang Sudah Dibayar
    |--------------------------------------------------------------------------
    |
    | Pesanan yang sudah dibayar atau dikonfirmasi admin.
    |
    */

    $paidOrderCount = $orders

        ->filter(function ($order) {

            return in_array(

                strtolower(

                    trim(

                        (string) $order->payment_status

                    )

                ),

                [

                    'paid',

                    'verified',

                    'confirmed',

                ],

                true

            );

        })

        ->count();


    /*
    |--------------------------------------------------------------------------
    | Tampilkan Profile
    |--------------------------------------------------------------------------
    */

    return view(

        'store.profile',

        compact(

            'user',

            'orders',

            'orderCount',

            'activeOrderCount',

            'unpaidOrderCount',

            'waitingVerificationCount',

            'paidOrderCount'

        )

    );
}

    # ============================================================
    # HALAMAN LOGIN & REGISTER (BLADE TERPISAH)
    # ============================================================

    /*
    |--------------------------------------------------------------------------
    | Halaman Login
    |--------------------------------------------------------------------------
    |
    | Jika user sudah login, langsung arahkan ke /profile supaya
    | tidak melihat form login lagi.
    |
    */

    public function loginForm()
    {
        if (session('zalina_user_id')) {

            return redirect()->route('profile');

        }

        return view('auth.login');
    }


    /*
    |--------------------------------------------------------------------------
    | Halaman Register
    |--------------------------------------------------------------------------
    */

    public function registerForm()
    {
        if (session('zalina_user_id')) {

            return redirect()->route('profile');

        }

        return view('auth.register');
    }


    # ============================================================
    # LOGIN EMAIL & PASSWORD
    # ============================================================

    /*
    |--------------------------------------------------------------------------
    | Login Email & Password
    |--------------------------------------------------------------------------
    |
    | Login menggunakan email dan password.
    |
    | Sistem menggunakan session custom:
    |
    | zalina_user_id
    |
    | Sistem "Lupa Kata Sandi" akan muncul setelah user salah
    | memasukkan password sebanyak 3 kali.
    |
    */

    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ], [
            'email.required' =>
                'Email wajib diisi.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email maksimal 255 karakter.',

            'password.required' =>
                'Password wajib diisi.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalisasi Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($data['email'])
        );


        # ============================================================
        # PASSWORD RESET COUNTER KEY
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Key Counter Password Salah
        |--------------------------------------------------------------------------
        |
        | Counter disimpan di session berdasarkan email.
        |
        | Contoh:
        |
        | failed_password:customer@gmail.com
        |
        | Dengan cara ini, percobaan email A tidak memengaruhi
        | percobaan email B.
        |
        */

        $failedPasswordKey = 'failed_password:' . $email;


        /*
        |--------------------------------------------------------------------------
        | Ambil Jumlah Percobaan Password Sebelumnya
        |--------------------------------------------------------------------------
        */

        $failedPasswordAttempts = (int) session(
            $failedPasswordKey,
            0
        );


        /*
        |--------------------------------------------------------------------------
        | Login Rate Limiting
        |--------------------------------------------------------------------------
        |
        | Maksimal 5 percobaan login dalam 1 menit
        | untuk kombinasi email + IP address.
        |
        | Tujuannya mengurangi risiko brute-force password.
        |
        */

        $rateLimitKey = 'login:' .
            Str::lower($email) .
            '|' .
            $request->ip();


        if (RateLimiter::tooManyAttempts(
            $rateLimitKey,
            5
        )) {

            $seconds = RateLimiter::availableIn(
                $rateLimitKey
            );

            return back()
                ->with(
                    'error',
                    'Terlalu banyak percobaan login. ' .
                    'Silakan coba lagi dalam ' .
                    $seconds .
                    ' detik.'
                )
                ->withInput(
                    $request->only('email')
                );
        }


        # ============================================================
        # CARI USER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Cari User
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            'email',
            $email
        )->first();


        # ============================================================
        # CEK CREDENTIAL
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Cek Credential
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            !$user->password ||
            !Hash::check(
                $data['password'],
                $user->password
            )
        ) {

            # ============================================================
            # PASSWORD SALAH - TAMBAH COUNTER
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Tambahkan Percobaan Password Salah
            |--------------------------------------------------------------------------
            */

            $failedPasswordAttempts++;


            /*
            |--------------------------------------------------------------------------
            | Simpan Counter ke Session
            |--------------------------------------------------------------------------
            */

            session()->put(
                $failedPasswordKey,
                $failedPasswordAttempts
            );


            # ============================================================
            # TAMPILKAN OPSI RESET SETELAH 3X SALAH
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Aktifkan Opsi Lupa Kata Sandi
            |--------------------------------------------------------------------------
            |
            | Setelah password salah sebanyak 3 kali,
            | kita memberikan flag session:
            |
            | show_password_reset = true
            |
            | Flag ini nantinya akan digunakan oleh Blade login
            | untuk menampilkan tombol/link:
            |
            | "Lupa Kata Sandi?"
            |
            */

            if ($failedPasswordAttempts >= 3) {

                session()->put(
                    'show_password_reset',
                    true
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan Email untuk Proses Reset
                |--------------------------------------------------------------------------
                |
                | Email yang sudah dimasukkan user dapat digunakan
                | oleh halaman reset password.
                |
                */

                session()->put(
                    'password_reset_email',
                    $email
                );
            }


            # ============================================================
            # TAMBAHKAN RATE LIMIT LOGIN
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Tambahkan Percobaan Gagal
            |--------------------------------------------------------------------------
            */

            RateLimiter::hit(
                $rateLimitKey,
                60
            );


            # ============================================================
            # PESAN ERROR PASSWORD
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Pesan Berdasarkan Jumlah Percobaan
            |--------------------------------------------------------------------------
            */

            if ($failedPasswordAttempts >= 3) {

                return back()
                    ->with(
                        'error',
                        'Email atau password salah. ' .
                        'Anda sudah salah memasukkan password ' .
                        $failedPasswordAttempts .
                        ' kali.'
                    )
                    ->withInput(
                        $request->only('email')
                    );
            }


            return back()
                ->with(
                    'error',
                    'Email atau password salah.'
                )
                ->withInput(
                    $request->only('email')
                );
        }
                # ============================================================
        # CEK VERIFIKASI EMAIL CUSTOMER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | PENTING - AKUN LAMA (LEGACY) TIDAK PERLU VERIFIKASI ULANG
        |--------------------------------------------------------------------------
        |
        | Fitur verifikasi email (EmailVerificationCode) baru dibuat
        | belakangan. Sebelumnya, register() TIDAK membuat kode
        | verifikasi apa pun, sehingga akun customer lama sudah pasti
        | punya email_verified_at = NULL walaupun akunnya sah, sudah
        | lama dipakai, dan passwordnya benar.
        |
        | BUG SEBELUMNYA:
        | Login akun lama yang sah tetap dipaksa masuk ke halaman
        | verifikasi email, padahal akun tersebut tidak pernah diminta
        | untuk memverifikasi apa pun saat mendaftar dulu.
        |
        | PERBAIKAN:
        | Customer hanya diwajibkan verifikasi jika dia memang PERNAH
        | dikirimi kode verifikasi (artinya dia mendaftar SETELAH fitur
        | ini aktif). Kalau tidak ada riwayat EmailVerificationCode
        | sama sekali untuk user ini, akun dianggap akun lama dan
        | email_verified_at langsung ditandai terverifikasi tanpa
        | perlu memasukkan kode apa pun.
        |
        */

        if (
            $user->role === 'customer' &&
            is_null($user->email_verified_at)
        ) {

            $hasVerificationHistory = EmailVerificationCode::where(
                'user_id',
                $user->id
            )->exists();

            if (!$hasVerificationHistory) {

                /*
                | Akun lama (pra-fitur verifikasi). Tandai terverifikasi
                | secara otomatis, tidak perlu mengirim kode apa pun.
                */

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

            } else {

                /*
                | Akun ini memang pernah dikirimi kode verifikasi
                | (mendaftar setelah fitur ini aktif) tetapi belum
                | menyelesaikan verifikasi. Tetap wajibkan verifikasi.
                */

                $request->session()->put(
                    'pending_email_verification_user_id',
                    $user->id
                );

                $request->session()->put(
                    'pending_email_verification_email',
                    $user->email
                );

                return redirect()
                    ->route('verification.notice')
                    ->with(
                        'error',
                        'Email Anda belum diverifikasi. Silakan masukkan kode verifikasi yang dikirim ke email.'
                    );
            }
        }


        # ============================================================
        # LOGIN BERHASIL
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Percobaan Gagal Sebelumnya Dihapus
        |--------------------------------------------------------------------------
        */

        RateLimiter::clear(
            $rateLimitKey
        );


        # ============================================================
        # RESET PASSWORD COUNTER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Hapus Counter Password Salah
        |--------------------------------------------------------------------------
        |
        | Jika user berhasil login, berarti password sudah benar.
        |
        | Counter kesalahan dihapus agar pada login berikutnya
        | perhitungan dimulai kembali dari 0.
        |
        */

        session()->forget(
            $failedPasswordKey
        );


        /*
        |--------------------------------------------------------------------------
        | Hapus Flag Lupa Password
        |--------------------------------------------------------------------------
        |
        | User berhasil login sehingga tidak perlu lagi
        | menampilkan opsi reset password.
        |
        */

        session()->forget(
            'show_password_reset'
        );


        /*
        |--------------------------------------------------------------------------
        | Hapus Email Reset Sementara
        |--------------------------------------------------------------------------
        */

        session()->forget(
            'password_reset_email'
        );


        # ============================================================
        # REGENERATE SESSION
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        |
        | Mencegah session fixation.
        |
        */

        $request->session()->regenerate();


        # ============================================================
        # SIMPAN USER ID
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Simpan User ID
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'zalina_user_id',
            $user->id
        );


        # ============================================================
        # REDIRECT ADMIN
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Redirect Admin
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            return redirect()
                ->intended(
                    route('admin.dashboard')
                )
                ->with(
                    'success',
                    'Login administrator berhasil.'
                );
        }


        # ============================================================
        # REDIRECT CUSTOMER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Redirect Customer
        |--------------------------------------------------------------------------
        |
        | Jika sebelumnya user mencoba membuka checkout/cart/payment,
        | middleware dapat menyimpan URL tersebut sebagai intended URL.
        |
        */

        return redirect()
            ->intended(
                route('profile')
            )
            ->with(
                'success',
                'Login berhasil. Selamat datang kembali di Zalina Hijab!'
            );
    }


    # ============================================================
    # REGISTER CUSTOMER
    # ============================================================

    /*
    |--------------------------------------------------------------------------
    | Register Customer
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Registration
        |--------------------------------------------------------------------------
        |
        | Consent wajib divalidasi di SERVER.
        | Jadi user tidak dapat melewati persetujuan hanya dengan
        | memodifikasi HTML dari browser.
        |
        */

        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'consent' => [
                'required',
                'accepted',
            ],

        ], [

            'name.required' =>
                'Nama lengkap wajib diisi.',

            'name.string' =>
                'Nama lengkap tidak valid.',

            'name.max' =>
                'Nama maksimal 100 karakter.',

            'email.required' =>
                'Email wajib diisi.',

            'email.string' =>
                'Email tidak valid.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email maksimal 255 karakter.',

            'email.unique' =>
                'Email tersebut sudah terdaftar. Silakan gunakan email lain atau login.',

            'password.required' =>
                'Password wajib diisi.',

            'password.string' =>
                'Password tidak valid.',

            'password.min' =>
                'Password minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak cocok.',

            'consent.required' =>
                'Anda harus menyetujui Kebijakan Privasi dan Syarat & Ketentuan.',

            'consent.accepted' =>
                'Anda harus menyetujui Kebijakan Privasi dan Syarat & Ketentuan.',
        ]);


        # ============================================================
        # NORMALISASI NAMA
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Normalisasi Nama
        |--------------------------------------------------------------------------
        */

        $name = trim(
            $data['name']
        );


        # ============================================================
        # NORMALISASI EMAIL
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Normalisasi Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($data['email'])
        );


        # ============================================================
        # CEK EMAIL SETELAH NORMALISASI
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Cek Email Lagi Setelah Normalisasi
        |--------------------------------------------------------------------------
        |
        | Misalnya:
        |
        | User@Email.com
        |
        | dan
        |
        | user@email.com
        |
        | Jangan sampai dianggap sebagai dua akun berbeda.
        |
        */

        $existingUser = User::where(
            'email',
            $email
        )->first();


        if ($existingUser) {

            return back()
                ->with(
                    'error',
                    'Email tersebut sudah terdaftar. Silakan login.'
                )
                ->withInput(
                    $request->except([
                        'password',
                        'password_confirmation',
                        'consent',
                    ])
                );
        }


        # ============================================================
        # BUAT CUSTOMER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Buat Customer
        |--------------------------------------------------------------------------
        |
        | PENTING:
        |
        | Role tidak pernah diambil dari request.
        |
        | User baru selalu:
        |
        | customer
        |
        */

        $user = User::create([

            'name' =>
                $name,

            'email' =>
                $email,

            'password' =>
                Hash::make(
                    $data['password']
                ),

            'role' =>
                'customer',
        ]);

            # ============================================================
        # SIAPKAN VERIFIKASI EMAIL
        # ============================================================

        $verificationCode = (string) random_int(
            100000,
            999999
        );

        EmailVerificationCode::where(
            'user_id',
            $user->id
        )
            ->where(
                'used',
                false
            )
            ->update([
                'used' => true,
            ]);

        EmailVerificationCode::create([

            'user_id' =>
                $user->id,

            'email' =>
                $user->email,

            'code' =>
                Hash::make(
                    $verificationCode
                ),

            'expires_at' =>
                now()->addMinutes(10),

            'used' =>
                false,
        ]);


        # ============================================================
        # SIMPAN USER SEMENTARA
        # ============================================================

        $request->session()->put(
            'pending_email_verification_user_id',
            $user->id
        );

        $request->session()->put(
            'pending_email_verification_email',
            $user->email
        );


        # ============================================================
        # KIRIM KODE VERIFIKASI
        # ============================================================

        Mail::raw(

            "Halo {$user->name},\n\n" .
            "Kode verifikasi email Zalina Fashion Anda adalah:\n\n" .
            "{$verificationCode}\n\n" .
            "Kode berlaku selama 10 menit.\n\n" .
            "Jika Anda tidak membuat akun Zalina Fashion, abaikan email ini.\n\n" .
            "Salam,\n" .
            "Zalina Fashion",

            function ($message) use ($user) {

                $message
                    ->to($user->email)
                    ->subject(
                        'Kode Verifikasi Email - Zalina Fashion'
                    );
            }
        );


        # ============================================================
        # REDIRECT KE HALAMAN VERIFIKASI
        # ============================================================

        return redirect()
            ->route('verification.notice')
            ->with(
                'success',
                'Akun berhasil dibuat. Kode verifikasi telah dikirim ke email Anda.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Google Login Callback
    |--------------------------------------------------------------------------
    */

    public function googleCallback($googleUser)
    {
        # ============================================================
        # AMBIL EMAIL GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Ambil Email Google
        |--------------------------------------------------------------------------
        */

        $googleEmail = $googleUser->getEmail();


        # ============================================================
        # VALIDASI EMAIL GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Email Google Wajib Tersedia
        |--------------------------------------------------------------------------
        */

        if (!$googleEmail) {

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Email dari akun Google tidak dapat diperoleh.'
                );
        }


        # ============================================================
        # NORMALISASI EMAIL GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Normalisasi Email
        |--------------------------------------------------------------------------
        */

        $email = strtolower(
            trim($googleEmail)
        );


        # ============================================================
        # VALIDASI FORMAT EMAIL GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Validasi Format Email
        |--------------------------------------------------------------------------
        */

        if (!filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )) {

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Email dari akun Google tidak valid.'
                );
        }


        # ============================================================
        # CARI USER GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Cari User Berdasarkan Email
        |--------------------------------------------------------------------------
        */

        $user = User::where(
            'email',
            $email
        )->first();


        # ============================================================
        # BUAT USER BARU DARI GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Jika User Belum Ada
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            # ============================================================
            # AMBIL NAMA GOOGLE
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Ambil Nama Google
            |--------------------------------------------------------------------------
            */

            $name = trim(
                $googleUser->getName()
                    ?: 'Customer Zalina'
            );


            # ============================================================
            # PASTIKAN NAMA TIDAK KOSONG
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Pastikan Nama Tidak Kosong
            |--------------------------------------------------------------------------
            */

            if ($name === '') {
                $name = 'Customer Zalina';
            }


            # ============================================================
            # BUAT AKUN CUSTOMER GOOGLE
            # ============================================================

            /*
            |--------------------------------------------------------------------------
            | Buat Akun Customer
            |--------------------------------------------------------------------------
            |
            | Akun baru dari Google SELALU customer.
            |
            */

            $user = User::create([

                'name' =>
                    $name,

                'email' =>
                    $email,

                # ========================================================
                # PASSWORD RANDOM GOOGLE
                # ========================================================

                /*
                |--------------------------------------------------------------------------
                | Password Random
                |--------------------------------------------------------------------------
                |
                | User Google menggunakan OAuth.
                | Password lokal tetap dibuat random dan tidak diketahui user.
                |
                */

                'password' =>
                    Hash::make(
                        Str::random(64)
                    ),

                # ========================================================
                # ROLE CUSTOMER
                # ========================================================

                /*
                |--------------------------------------------------------------------------
                | Role Aman
                |--------------------------------------------------------------------------
                */

                'role' =>
                    'customer',

                # ========================================================
                # VERIFIKASI EMAIL GOOGLE
                # ========================================================

                /*
                |--------------------------------------------------------------------------
                | Google sudah memverifikasi email melalui OAuth provider.
                |--------------------------------------------------------------------------
                */

                'email_verified_at' =>
                    now(),
            ]);
        }


        # ============================================================
        # REGENERATE SESSION GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Regenerate Session
        |--------------------------------------------------------------------------
        */

        request()
            ->session()
            ->regenerate();


        # ============================================================
        # SIMPAN USER ID GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Simpan User ID
        |--------------------------------------------------------------------------
        */

        request()
            ->session()
            ->put(
                'zalina_user_id',
                $user->id
            );


        # ============================================================
        # REDIRECT ADMIN GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Redirect Admin
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            return redirect()
                ->intended(
                    route('admin.dashboard')
                )
                ->with(
                    'success',
                    'Login Google berhasil.'
                );
        }


        # ============================================================
        # REDIRECT CUSTOMER GOOGLE
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Redirect Customer
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->intended(
                route('profile')
            )
            ->with(
                'success',
                'Login dengan Google berhasil.'
            );
    }

        # ============================================================
    # TAMPILKAN HALAMAN VERIFIKASI EMAIL
    # ============================================================

    public function showEmailVerification(Request $request)
    {
        $userId = $request->session()->get(
            'pending_email_verification_user_id'
        );

        $email = $request->session()->get(
            'pending_email_verification_email'
        );

        if (!$userId || !$email) {
            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Sesi verifikasi tidak ditemukan. Silakan daftar kembali.'
                );
        }

        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget([
                'pending_email_verification_user_id',
                'pending_email_verification_email',
            ]);

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Data akun tidak ditemukan. Silakan daftar kembali.'
                );
        }

        if ($user->email_verified_at) {
            $request->session()->put(
                'zalina_user_id',
                $user->id
            );

            return redirect()
                ->route('profile')
                ->with(
                    'success',
                    'Email Anda sudah terverifikasi.'
                );
        }

        return view(
            'store.verify-email',
            compact('user', 'email')
        );
    }


    # ============================================================
    # PROSES VERIFIKASI EMAIL
    # ============================================================

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => [
                'required',
                'digits:6',
            ],
        ], [
            'code.required' =>
                'Kode verifikasi wajib diisi.',

            'code.digits' =>
                'Kode verifikasi harus terdiri dari 6 angka.',
        ]);

        $userId = $request->session()->get(
            'pending_email_verification_user_id'
        );

        if (!$userId) {
            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Sesi verifikasi telah berakhir. Silakan daftar kembali.'
                );
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Data akun tidak ditemukan.'
                );
        }

        if ($user->email_verified_at) {
            $request->session()->put(
                'zalina_user_id',
                $user->id
            );

            return redirect()
                ->route('profile')
                ->with(
                    'success',
                    'Email berhasil diverifikasi.'
                );
        }

        $verification = EmailVerificationCode::where(
            'user_id',
            $user->id
        )
            ->where(
                'used',
                false
            )
            ->latest()
            ->first();

        if (!$verification) {
            return back()
                ->with(
                    'error',
                    'Kode verifikasi tidak ditemukan. Silakan kirim ulang kode.'
                );
        }

        if (
            $verification->expires_at &&
            now()->greaterThan($verification->expires_at)
        ) {
            return back()
                ->with(
                    'error',
                    'Kode verifikasi sudah kedaluwarsa. Silakan kirim ulang kode.'
                );
        }

        if (!Hash::check(
            $request->input('code'),
            $verification->code
        )) {
            return back()
                ->with(
                    'error',
                    'Kode verifikasi salah.'
                );
        }

        $verification->update([
            'used' => true,
        ]);

        $user->update([
            'email_verified_at' => now(),
        ]);

        $request->session()->forget([
            'pending_email_verification_user_id',
            'pending_email_verification_email',
        ]);

        $request->session()->regenerate();

        $request->session()->put(
            'zalina_user_id',
            $user->id
        );

        return redirect()
            ->intended(route('profile'))
            ->with(
                'success',
                'Email berhasil diverifikasi. Selamat datang di Zalina Fashion!'
            );
    }


    # ============================================================
    # KIRIM ULANG KODE VERIFIKASI
    # ============================================================

    public function resendEmailVerificationCode(Request $request)
    {
        $userId = $request->session()->get(
            'pending_email_verification_user_id'
        );

        if (!$userId) {
            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Sesi verifikasi telah berakhir. Silakan daftar kembali.'
                );
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Data akun tidak ditemukan.'
                );
        }

        if ($user->email_verified_at) {
            return redirect()
                ->route('profile')
                ->with(
                    'success',
                    'Email Anda sudah terverifikasi.'
                );
        }

        $verificationCode = (string) random_int(
            100000,
            999999
        );

        EmailVerificationCode::where(
            'user_id',
            $user->id
        )
            ->where(
                'used',
                false
            )
            ->update([
                'used' => true,
            ]);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => Hash::make($verificationCode),
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        Mail::raw(
            "Halo {$user->name},\n\n" .
            "Kode verifikasi email Zalina Fashion Anda adalah:\n\n" .
            "{$verificationCode}\n\n" .
            "Kode berlaku selama 10 menit.\n\n" .
            "Salam,\n" .
            "Zalina Fashion",
            function ($message) use ($user) {
                $message
                    ->to($user->email)
                    ->subject(
                        'Kode Verifikasi Email - Zalina Fashion'
                    );
            }
        );

        return back()
            ->with(
                'success',
                'Kode verifikasi baru telah dikirim ke email Anda.'
            );
    }


    # ============================================================
    # LOGOUT
    # ============================================================

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        # ============================================================
        # HAPUS IDENTITAS USER
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Hapus Identitas User
        |--------------------------------------------------------------------------
        */

        $request->session()->forget(
            'zalina_user_id'
        );


        # ============================================================
        # HAPUS PASSWORD RESET SESSION
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Hapus status reset password sementara
        |--------------------------------------------------------------------------
        |
        | Tidak perlu membawa status lupa password setelah logout.
        |
        */

        $request->session()->forget(
            'show_password_reset'
        );

        $request->session()->forget(
            'password_reset_email'
        );


        # ============================================================
        # HANCURKAN SESSION
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Hancurkan Session
        |--------------------------------------------------------------------------
        |
        | Mencegah session lama digunakan kembali.
        |
        */

        $request->session()->invalidate();


        # ============================================================
        # GENERATE CSRF TOKEN BARU
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Generate CSRF Token Baru
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerateToken();


        # ============================================================
        # REDIRECT HOME
        # ============================================================

        /*
        |--------------------------------------------------------------------------
        | Redirect ke Home
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Anda telah berhasil logout.'
            );
    }
}