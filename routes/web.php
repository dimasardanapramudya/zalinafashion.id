<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use Laravel\Socialite\Facades\Socialite;

use App\Models\User;

use App\Http\Controllers\{
    StoreController,
    AuthController,
    CartController,
    CheckoutController,
    ShippingController,
    PasswordResetController,
    ReturnController,
    FavoriteController
};

use App\Http\Controllers\Admin\{
    DashboardController,
    ProductController,
    CategoryController,
    OrderController,
    PaymentController,
    SettingController,
    HomeSliderController,
    DiscountController,
    ReturnManagementController,
    SalesRecapController
};


/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
|
| Zalina Fashion Laravel Store
|
| File ini menangani:
|
| 1. Store
| 2. Authentication
| 3. Google Login
| 4. Forgot Password
| 5. Password Reset
| 6. Cart
| 7. Checkout
| 8. Payment
| 9. Receipt
| 10. Customer Delivery Confirmation
| 11. Shipping
| 12. RajaOngkir Testing
| 13. Admin Panel
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| STORE ROUTES
|--------------------------------------------------------------------------
|
| Route publik yang dapat diakses oleh semua pengunjung.
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| HOMEPAGE
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [StoreController::class, 'home']
)->name('home');


/*
|--------------------------------------------------------------------------
| CATEGORY STORE
|--------------------------------------------------------------------------
|
| HALAMAN SEMUA KATEGORI
|
| URL:
| /kategori
|
| Controller:
| StoreController@categories
|
| View:
| resources/views/store/categories.blade.php
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/kategori',
    [StoreController::class, 'categories']
)->name('categories');


/*
|--------------------------------------------------------------------------
| CATEGORY INDEX ALIAS
|--------------------------------------------------------------------------
|
| Alias untuk halaman semua kategori.
|
| URL tetap:
| /kategori
|
| Nama route:
| category.index
|
| Ini diperlukan oleh beberapa bagian Blade yang menggunakan:
|
| route('category.index')
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/kategori',
    [StoreController::class, 'categories']
)->name('category.index');


/*
|--------------------------------------------------------------------------
| CATEGORY DETAIL
|--------------------------------------------------------------------------
|
| HALAMAN PRODUK BERDASARKAN KATEGORI
|
| Contoh:
|
| /kategori/pashmina
| /kategori/bergo
| /kategori/instant
|
| Controller:
| StoreController@category
|
| View:
| resources/views/store/category.blade.php
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/kategori/{category:slug}',
    [StoreController::class, 'category']
)->name('category.show');


/*
|--------------------------------------------------------------------------
| SHOP
|--------------------------------------------------------------------------
|
| HALAMAN SEMUA PRODUK
|
| URL:
| /shop
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/shop',
    [StoreController::class, 'shop']
)->name('shop');


/*
|--------------------------------------------------------------------------
| SEARCH SUGGESTIONS (LIVE)
|--------------------------------------------------------------------------
|
| Dipanggil lewat fetch() dari kolom pencarian di header untuk
| menampilkan saran produk saat user mengetik.
|
| URL:
| /search/suggest?search=...
|
| Controller:
| StoreController@searchSuggest
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/search/suggest',
    [StoreController::class, 'searchSuggest']
)->name('search.suggest');


/*
|--------------------------------------------------------------------------
| PRODUCT DETAIL
|--------------------------------------------------------------------------
|
| HALAMAN DETAIL SATU PRODUK
|
| Contoh:
|
| /product/pashmina-voal
|
| Controller:
| StoreController@product
|
| View:
| resources/views/store/product.blade.php
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/product/{product:slug}',
    [StoreController::class, 'product']
)->name('product.show');


/*
|--------------------------------------------------------------------------
| TENTANG KAMI (ABOUT US)
|--------------------------------------------------------------------------
|
| HALAMAN TENTANG KAMI
|
| URL:
| /tentang-kami
|
| Controller:
| StoreController@about
|
| View:
| resources/views/store/about.blade.php
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/about',
    [StoreController::class, 'about']
)->name('about');

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
|
| Profile tetap dapat dibuka tanpa login.
|
| Jika belum login:
| Menampilkan Login dan Register.
|
| Jika sudah login:
| Menampilkan Profile dan Riwayat Pesanan.
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/profile',
    [AuthController::class, 'profile']
)->name('profile');


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get(
    '/profile/login',
    [AuthController::class, 'loginForm']
)->name('login.form');

Route::post(
    '/profile/login',
    [AuthController::class, 'login']
)->name('login');


/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

Route::get(
    '/profile/register',
    [AuthController::class, 'registerForm']
)->name('register.form');

Route::post(
    '/profile/register',
    [AuthController::class, 'register']
)->name('register');

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/

Route::get(
    '/verify-email',
    [AuthController::class, 'showEmailVerification']
)->name('verification.notice');

Route::post(
    '/verify-email',
    [AuthController::class, 'verifyEmail']
)->name('verification.verify');

Route::post(
    '/verify-email/resend',
    [AuthController::class, 'resendEmailVerificationCode']
)->name('verification.resend');

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->name('logout');


/*
|--------------------------------------------------------------------------
| FORGOT PASSWORD
|--------------------------------------------------------------------------
|
| Route forgot password TIDAK menggunakan middleware
| zalina.login karena user memang belum login.
|
| Alur:
|
| 1. User salah password 3 kali.
| 2. Link "Lupa Kata Sandi?" muncul.
| 3. User membuka halaman forgot password.
| 4. User memasukkan email.
| 5. Sistem mengirim kode OTP melalui Gmail.
| 6. OTP diverifikasi.
| 7. User membuat password baru.
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| FORGOT PASSWORD PAGE
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password',
    [PasswordResetController::class, 'showForgotPassword']
)->name('password.forgot');


/*
|--------------------------------------------------------------------------
| SEND PASSWORD RESET CODE
|--------------------------------------------------------------------------
*/

