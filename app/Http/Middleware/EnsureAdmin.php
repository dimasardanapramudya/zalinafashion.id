<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * Middleware ini memastikan:
     *
     * 1. User sudah login.
     * 2. Session zalina_user_id masih valid.
     * 3. User yang login benar-benar ada di database.
     * 4. User memiliki role admin.
     * 5. Customer tidak dapat mengakses halaman admin.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | 1. Ambil User ID dari Session
        |--------------------------------------------------------------------------
        |
        | Sistem autentikasi Zalina menggunakan:
        |
        | zalina_user_id
        |
        */

        $userId = $request->session()->get(
            'zalina_user_id'
        );


        /*
        |--------------------------------------------------------------------------
        | 2. User Belum Login
        |--------------------------------------------------------------------------
        |
        | Jika session user tidak tersedia, user dianggap belum login.
        |
        */

        if (!$userId) {

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Silakan login terlebih dahulu untuk mengakses halaman admin.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Ambil User dari Database
        |--------------------------------------------------------------------------
        |
        | Session saja tidak cukup.
        | Kita tetap mengambil user dari database untuk memastikan
        | akun tersebut masih ada.
        |
        */

        $user = User::find($userId);


        /*
        |--------------------------------------------------------------------------
        | 4. User Tidak Ditemukan
        |--------------------------------------------------------------------------
        |
        | Jika session masih memiliki ID tetapi akun sudah tidak ada,
        | jangan izinkan akses admin.
        |
        */

        if (!$user) {

            /*
            | Hapus session login yang sudah tidak valid.
            */

            $request->session()->forget(
                'zalina_user_id'
            );

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Sesi akun tidak valid. Silakan login kembali.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Periksa Role Admin
        |--------------------------------------------------------------------------
        |
        | Hanya user dengan role:
        |
        | admin
        |
        | yang boleh masuk ke area admin.
        |
        */

        if ($user->role !== 'admin') {

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Akses admin tidak tersedia untuk akun ini.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | 6. User Adalah Admin
        |--------------------------------------------------------------------------
        |
        | Semua pemeriksaan berhasil.
        | Request boleh diteruskan ke halaman admin.
        |
        */

        return $next($request);
    }
}