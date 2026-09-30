<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoggedIn
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Cek Login
        |--------------------------------------------------------------------------
        */

        if (!session('zalina_user_id')) {

            /*
            |--------------------------------------------------------------------------
            | REQUEST GET
            |--------------------------------------------------------------------------
            |
            | GET adalah halaman yang aman untuk dijadikan intended URL.
            |
            */

            if ($request->isMethod('GET')) {

                $request->session()->put(
                    'url.intended',
                    $request->fullUrl()
                );
            }


            /*
            |--------------------------------------------------------------------------
            | REQUEST SELAIN GET
            |--------------------------------------------------------------------------
            |
            | Jangan biarkan intended URL lama digunakan kembali.
            |
            | Contoh:
            |
            | POST /cart/pashmina-ceruty-premium
            |
            | URL tersebut adalah endpoint POST, bukan halaman GET.
            |
            */

            else {

                $request->session()->forget(
                    'url.intended'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Redirect ke Profile
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Silakan login terlebih dahulu untuk melanjutkan pesanan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | User Sudah Login
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}