Route::post(
    '/forgot-password',
    [PasswordResetController::class, 'sendCode']
)->name('password.send');


/*
|--------------------------------------------------------------------------
| VERIFY PASSWORD RESET CODE
|--------------------------------------------------------------------------
*/

Route::post(
    '/forgot-password/verify',
    [PasswordResetController::class, 'verifyCode']
)->name('password.verify');


/*
|--------------------------------------------------------------------------
| RESET PASSWORD FORM
|--------------------------------------------------------------------------
*/

Route::get(
    '/reset-password',
    [PasswordResetController::class, 'showResetPassword']
)->name('password.reset.form');


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

Route::post(
    '/reset-password',
    [PasswordResetController::class, 'resetPassword']
)->name('password.reset');


/*
|--------------------------------------------------------------------------
| GOOGLE AUTHENTICATION
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| REDIRECT TO GOOGLE
|--------------------------------------------------------------------------
*/

Route::get(
    '/auth/google',
    function () {

        return Socialite::driver('google')
            ->stateless()
            ->redirect();

    }
)->name('google.login');


/*
|--------------------------------------------------------------------------
| GOOGLE CALLBACK
|--------------------------------------------------------------------------
*/

Route::get(
    '/auth/google/callback',
    function () {

        try {

            /*
            |--------------------------------------------------------------------------
            | GET GOOGLE USER
            |--------------------------------------------------------------------------
            */

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();


            /*
            |--------------------------------------------------------------------------
            | GET GOOGLE EMAIL
            |--------------------------------------------------------------------------
            */

            $email = $googleUser->getEmail();


            /*
            |--------------------------------------------------------------------------
            | VALIDATE EMAIL
            |--------------------------------------------------------------------------
            */

            if (!$email) {

                return redirect()
                    ->route('profile')
                    ->with(
                        'error',
                        'Google tidak memberikan alamat email untuk akun ini.'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | FIND EXISTING USER
            |--------------------------------------------------------------------------
            */

            $user = User::where(
                'email',
                $email
            )->first();


            /*
            |--------------------------------------------------------------------------
            | CREATE CUSTOMER
            |--------------------------------------------------------------------------
            */

            if (!$user) {

                $user = User::create([

                    'name' =>
                        $googleUser->getName()
                        ?: 'Customer Zalina',

                    'email' =>
                        $email,

                    'password' =>
                        Hash::make(
                            Str::random(40)
                        ),

                    'role' =>
                        'customer',

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | SAVE USER SESSION
            |--------------------------------------------------------------------------
            */

            request()
                ->session()
                ->put(
                    'zalina_user_id',
                    $user->id
                );


            /*
            |--------------------------------------------------------------------------
            | REGENERATE SESSION
            |--------------------------------------------------------------------------
            */

            request()
                ->session()
                ->regenerate();


            /*
            |--------------------------------------------------------------------------
            | CLEAR PASSWORD RESET STATE
            |--------------------------------------------------------------------------
            */

            request()
                ->session()
                ->forget([
                    'show_password_reset',
                    'password_reset_email',
                ]);


            /*
            |--------------------------------------------------------------------------
            | REDIRECT ADMIN
            |--------------------------------------------------------------------------
            */

            if ($user->role === 'admin') {

                return redirect()
                    ->route('admin.dashboard')
                    ->with(
                        'success',
                        'Login Google berhasil. Selamat datang kembali!'
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | REDIRECT CUSTOMER
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('profile')
                ->with(
                    'success',
                    'Login dengan Google berhasil. Selamat datang di Zalina Fashion!'
                );


        } catch (\Throwable $e) {

            return redirect()
                ->route('profile')
                ->with(
                    'error',
                    'Login Google gagal. Silakan coba kembali.'
                );

        }

    }
)->name('google.callback');


/*
|--------------------------------------------------------------------------
| CART ROUTES
|--------------------------------------------------------------------------
|
| CART dibuat wajib login agar customer tidak dapat
| melanjutkan proses pemesanan tanpa akun.
|
|--------------------------------------------------------------------------
*/

Route::middleware(
    'zalina.login'
)->group(
    function () {


        /*
        |--------------------------------------------------------------------------
        | CART PAGE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cart',
            [CartController::class, 'index']
        )->name('cart.index');


        /*
        |--------------------------------------------------------------------------
        | CART PRODUCT GET FALLBACK
        |--------------------------------------------------------------------------
        |
        | Jika URL /cart/{product} dibuka langsung atau direfresh,
        | arahkan kembali ke halaman produk.
        |
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cart/{product:slug}',
            function (\App\Models\Product $product) {

                return redirect()
                    ->route('product.show', $product);

            }
        )->name('cart.product.fallback');


        /*
        |--------------------------------------------------------------------------
        | ADD PRODUCT TO CART
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/cart/{product:slug}',
            [CartController::class, 'add']
        )->name('cart.add');


        /*
        |--------------------------------------------------------------------------
        | UPDATE CART ITEM
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/cart/item/{item}',
            [CartController::class, 'update']
        )->name('cart.update');


        /*
        |--------------------------------------------------------------------------
        | DELETE CART ITEM
        |--------------------------------------------------------------------------
        */

        Route::delete(
            '/cart/item/{item}',
            [CartController::class, 'destroy']
        )->name('cart.destroy');


    }
);


/*
|--------------------------------------------------------------------------
| CHECKOUT ROUTES
|--------------------------------------------------------------------------
|
| Semua proses checkout WAJIB LOGIN.
|
|--------------------------------------------------------------------------
*/

Route::middleware(
    'zalina.login'
)->group(
    function () {


        /*
        |--------------------------------------------------------------------------
        | CHECKOUT PAGE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/checkout',
            [CheckoutController::class, 'show']
        )->name('checkout.show');


        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/checkout',
            [CheckoutController::class, 'store']
        )->name('checkout.store');


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER RECEIPT PAGE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/order/{order}/receipt',
            [CheckoutController::class, 'receipt']
        )->name('receipt.show');


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD RECEIPT
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/order/{order}/receipt/download',
            [CheckoutController::class, 'downloadReceipt']
        )->name('receipt.download');


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER CONFIRM DELIVERY
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/order/{order}/delivered',
            [CheckoutController::class, 'confirmDelivered']
        )->name('order.delivered');





        Route::post(
            '/order/{order}/return',
            [ReturnController::class, 'store']
        )->name('order.return.request');


    }
);


/*
|--------------------------------------------------------------------------
| PUBLIC PAYMENT ROUTES
|--------------------------------------------------------------------------
|
| Guest dapat membuka pembayaran menggunakan validasi
| kepemilikan order di CheckoutController.
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/order/{order}/payment',
    [CheckoutController::class, 'payment']
)->name('payment.show');


Route::post(
    '/order/{order}/payment-proof',
    [CheckoutController::class, 'upload']
)->name('payment.upload');


/*
|--------------------------------------------------------------------------
| SHIPPING ROUTES
|--------------------------------------------------------------------------
|
| Shipping destination dan perhitungan ongkir dibuat
| PUBLIC agar request AJAX tidak diarahkan ke /profile.
|
| Catatan:
| - Endpoint destination dapat diakses tanpa login.
| - Endpoint calculate dapat diakses tanpa login.
| - Keamanan checkout tetap dijaga oleh middleware
|   zalina.login pada route checkout.
| - Endpoint dibungkus throttle untuk mencegah
|   penyalahgunaan (spam request ke API RajaOngkir).
|
|--------------------------------------------------------------------------
*/

Route::middleware(
    'throttle:30,1'
)->group(
    function () {


        /*
        |--------------------------------------------------------------------------
        | SEARCH DESTINATION
        |--------------------------------------------------------------------------
        |
        | GET:
        | /shipping/destinations
        |
        | Route ini tidak menggunakan middleware zalina.login
        | karena dipanggil oleh AJAX pada halaman checkout.
        |
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/shipping/destinations',
            [ShippingController::class, 'destinations']
        )->name(
            'shipping.destinations'
        );


        /*
        |--------------------------------------------------------------------------
        | CALCULATE SHIPPING COST
        |--------------------------------------------------------------------------
        |
        | POST:
        | /shipping/calculate
        |
        | Route ini tidak menggunakan middleware zalina.login
        | agar request AJAX tidak mendapatkan redirect 302
        | ke halaman /profile.
        |
        | Validasi customer dan order tetap dilakukan
        | di dalam ShippingController jika diperlukan.
        |
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/shipping/calculate',
            [ShippingController::class, 'calculate']
        )->name(
            'shipping.calculate'
        );


    }
);


/*
|--------------------------------------------------------------------------
| RAJAONGKIR TEST ROUTES
|--------------------------------------------------------------------------
|
| Route testing ini dipertahankan, TAPI dibatasi hanya untuk admin.
|
| Sebelumnya route ini bisa diakses publik tanpa login dan
| membocorkan sebagian API key (api_key_first_4 / api_key_last_4)
| serta raw response dari RajaOngkir ke siapa saja. Sekarang
| dibungkus middleware zalina.admin agar hanya admin yang login
| yang bisa menggunakannya untuk testing.
|
|--------------------------------------------------------------------------
*/

Route::middleware(
    'zalina.admin'
)->group(
    function () {


/*
|--------------------------------------------------------------------------
| TEST 1
|--------------------------------------------------------------------------
|
| SEARCH GRESIK
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/test-rajaongkir-gresik',
    function () {

        $apiKey = trim(
            (string) config(
                'services.rajaongkir.api_key'
            )
        );


        $baseUrl = rtrim(
            (string) config(
                'services.rajaongkir.base_url'
            ),
            '/'
        );


        $requestUrl =
            $baseUrl .
            '/destination/domestic-destination';


        try {

            $response = Http::timeout(30)

                ->acceptJson()

                ->withHeaders([
                    'key' => $apiKey,
                ])

                ->get(
                    $requestUrl,
                    [
                        'search' => 'Gresik',
                        'limit' => 20,
                        'offset' => 0,
                    ]
                );


            return response()->json(
                [
                    'success' =>
                        $response->successful(),

                    'http_status' =>
                        $response->status(),

                    'base_url' =>
                        $baseUrl,

                    'request_url' =>
                        $requestUrl,

                    'api_key_loaded' =>
                        !empty($apiKey),

                    'api_key_length' =>
                        strlen($apiKey),

                    'api_key_first_4' =>
                        substr(
                            $apiKey,
                            0,
                            4
                        ),

                    'api_key_last_4' =>
                        substr(
                            $apiKey,
                            -4
                        ),

                    'api_response' =>
                        $response->json(),

                ],
                $response->successful()
                    ? 200
                    : $response->status()
            );


        } catch (\Throwable $e) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'Terjadi kesalahan saat menghubungi RajaOngkir.',

                    'error_message' =>
                        $e->getMessage(),

                ],
                500
            );

        }

    }
)->name(
    'test.rajaongkir.gresik'
);


/*
|--------------------------------------------------------------------------
| TEST 2
|--------------------------------------------------------------------------
|
| SEARCH DESTINATION
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/test-rajaongkir-destination',
    function () {

        $search = request()->get(
            'search',
            'Gresik'
        );


        $apiKey = trim(
            (string) config(
                'services.rajaongkir.api_key'
            )
        );


        $baseUrl = rtrim(
            (string) config(
                'services.rajaongkir.base_url'
            ),
            '/'
        );


        $requestUrl =
            $baseUrl .
            '/destination/domestic-destination';


        try {

            $response = Http::timeout(30)

                ->acceptJson()

                ->withHeaders([
                    'key' => $apiKey,
                ])

                ->get(
                    $requestUrl,
                    [
                        'search' =>
                            $search,

                        'limit' =>
                            20,

                        'offset' =>
                            0,

                    ]
                );


            return response()->json(
                [
                    'success' =>
                        $response->successful(),

                    'http_status' =>
                        $response->status(),

                    'base_url' =>
                        $baseUrl,

                    'request_url' =>
                        $requestUrl,

                    'search' =>
                        $search,

                    'api_key_loaded' =>
                        !empty($apiKey),

                    'api_response' =>
                        $response->json(),

                ],
                $response->successful()
                    ? 200
                    : $response->status()
            );


        } catch (\Throwable $e) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'Terjadi kesalahan saat mencari destination.',

                    'error_message' =>
                        $e->getMessage(),

                ],
                500
            );

        }

    }
)->name(
    'test.rajaongkir.destination'
);


/*
|--------------------------------------------------------------------------
| TEST 3
|--------------------------------------------------------------------------
|
| CALCULATE SHIPPING COST
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/test-rajaongkir-cost',
    function () {

        $apiKey = trim(
            (string) config(
                'services.rajaongkir.api_key'
            )
        );


        $baseUrl = rtrim(
            (string) config(
                'services.rajaongkir.base_url'
            ),
            '/'
        );


        $origin = trim(
            (string) config(
                'services.rajaongkir.origin_id'
            )
        );


        /*
        |--------------------------------------------------------------------------
        | REQUEST PARAMETERS
        |--------------------------------------------------------------------------
        */

        $destination = (int) request()->get(
            'destination',
            69366
        );


        $weight = (int) request()->get(
            'weight',
            500
        );


        $courier = strtolower(
            trim(
                (string) request()->get(
                    'courier',
                    'jne'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | MINIMUM WEIGHT
        |--------------------------------------------------------------------------
        */

        $minimumWeight = (int) config(
            'services.rajaongkir.minimum_weight',
            100
        );


        if ($weight < $minimumWeight) {

            $weight =
                $minimumWeight;

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE API KEY
        |--------------------------------------------------------------------------
        */

        if (empty($apiKey)) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'RAJAONGKIR_API_KEY belum terbaca.',

                ],
                500
            );

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE ORIGIN
        |--------------------------------------------------------------------------
        */

        if (empty($origin)) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'RAJAONGKIR_ORIGIN_ID belum diatur.',

                ],
                500
            );

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE DESTINATION
        |--------------------------------------------------------------------------
        */

        if ($destination <= 0) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'Destination ID tidak valid.',

                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST URL
        |--------------------------------------------------------------------------
        */

        $requestUrl =
            $baseUrl .
            '/calculate/domestic-cost';


        try {

            $response = Http::timeout(30)

                ->acceptJson()

                ->withHeaders([
                    'key' =>
                        $apiKey,
                ])

                ->asForm()

                ->post(
                    $requestUrl,
                    [
                        'origin' =>
                            $origin,

                        'destination' =>
                            $destination,

                        'weight' =>
                            $weight,

                        'courier' =>
                            $courier,

                    ]
                );


            return response()->json(
                [
                    'success' =>
                        $response->successful(),

                    'http_status' =>
                        $response->status(),

                    'base_url' =>
                        $baseUrl,

                    'request_url' =>
                        $requestUrl,

                    'request_data' => [
                        'origin' =>
                            (int) $origin,

                        'destination' =>
                            $destination,

                        'weight' =>
                            $weight,

                        'courier' =>
                            $courier,

                    ],

                    'api_response' =>
                        $response->json(),

                    'raw_response' =>
                        $response->body(),

                ],
                $response->successful()
                    ? 200
                    : $response->status()
            );


        } catch (\Throwable $e) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'Terjadi kesalahan saat menghitung ongkir.',

                    'error_type' =>
                        get_class($e),

                    'error_message' =>
                        $e->getMessage(),

                ],
                500
            );

        }

    }
)->name(
    'test.rajaongkir.cost'
);


/*
|--------------------------------------------------------------------------
| TEST 4 — RAJAONGKIR TRACKING AWB
|--------------------------------------------------------------------------
|
| Route sementara untuk menguji Tracking AWB RajaOngkir / Komerce.
|
|--------------------------------------------------------------------------
*/

Route::get(
    '/test-rajaongkir-tracking',
    function () {

        /*
        |--------------------------------------------------------------------------
        | API CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $apiKey = trim(
            (string) config(
                'services.rajaongkir.api_key'
            )
        );


        $baseUrl = rtrim(
            (string) config(
                'services.rajaongkir.base_url',
                'https://rajaongkir.komerce.id/api/v1'
            ),
            '/'
        );


        /*
        |--------------------------------------------------------------------------
        | TEST AWB
        |--------------------------------------------------------------------------
        */

        $awb = 'ISI_RESI_VALID_DI_SINI';


        /*
        |--------------------------------------------------------------------------
        | TEST COURIER
        |--------------------------------------------------------------------------
        */

        $courier = 'jne';


        /*
        |--------------------------------------------------------------------------
        | VALIDATE API KEY
        |--------------------------------------------------------------------------
        */

        if ($apiKey === '') {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'RAJAONGKIR_API_KEY belum terbaca dari konfigurasi Laravel.',

                ],
                500
            );

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE AWB
        |--------------------------------------------------------------------------
        */

        if (
            $awb === ''
            || $awb === 'ISI_RESI_VALID_DI_SINI'
        ) {

            return response()->json(
                [
                    'success' => false,

                    'message' =>
                        'Silakan isi nomor AWB/resi valid pada route testing.',

                    'example' => [
                        'awb' =>
                            'NOMOR_RESI_VALID',

                        'courier' =>
                            $courier,
                    ],

                ],
                422
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PREPARE PARAMETERS
        |--------------------------------------------------------------------------
        */

        $params = [

            'awb' =>
                trim($awb),

            'courier' =>
                strtolower(
                    trim($courier)
                ),

        ];


        /*
        |--------------------------------------------------------------------------
        | TRACKING ENDPOINT
        |--------------------------------------------------------------------------
        */

        $requestUrl =
            $baseUrl .
            '/track/waybill';


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        try {

            $query = http_build_query(
                $params
            );


            $response = Http::timeout(
                (int) config(
                    'services.rajaongkir.timeout',
                    30
                )
            )

                ->acceptJson()

                ->withHeaders([
                    'key' =>
                        $apiKey,
                ])

                ->post(
                    $requestUrl . '?' . $query
                );


            /*
            |--------------------------------------------------------------------------
            | API RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [

                    'success' =>
                        $response->successful(),

                    'http_status' =>
                        $response->status(),

                    'endpoint' =>
                        $requestUrl,

                    'request' => [

                        'awb' =>
                            $params['awb'],

                        'courier' =>
                            $params['courier'],

                    ],

                    'api_response' =>
                        $response->json(),

                ],

                $response->successful()
                    ? 200
                    : $response->status()

            );


        } catch (\Throwable $e) {

            return response()->json(
                [

                    'success' => false,

                    'message' =>
                        'Terjadi kesalahan saat menghubungi API Tracking RajaOngkir.',

                    'error_type' =>
                        get_class($e),

                    'error_message' =>
                        $e->getMessage(),

                ],
                500
            );

        }

    }
)->name(
    'test.rajaongkir.tracking'
);


    }
);


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
|
| Semua route admin menggunakan middleware:
|
| zalina.admin
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ADMIN PREFIX / MIDDLEWARE
|--------------------------------------------------------------------------
*/

Route::prefix(
    'admin'
)

    ->name(
        'admin.'
    )

    ->middleware(
        'zalina.admin'
    )

    ->group(

        function () {


            /*
            |--------------------------------------------------------------------------
            | ADMIN DASHBOARD
            |--------------------------------------------------------------------------
            */

            Route::get(

                '/',

                [
                    DashboardController::class,
                    'index'
                ]

            )->name(

                'dashboard'

            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN PRODUCTS
            |--------------------------------------------------------------------------
            */

            Route::resource(

                'products',

                ProductController::class

            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN CATEGORIES
            |--------------------------------------------------------------------------
            */

            Route::get(

                'categories',

                [
                    CategoryController::class,
                    'index'
                ]

            )->name(

                'categories.index'

            );


            Route::post(

                'categories',

                [
                    CategoryController::class,
                    'store'
                ]

            )->name(

                'categories.store'

            );





            Route::put(
                'categories/home',
                [
                    CategoryController::class,
                    'updateHomeCategories'
                ]
            )->name(
                'categories.home'
            );

            Route::delete(

                'categories/{category}',

                [
                    CategoryController::class,
                    'destroy'
                ]

            )->name(

                'categories.destroy'

            );



            Route::put(
                'categories/{category}',
                [
                    CategoryController::class,
                    'update'
                ]
            )->name(
                'categories.update'
            );

            /*
            |--------------------------------------------------------------------------
            | ADMIN ORDERS
            |--------------------------------------------------------------------------
            */

            Route::get(

                'orders',

                [
                    OrderController::class,
                    'index'
                ]

            )->name(

                'orders.index'

            );


            /*
            |--------------------------------------------------------------------------
            | DELETE ORDER HISTORY
            |--------------------------------------------------------------------------
            */

            Route::delete(

                'orders/{order}/history',

                [
                    OrderController::class,
                    'destroyHistory'
                ]

            )->name(

                'orders.destroy-history'

            );


            /*
            |--------------------------------------------------------------------------
            | ORDER DETAIL
            |--------------------------------------------------------------------------
            */

            Route::get(

                'orders/{order}',

                [
                    OrderController::class,
                    'show'
                ]

            )->name(

                'orders.show'

            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE MAIN ORDER STATUS
            |--------------------------------------------------------------------------
            */

            Route::patch(

                'orders/{order}/status',

                [
                    OrderController::class,
                    'updateStatus'
                ]

            )->name(

                'orders.status'

            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHIPPING STATUS
            |--------------------------------------------------------------------------
            */

            Route::post(

                'orders/{order}/shipping-status',

                [
                    OrderController::class,
                    'updateShippingStatus'
                ]

            )->name(

                'orders.shipping-status'

            );


            /*
            |--------------------------------------------------------------------------
            | START PACKING
            |--------------------------------------------------------------------------
            */

            Route::post(

                'orders/{order}/start-packing',

                [
                    OrderController::class,
                    'startPacking'
                ]

            )->name(

                'orders.start-packing'

            );


            /*
            |--------------------------------------------------------------------------
            | HANDOVER TO COURIER
            |--------------------------------------------------------------------------
            */

            Route::post(

                'orders/{order}/handover-to-courier',

                [
                    OrderController::class,
                    'handoverToCourier'
                ]

            )->name(

                'orders.handover-to-courier'

            );


            /*
            |--------------------------------------------------------------------------
            | MARK AS DELIVERED
            |--------------------------------------------------------------------------
            */

            Route::post(

                'orders/{order}/mark-delivered',

                [
                    OrderController::class,
                    'markAsDelivered'
                ]

            )->name(

                'orders.mark-delivered'

            );


            /*
            |--------------------------------------------------------------------------
            | UPDATE TRACKING NUMBER
            |--------------------------------------------------------------------------
            */

            Route::post(

                'orders/{order}/tracking-number',

                [
                    OrderController::class,
                    'updateTrackingNumber'
                ]

            )->name(

                'orders.tracking-number'

            );

            /*
            |--------------------------------------------------------------------------
            | SEND TRACKING TO CUSTOMER  ("Cetak & Kirim Resi")
            |--------------------------------------------------------------------------
            |
            | Menandai resi sudah dikirim ke pelanggan (orders.tracking_sent_at).
            | Setelah ini halaman pesanan pelanggan menampilkan nomor resi,
            | barcode, dan detail pengiriman.
            |
            */

            Route::post(

                'orders/{order}/send-tracking',

                [
                    OrderController::class,
                    'sendTracking'
                ]

            )->name(

                'orders.send-tracking'

            );



            /*
            |--------------------------------------------------------------------------
            | ADMIN RECEIPT
            |--------------------------------------------------------------------------
            */

            Route::get(

                'orders/{order}/receipt',

                [
                    OrderController::class,
                    'printReceipt'
                ]

            )->name(

                'orders.receipt'

            );


            /*
            |--------------------------------------------------------------------------
            | SHIPPING LABEL
            |--------------------------------------------------------------------------
            */

            Route::get(

                'orders/{order}/shipping-label',

                function (

                    \App\Models\Order $order

                ) {

                    return view(

                        'admin.orders.shipping-label',

                        [

                            'order' =>
                                $order,

                        ]

                    );

                }

            )->name(

                'orders.shipping-label'

            );


            /*
            |--------------------------------------------------------------------------
            | ADMIN PAYMENTS
            |--------------------------------------------------------------------------
            */

            Route::get(

                'payments',

                [
                    PaymentController::class,
                    'index'
                ]

            )->name(

                'payments.index'

            );


            /*
            |--------------------------------------------------------------------------
            | VERIFY PAYMENT
            |--------------------------------------------------------------------------
            */

            Route::post(

                'payments/{payment}/verify',

                [
                    PaymentController::class,
                    'verify'
                ]

            )->name(

                'payments.verify'

            );


            /*
            |--------------------------------------------------------------------------
            | REJECT PAYMENT
            |--------------------------------------------------------------------------
            */

            Route::post(

                'payments/{payment}/reject',

                [
                    PaymentController::class,
                    'reject'
                ]

            )->name(

                'payments.reject'

            );


            /*
            |--------------------------------------------------------------------------
            | PAYMENT METHODS
            |--------------------------------------------------------------------------
            */

            Route::get(

                'payment-methods',

                [
                    PaymentController::class,
                    'methods'
                ]

            )->name(

                'payment-methods.index'

            );


            Route::post(

                'payment-methods',

                [
                    PaymentController::class,
                    'storeMethod'
                ]

            )->name(

                'payment-methods.store'

            );


            Route::put(

                'payment-methods/{paymentMethod}',

                [
                    PaymentController::class,
                    'updateMethod'
                ]

            )->name(

                'payment-methods.update'

            );


            Route::delete(

                'payment-methods/{paymentMethod}',

                [
                    PaymentController::class,
                    'destroyMethod'
                ]

            )->name(

                'payment-methods.destroy'

            );


            /*
            |--------------------------------------------------------------------------
            | STORE SETTINGS
            |--------------------------------------------------------------------------
            */

            Route::get(

                'settings',

                [
                    SettingController::class,
                    'index'
                ]

            )->name(

                'settings.index'

            );


            Route::put(

                'settings',

                [
                    SettingController::class,
                    'update'
                ]

            )->name(

                'settings.update'

            );


            /*
            |--------------------------------------------------------------------------
            | SALES RECAP (REKAP PENJUALAN & LAPORAN KEUANGAN)
            |--------------------------------------------------------------------------
            */

            Route::get(

                'sales-recap',

                [
                    SalesRecapController::class,
                    'index'
                ]

            )->name(

                'sales-recap.index'

            );


            Route::get(

                'sales-recap/export',

                [
                    SalesRecapController::class,
                    'exportExcel'
                ]

            )->name(

                'sales-recap.export'

            );


            Route::post(

                'sales-recap/offline',

                [
                    SalesRecapController::class,
                    'storeOffline'
                ]

            )->name(

                'sales-recap.offline'

            );


            Route::post(

                'sales-recap/close',

                [
                    SalesRecapController::class,
                    'close'
                ]

            )->name(

                'sales-recap.close'

            );


            /*
            |--------------------------------------------------------------------------
            | HOME SLIDERS
            |--------------------------------------------------------------------------
            */

            Route::resource(

                'sliders',

                HomeSliderController::class

            )->except(

                'show'

            );


            /*
            |--------------------------------------------------------------------------
            | DISCOUNTS
            |--------------------------------------------------------------------------
            */

            Route::get(

                'discounts',

                [
                    DiscountController::class,
                    'index'
                ]

            )->name(

                'discounts.index'

            );


            Route::post(

                'discounts',

                [
                    DiscountController::class,
                    'store'
                ]

            )->name(

                'discounts.store'

            );


            Route::put(

                'discounts/{discount}',

                [
                    DiscountController::class,
                    'update'
                ]

            )->name(

                'discounts.update'

            );


            Route::delete(

                'discounts/{discount}',

                [
                    DiscountController::class,
                    'destroy'
                ]

            )->name(

                'discounts.destroy'

            );


            Route::get(

                'returns',

                [
                    ReturnManagementController::class,
                    'index'
                ]

            )->name(

                'returns.index'

            );


            Route::post(

                'returns/{order}/approve',

                [
                    ReturnManagementController::class,
                    'approve'
                ]

            )->name(

                'returns.approve'

            );


            Route::post(

                'returns/{order}/reject',

                [
                    ReturnManagementController::class,
                    'reject'
                ]

            )->name(

                'returns.reject'

            );


            Route::post(

                'returns/{order}/complete',

                [
                    ReturnManagementController::class,
                    'complete'
                ]

            )->name(
    'returns.complete'
);

Route::delete(
    'returns/{order}/delete',
    [
        ReturnManagementController::class,
        'destroy'
    ]
)->name(
    'returns.destroy'
);



        }

    );


/*
|--------------------------------------------------------------------------
| END OF WEB ROUTES
|--------------------------------------------------------------------------
|
| PUBLIC
| - Homepage
| - Shop
| - Category
| - Category Detail
| - Product Detail
| - Profile
| - Login
| - Register
| - Google Login
| - Shipping Destination (public, throttled)
| - Calculate Shipping (public, throttled)
|
| PASSWORD RESET
| - Forgot Password
| - Send OTP
| - Verify OTP
| - Reset Password
|
| LOGIN REQUIRED
| - Cart
| - Add Cart
| - Update Cart
| - Delete Cart
| - Checkout
| - Create Order
| - Payment
| - Upload Payment Proof
| - Receipt
| - Download Receipt
| - Confirm Delivery
|
| RAJAONGKIR TEST
| - Search Gresik
| - Search Destination
| - Calculate Cost
| - Tracking AWB
|
| ADMIN REQUIRED
| - Dashboard
| - Products
| - Categories
| - Orders
| - Delete Order History
| - Shipping Progress
| - Receipt
| - Shipping Label
| - Payments
| - Payment Methods
| - Settings
| - Home Sliders
| - Discounts
|
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| FAVORITE ROUTES
|--------------------------------------------------------------------------
*/


Route::get('/favorit', [FavoriteController::class, 'index'])
    ->middleware('zalina.login')
    ->name('favorites.index');

Route::post('/favorit/{product}/toggle', [FavoriteController::class, 'toggle'])
    ->middleware('zalina.login')
    ->name('favorites.toggle');