<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class PasswordResetController extends Controller
{
    // ============================================================
    // FORGOT PASSWORD PAGE
    // ============================================================

    public function showForgotPassword(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | GUNAKAN EMAIL LAIN
        |--------------------------------------------------------------------------
        |
        | Jika user menekan "Gunakan email lain", seluruh status reset
        | sebelumnya harus dibersihkan.
        |
        */

        if ($request->boolean('new_email')) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_code_sent',
                'password_reset_verified',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DISPLAY FORGOT PASSWORD PAGE
        |--------------------------------------------------------------------------
        |
        | PENTING:
        | password_reset_email TIDAK lagi digunakan sebagai tanda bahwa
        | OTP sudah diminta.
        |
        | OTP hanya ditampilkan jika:
        |
        | password_reset_code_sent = true
        |
        */

        return view('store.forgot-password', [
            'email' => $request->session()->get(
                'password_reset_email'
            ),

            'codeSent' => $request->session()->get(
                'password_reset_code_sent',
                false
            ),

            'verified' => $request->session()->get(
                'password_reset_verified',
                false
            ),
        ]);
    }


    // ============================================================
    // SEND PASSWORD RESET CODE
    // ============================================================

    public function sendCode(Request $request)
    {
        // ========================================================
        // VALIDATE EMAIL
        // ========================================================

        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ], [
            'email.required' =>
                'Email wajib diisi.',

            'email.email' =>
                'Format email tidak valid.',

            'email.max' =>
                'Email terlalu panjang.',
        ]);


        // ========================================================
        // NORMALIZE EMAIL
        // ========================================================

        $email = strtolower(
            trim(
                $request->input('email')
            )
        );


        // ========================================================
        // RESET OLD PASSWORD RESET SESSION
        // ========================================================

        /*
        |--------------------------------------------------------------------------
        | Jika user meminta kode baru, status verifikasi sebelumnya
        | harus selalu dibatalkan.
        |--------------------------------------------------------------------------
        */

        $request->session()->forget([
            'password_reset_verified',
            'password_reset_code_sent',
        ]);


        // ========================================================
        // RATE LIMIT PASSWORD RESET REQUEST
        // ========================================================

        $rateLimitKey =
            'password-reset:' .
            $request->ip() .
            ':' .
            $email;

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                3
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'Terlalu banyak permintaan. Silakan coba lagi beberapa saat.',
                ]);
        }

        RateLimiter::hit(
            $rateLimitKey,
            60
        );


        // ========================================================
        // FIND USER
        // ========================================================

        $user = User::where(
            'email',
            $email
        )->first();


        // ========================================================
        // GENERIC RESPONSE
        // ========================================================

        /*
        |--------------------------------------------------------------------------
        | Jangan memberitahu user apakah email terdaftar.
        |
        | Ini mencegah email enumeration.
        |
        | Jika email tidak ditemukan:
        |
        | - jangan membuat OTP
        | - jangan membuat password_reset_email
        | - jangan membuat password_reset_code_sent
        | - jangan menampilkan form OTP
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return back()
                ->withInput()
                ->with(
                    'success',
                    'Jika email terdaftar di Zalina Fashion, kode verifikasi akan dikirim ke email tersebut.'
                );
        }


        // ========================================================
        // INVALIDATE OLD RESET CODES
        // ========================================================

        PasswordResetCode::where(
            'email',
            $email
        )
            ->where(
                'used',
                false
            )
            ->update([
                'used' => true,
            ]);


        // ========================================================
        // GENERATE 6 DIGIT OTP
        // ========================================================

        $code = (string) random_int(
            100000,
            999999
        );


        // ========================================================
        // SAVE HASHED OTP
        // ========================================================

        PasswordResetCode::create([
            'email' =>
                $email,

            'code' =>
                Hash::make(
                    $code
                ),

            'expires_at' =>
                now()->addMinutes(10),

            'used' =>
                false,
        ]);


        // ========================================================
        // SEND OTP EMAIL
        // ========================================================

        try {
            Mail::raw(
                "Halo {$user->name},\n\n"
                . "Kami menerima permintaan untuk mengatur ulang kata sandi akun Zalina Fashion Anda.\n\n"
                . "Kode verifikasi Anda:\n\n"
                . "{$code}\n\n"
                . "Kode ini berlaku selama 10 menit.\n\n"
                . "Jika Anda tidak meminta reset kata sandi, abaikan email ini.\n\n"
                . "Salam,\n"
                . "Zalina Fashion",
                function ($message) use ($email) {
                    $message
                        ->to($email)
                        ->subject(
                            'Kode Verifikasi Reset Kata Sandi - Zalina Fashion'
                        );
                }
            );
        } catch (\Throwable $e) {

            // ====================================================
            // INVALIDATE CODE IF EMAIL FAILED
            // ====================================================

            PasswordResetCode::where(
                'email',
                $email
            )
                ->where(
                    'used',
                    false
                )
                ->update([
                    'used' => true,
                ]);


            // ====================================================
            // CLEAR RESET SESSION
            // ====================================================

            $request->session()->forget([
                'password_reset_email',
                'password_reset_code_sent',
                'password_reset_verified',
            ]);


            // ====================================================
            // RETURN ERROR
            // ====================================================

            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'Kode verifikasi gagal dikirim. Silakan coba kembali.',
                ]);
        }


        // ========================================================
        // SAVE RESET EMAIL SESSION
        // ========================================================

        $request->session()->put(
            'password_reset_email',
            $email
        );


        // ========================================================
        // MARK CODE AS SENT
        // ========================================================

        /*
        |--------------------------------------------------------------------------
        | INI ADALAH BAGIAN PENTING.
        |
        | Form OTP BARU boleh ditampilkan setelah proses Mail::raw()
        | berhasil.
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'password_reset_code_sent',
            true
        );


        // ========================================================
        // MAKE SURE VERIFICATION IS NOT ACTIVE
        // ========================================================

        $request->session()->forget(
            'password_reset_verified'
        );


        // ========================================================
        // REDIRECT BACK TO FORGOT PASSWORD
        // ========================================================

        return redirect()
            ->route(
                'password.forgot'
            )
            ->with(
                'success',
                'Kode verifikasi telah dikirim ke email Anda. Silakan periksa inbox.'
            );
    }


    // ============================================================
    // VERIFY PASSWORD RESET CODE
    // ============================================================

    public function verifyCode(Request $request)
    {
        // ========================================================
        // CHECK WHETHER CODE WAS REQUESTED
        // ========================================================

        if (
            !$request->session()->get(
                'password_reset_code_sent',
                false
            )
        ) {
            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'code' =>
                        'Silakan minta kode verifikasi terlebih dahulu.',
                ]);
        }


        // ========================================================
        // VALIDATE OTP
        // ========================================================

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


        // ========================================================
        // GET RESET EMAIL FROM SESSION
        // ========================================================

        $email = $request->session()->get(
            'password_reset_email'
        );


        // ========================================================
        // CHECK RESET EMAIL
        // ========================================================

        if (!$email) {
            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'code' =>
                        'Sesi reset password sudah berakhir. Silakan minta kode baru.',
                ]);
        }


        // ========================================================
        // GET LATEST ACTIVE CODE
        // ========================================================

        $resetCode = PasswordResetCode::where(
            'email',
            $email
        )
            ->where(
                'used',
                false
            )
            ->latest()
            ->first();


        // ========================================================
        // CODE NOT FOUND
        // ========================================================

        if (!$resetCode) {
            $request->session()->forget(
                'password_reset_code_sent'
            );

            return back()
                ->withErrors([
                    'code' =>
                        'Kode verifikasi tidak ditemukan. Silakan minta kode baru.',
                ]);
        }


        // ========================================================
        // CHECK CODE EXPIRATION
        // ========================================================

        if (
            !$resetCode->expires_at ||
            $resetCode->expires_at->isPast()
        ) {
            $resetCode->update([
                'used' => true,
            ]);

            $request->session()->forget(
                'password_reset_code_sent'
            );

            return back()
                ->withErrors([
                    'code' =>
                        'Kode verifikasi sudah kedaluwarsa. Silakan minta kode baru.',
                ]);
        }


        // ========================================================
        // VERIFY HASHED OTP
        // ========================================================

        if (
            !Hash::check(
                $request->input('code'),
                $resetCode->code
            )
        ) {
            return back()
                ->withErrors([
                    'code' =>
                        'Kode verifikasi salah.',
                ]);
        }


        // ========================================================
        // MARK OTP AS USED
        // ========================================================

        $resetCode->update([
            'used' => true,
        ]);


        // ========================================================
        // SAVE VERIFIED SESSION
        // ========================================================

        $request->session()->put(
            'password_reset_verified',
            true
        );


        // ========================================================
        // REDIRECT TO RESET PASSWORD FORM
        // ========================================================

        return redirect()
            ->route(
                'password.reset.form'
            )
            ->with(
                'success',
                'Kode verifikasi berhasil. Silakan buat kata sandi baru.'
            );
    }


    // ============================================================
    // RESET PASSWORD FORM
    // ============================================================

    public function showResetPassword(Request $request)
    {
        // ========================================================
        // CHECK VERIFICATION
        // ========================================================

        if (
            !$request->session()->get(
                'password_reset_verified',
                false
            )
        ) {
            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'code' =>
                        'Silakan verifikasi kode terlebih dahulu.',
                ]);
        }


        // ========================================================
        // CHECK RESET EMAIL
        // ========================================================

        if (
            !$request->session()->get(
                'password_reset_email'
            )
        ) {
            $request->session()->forget([
                'password_reset_verified',
                'password_reset_code_sent',
            ]);

            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'code' =>
                        'Sesi reset password sudah berakhir. Silakan ulangi proses.',
                ]);
        }


        // ========================================================
        // DISPLAY RESET PASSWORD PAGE
        // ========================================================

        return view(
            'store.reset-password'
        );
    }


    // ============================================================
    // RESET PASSWORD
    // ============================================================

    public function resetPassword(Request $request)
    {
        // ========================================================
        // CHECK VERIFICATION SESSION
        // ========================================================

        if (
            !$request->session()->get(
                'password_reset_verified',
                false
            )
        ) {
            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'password' =>
                        'Sesi reset password tidak valid. Silakan verifikasi kode terlebih dahulu.',
                ]);
        }


        // ========================================================
        // VALIDATE NEW PASSWORD
        // ========================================================

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.required' =>
                'Kata sandi baru wajib diisi.',

            'password.string' =>
                'Kata sandi tidak valid.',

            'password.min' =>
                'Kata sandi minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi kata sandi tidak cocok.',
        ]);


        // ========================================================
        // GET RESET EMAIL
        // ========================================================

        $email = $request->session()->get(
            'password_reset_email'
        );


        // ========================================================
        // CHECK EMAIL SESSION
        // ========================================================

        if (!$email) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_code_sent',
                'password_reset_verified',
            ]);

            return redirect()
                ->route(
                    'password.forgot'
                )
                ->withErrors([
                    'password' =>
                        'Sesi reset password sudah berakhir. Silakan ulangi proses.',
                ]);
        }


        // ========================================================
        // FIND USER
        // ========================================================

        $user = User::where(
            'email',
            $email
        )->first();


        // ========================================================
        // USER NOT FOUND
        // ========================================================

        if (!$user) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_code_sent',
                'password_reset_verified',
                'show_password_reset',
            ]);

            return redirect()
                ->route(
                    'profile'
                )
                ->with(
                    'error',
                    'Akun tidak ditemukan.'
                );
        }


        // ========================================================
        // UPDATE PASSWORD
        // ========================================================

        $user->update([
            'password' =>
                Hash::make(
                    $request->input('password')
                ),
        ]);


        // ========================================================
        // EMAIL SUDAH TERBUKTI MILIK USER
        // ========================================================

        /*
        |--------------------------------------------------------------------------
        | Untuk sampai ke titik ini, user sudah membuktikan kepemilikan
        | emailnya dengan memasukkan kode OTP yang dikirim ke email
        | tersebut (lihat verifyCode()). Ini adalah bukti kepemilikan
        | email yang setara dengan verifikasi email biasa.
        |
        | Kalau akun ini kebetulan belum pernah verifikasi email
        | (mis. lupa password sebelum sempat verifikasi saat daftar),
        | jangan minta dia verifikasi ulang lagi setelah ini - cukup
        | tandai email sudah terverifikasi sekalian.
        |--------------------------------------------------------------------------
        */

        if (is_null($user->email_verified_at)) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }


        // ========================================================
        // INVALIDATE ALL REMAINING RESET CODES
        // ========================================================

        PasswordResetCode::where(
            'email',
            $email
        )
            ->where(
                'used',
                false
            )
            ->update([
                'used' => true,
            ]);


        // ========================================================
        // CLEAR PASSWORD RESET SESSION
        // ========================================================

        $request->session()->forget([
            'password_reset_email',
            'password_reset_code_sent',
            'password_reset_verified',
            'show_password_reset',
        ]);


        // ========================================================
        // CLEAR FAILED LOGIN COUNTER
        // ========================================================

        $failedPasswordKey =
            'failed_password:' . $email;

        $request->session()->forget(
            $failedPasswordKey
        );


        // ========================================================
        // SUCCESS
        // ========================================================

        return redirect()
            ->route(
                'profile'
            )
            ->with(
                'success',
                'Kata sandi berhasil diubah. Silakan login menggunakan kata sandi baru.'
            );
    }
}