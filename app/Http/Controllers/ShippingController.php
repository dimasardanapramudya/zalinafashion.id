<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ShippingController extends Controller
{
    /**
     * ============================================================
     * RAJAONGKIR / KOMERCE SHIPPING CONTROLLER
     * ============================================================
     *
     * Menangani:
     *
     * 1. Pencarian destination/wilayah Indonesia
     * 2. Perhitungan ongkos kirim
     *
     * Endpoint RajaOngkir:
     *
     * GET  /destination/domestic-destination
     * POST /calculate/domestic-cost
     *
     * Credential diambil dari:
     *
     * config/services.php
     *
     * API Key tetap berada di .env.
     */


    /**
     * ============================================================
     * BASE URL RAJAONGKIR
     * ============================================================
     */
    protected function getBaseUrl(): string
    {
        return rtrim(
            (string) config(
                'services.rajaongkir.base_url',
                'https://rajaongkir.komerce.id/api/v1'
            ),
            '/'
        );
    }


    /**
     * ============================================================
     * API KEY
     * ============================================================
     */
    protected function getApiKey(): string
    {
        return trim(
            (string) config(
                'services.rajaongkir.api_key',
                ''
            )
        );
    }


    /**
     * ============================================================
     * ORIGIN ID
     * ============================================================
     *
     * Menggunakan:
     *
     * RAJAONGKIR_ORIGIN_ID
     *
     * yang berada di config/services.php.
     */
    protected function getOrigin(): int
    {
        return (int) config(
            'services.rajaongkir.origin_id',
            0
        );
    }


    /**
     * ============================================================
     * REQUEST TIMEOUT
     * ============================================================
     */
    protected function getTimeout(): int
    {
        $timeout = (int) config(
            'services.rajaongkir.timeout',
            30
        );

        return max(
            $timeout,
            5
        );
    }


    /**
     * ============================================================
     * DESTINATION LIMIT
     * ============================================================
     */
    protected function getDestinationLimit(): int
    {
        $limit = (int) config(
            'services.rajaongkir.destination_limit',
            10
        );

        return max(
            min($limit, 100),
            1
        );
    }


    /**
     * ============================================================
     * DESTINATION OFFSET
     * ============================================================
     */
    protected function getDestinationOffset(): int
    {
        $offset = (int) config(
            'services.rajaongkir.destination_offset',
            0
        );

        return max(
            $offset,
            0
        );
    }


    /**
     * ============================================================
     * HTTP HEADERS
     * ============================================================
     */
    protected function getHeaders(): array
    {
        return [
            'key' => $this->getApiKey(),
            'Accept' => 'application/json',
        ];
    }


    /**
     * ============================================================
     * SEARCH DESTINATION
     * ============================================================
     *
     * Route:
     *
     * GET /shipping/destinations?search=Gresik
     *
     * RajaOngkir:
     *
     * GET /destination/domestic-destination
     *
     * Parameter:
     *
     * search
     * limit
     * offset
     */
    public function destinations(Request $request): JsonResponse
    {
        try {
            /*
             * Ambil dan bersihkan keyword pencarian.
             */
            $search = trim(
                (string) $request->input('search', '')
            );

            /*
             * Keyword kosong atau terlalu pendek dianggap
             * sebagai hasil kosong, bukan error server.
             */
            if ($search === '') {
                return response()->json([
                    'success' => true,
                    'message' => 'Silakan masukkan nama wilayah.',
                    'data' => [],
                ], 200);
            }

            if (mb_strlen($search) < 2) {
                return response()->json([
                    'success' => true,
                    'message' => 'Masukkan minimal 2 karakter.',
                    'data' => [],
                ], 200);
            }

            /*
             * Batasi panjang keyword.
             */
            if (mb_strlen($search) > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keyword pencarian terlalu panjang.',
                    'data' => [],
                ], 422);
            }

            /*
             * Periksa API key.
             */
            $apiKey = $this->getApiKey();

            if ($apiKey === '') {
                Log::error(
                    'RajaOngkir API key is empty.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'RAJAONGKIR_API_KEY belum dikonfigurasi.',
                    'data' => [],
                ], 500);
            }

            /*
             * Periksa base URL.
             */
            $baseUrl = $this->getBaseUrl();

            if ($baseUrl === '') {
                Log::error(
                    'RajaOngkir base URL is empty.'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'RAJAONGKIR_BASE_URL belum dikonfigurasi.',
                    'data' => [],
                ], 500);
            }

            /*
             * Endpoint RajaOngkir.
             */
            $url = $baseUrl . '/destination/domestic-destination';

            /*
             * Parameter API.
             */
            $parameters = [
                'search' => $search,
                'limit' => $this->getDestinationLimit(),
                'offset' => $this->getDestinationOffset(),
            ];

            /*
             * Request ke RajaOngkir.
             */
            $response = Http::timeout(
                $this->getTimeout()
            )
                ->withHeaders(
                    $this->getHeaders()
                )
                ->acceptJson()
                ->get(
                    $url,
                    $parameters
                );

            $rawResponse = $response->body();
            $json = $response->json();

            /*
             * ========================================================
             * PENANGANAN RESPONSE ERROR DARI RAJAONGKIR
             * ========================================================
             */
            if (!$response->successful()) {
                $httpStatus = (int) $response->status();

                $message = trim(
                    (string) (
                        data_get($json, 'meta.message')
                        ??
                        data_get($json, 'message')
                        ??
                        'Gagal mencari wilayah tujuan.'
                    )
                );

                Log::warning(
                    'RajaOngkir destination search returned non-success status.',
                    [
                        'http_status' => $httpStatus,
                        'search' => $search,
                        'limit' => $parameters['limit'],
                        'offset' => $parameters['offset'],
                        'url' => $url,
                        'response' => $rawResponse,
                    ]
                );

                /*
                 * RajaOngkir mengembalikan 404 jika wilayah tidak
                 * ditemukan. Kondisi ini bukan error aplikasi.
                 *
                 * Kembalikan 200 agar frontend menerima JSON normal
                 * dengan data kosong.
                 */
                if ($httpStatus === 404) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Wilayah tidak ditemukan.',
                        'data' => [],
                    ], 200);
                }

                /*
                 * Status error lainnya tetap dikembalikan sebagai
                 * JSON, bukan halaman HTML Laravel.
                 */
                $safeStatus = (
                    $httpStatus >= 400 &&
                    $httpStatus <= 599
                )
                    ? $httpStatus
                    : 502;

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'data' => [],
                    'http_status' => $httpStatus,
                ], $safeStatus);
            }

            /*
             * ========================================================
             * VALIDASI RESPONSE JSON
             * ========================================================
             */
            if (!is_array($json)) {
                Log::error(
                    'RajaOngkir destination returned invalid JSON.',
                    [
                        'search' => $search,
                        'url' => $url,
                        'response' => $rawResponse,
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Server pengiriman tidak mengembalikan JSON yang valid.',
                    'data' => [],
                ], 502);
            }

            /*
             * Ambil data dari response RajaOngkir.
             */
            $destinations = data_get(
                $json,
                'data',
                []
            );

            if (!is_array($destinations)) {
                $destinations = [];
            }

            /*
             * ========================================================
             * NORMALISASI DATA DESTINATION
             * ========================================================
             */
            $normalized = [];

            foreach ($destinations as $destination) {
                if (!is_array($destination)) {
                    continue;
                }

                $id = (int) (
                    $destination['id']
                    ??
                    0
                );

                /*
                 * Destination tanpa ID tidak dapat digunakan
                 * untuk perhitungan ongkos kirim.
                 */
                if ($id <= 0) {
                    continue;
                }

                $label = trim(
                    (string) (
                        $destination['label']
                        ??
                        ''
                    )
                );

                $provinceName = trim(
                    (string) (
                        $destination['province_name']
                        ??
                        ''
                    )
                );

                $cityName = trim(
                    (string) (
                        $destination['city_name']
                        ??
                        ''
                    )
                );

                $districtName = trim(
                    (string) (
                        $destination['district_name']
                        ??
                        ''
                    )
                );

                $subdistrictName = trim(
                    (string) (
                        $destination['subdistrict_name']
                        ??
                        ''
                    )
                );

                $zipCode = trim(
                    (string) (
                        $destination['zip_code']
                        ??
                        ''
                    )
                );

                /*
                 * Buat label cadangan apabila API tidak mengirimkan
                 * field label.
                 */
                if ($label === '') {
                    $label = implode(', ', array_filter([
                        $subdistrictName,
                        $districtName,
                        $cityName,
                        $provinceName,
                        $zipCode,
                    ]));
                }

                $normalized[] = [
                    'id' => $id,
                    'label' => $label,
                    'province_name' => $provinceName,
                    'city_name' => $cityName,
                    'district_name' => $districtName,
                    'subdistrict_name' => $subdistrictName,
                    'zip_code' => $zipCode,
                ];
            }

            /*
             * ========================================================
             * RESPONSE BERHASIL
             * ========================================================
             */
            return response()->json([
                'success' => true,
                'message' => empty($normalized)
                    ? 'Wilayah tidak ditemukan.'
                    : 'Wilayah berhasil ditemukan.',
                'data' => $normalized,
            ], 200);

        } catch (Throwable $exception) {
            /*
             * Semua exception ditangani agar frontend selalu
             * menerima JSON.
             */
            Log::error(
                'Shipping destination search exception.',
                [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari wilayah.',
                'data' => [],
                'debug' => app()->environment('local')
                    ? $exception->getMessage()
                    : null,
            ], 500);
        }
    }


    /**
     * ============================================================
     * CALCULATE SHIPPING COST
     * ============================================================
     *
     * Route:
     *
     * POST /shipping/calculate
     *
     * Request:
     *
     * destination_id
     * weight
     *
     * RajaOngkir:
     *
     * POST /calculate/domestic-cost
     */
    public function calculate(Request $request): JsonResponse
    {
        try {

            /**
             * ====================================================
             * VALIDASI REQUEST
             * ====================================================
             */
            $validated = $request->validate([
                'destination_id' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'weight' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:500000',
                ],
            ]);


            /**
             * ====================================================
             * PARAMETER
             * ====================================================
             */
            $origin =
                $this->getOrigin();


            $destination =
                (int) $validated[
                    'destination_id'
                ];


            $weight =
                (int) $validated[
                    'weight'
                ];


            $weight =
                max(
                    $weight,
                    1
                );


            /**
             * ====================================================
             * API KEY
             * ====================================================
             */
            $apiKey =
                $this->getApiKey();


            if ($apiKey === '') {

                Log::error(
                    'RajaOngkir API key is empty during shipping calculation.'
                );

                return response()->json([
                    'success' => false,

                    'message' =>
                        'RAJAONGKIR_API_KEY belum dikonfigurasi.',

                    'data' =>
                        [],
                ], 500);
            }


            /**
             * ====================================================
             * ORIGIN VALIDATION
             * ====================================================
             */
            if ($origin <= 0) {

                Log::error(
                    'RajaOngkir origin ID is invalid.',
                    [
                        'origin' =>
                            $origin,
                    ]
                );

                return response()->json([
                    'success' => false,

                    'message' =>
                        'RAJAONGKIR_ORIGIN_ID belum dikonfigurasi dengan benar.',

                    'data' =>
                        [],
                ], 500);
            }


            /**
             * ====================================================
             * DESTINATION VALIDATION
             * ====================================================
             */
            if ($destination <= 0) {

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Wilayah tujuan belum dipilih.',

                    'data' =>
                        [],
                ], 422);
            }


            /**
             * ====================================================
             * COURIER
             * ====================================================
             *
             * Untuk menjaga kompatibilitas dengan implementasi
             * checkout yang sedang digunakan, tahap ini memakai JNE.
             */
            $couriers = [
                'jne',
            ];


            /**
             * ====================================================
             * HASIL
             * ====================================================
             */
            $shippingResults = [];

            /*
            |--------------------------------------------------------------------------
            | BUG FIX: SATU KURIR GAGAL TIDAK BOLEH MEMBATALKAN KURIR LAIN
            |--------------------------------------------------------------------------
            |
            | Sebelumnya begitu satu kurir gagal (HTTP error / JSON tidak valid),
            | method langsung `return` sehingga membatalkan seluruh proses —
            | termasuk kurir lain yang belum sempat dicoba. Karena struktur
            | kode ini memang dirancang untuk banyak kurir (foreach $couriers),
            | kegagalan satu kurir sekarang hanya di-skip, bukan menggagalkan
            | semuanya. Pesan error kurir yang gagal disimpan di sini supaya
            | tetap bisa ditampilkan kalau ternyata SEMUA kurir gagal.
            |
            */
            $courierErrors = [];


            /**
             * ====================================================
             * LOOP COURIER
             * ====================================================
             */
            foreach (
                $couriers
                as $courier
            ) {

                /**
                 * Endpoint ongkos kirim.
                 */
                $url =
                    $this->getBaseUrl()
                    .
                    '/calculate/domestic-cost';


                /**
                 * Parameter form.
                 */
                $parameters = [

                    'origin' =>
                        $origin,

                    'destination' =>
                        $destination,

                    'weight' =>
                        $weight,

                    'courier' =>
                        $courier,
                ];


                /**
                 * =================================================
                 * REQUEST
                 * =================================================
                 */
                $response = Http::timeout(
                    $this->getTimeout()
                )
                    ->withHeaders(
                        $this->getHeaders()
                    )
                    ->asForm()
                    ->post(
                        $url,
                        $parameters
                    );


                /**
                 * =================================================
                 * RAW RESPONSE
                 * =================================================
                 */
                $rawResponse =
                    $response->body();


                /**
                 * =================================================
                 * JSON
                 * =================================================
                 */
                $json =
                    $response->json();


                /**
                 * =================================================
                 * HTTP ERROR
                 * =================================================
                 */
                if (!$response->successful()) {

                    Log::error(
                        'RajaOngkir shipping calculation failed',
                        [
                            'http_status' =>
                                $response->status(),

                            'url' =>
                                $url,

                            'origin' =>
                                $origin,

                            'destination' =>
                                $destination,

                            'weight' =>
                                $weight,

                            'courier' =>
                                $courier,

                            'response' =>
                                $rawResponse,
                        ]
                    );


                    $message =
                        data_get(
                            $json,
                            'meta.message'
                        )
                        ??
                        data_get(
                            $json,
                            'message'
                        )
                        ??
                        'Gagal menghitung ongkos kirim.';


                    $courierErrors[$courier] = [
                        'message' => $message,
                        'http_status' => $response->status(),
                    ];

                    // Lewati kurir ini, lanjut ke kurir berikutnya — jangan
                    // batalkan seluruh proses hanya karena satu kurir gagal.
                    continue;
                }


                /**
                 * =================================================
                 * VALIDASI JSON
                 * =================================================
                 */
                if (!is_array($json)) {

                    Log::error(
                        'RajaOngkir shipping returned invalid JSON',
                        [
                            'url' =>
                                $url,

                            'origin' =>
                                $origin,

                            'destination' =>
                                $destination,

                            'weight' =>
                                $weight,

                            'courier' =>
                                $courier,

                            'response' =>
                                $rawResponse,
                        ]
                    );


                    $courierErrors[$courier] = [
                        'message' => 'Server pengiriman tidak mengembalikan JSON yang valid.',
                        'http_status' => 502,
                    ];

                    // Lewati kurir ini, lanjut ke kurir berikutnya.
                    continue;
                }


                /**
                 * =================================================
                 * DATA SERVICE
                 * =================================================
                 */
                $services =
                    data_get(
                        $json,
                        'data',
                        []
                    );


                if (!is_array($services)) {

                    $services = [];
                }


                /**
                 * =================================================
                 * NORMALISASI SERVICE
                 * =================================================
                 */
                foreach (
                    $services
                    as $service
                ) {

                    if (
                        !is_array($service)
                    ) {
                        continue;
                    }


                    /**
                     * ------------------------------------------------
                     * COURIER CODE
                     * ------------------------------------------------
                     */
                    $serviceCourier =
                        strtolower(
                            trim(
                                (string) (
                                    $service['code']
                                    ??
                                    $courier
                                )
                            )
                        );


                    /**
                     * ------------------------------------------------
                     * COURIER NAME
                     * ------------------------------------------------
                     */
                    $courierName =
                        trim(
                            (string) (
                                $service['name']
                                ??
                                strtoupper($courier)
                            )
                        );


                    /**
                     * ------------------------------------------------
                     * SERVICE CODE
                     * ------------------------------------------------
                     */
                    $serviceCode =
                        trim(
                            (string) (
                                $service['service']
                                ??
                                $service['service_name']
                                ??
                                'REG'
                            )
                        );


                    /**
                     * ------------------------------------------------
                     * DESCRIPTION
                     * ------------------------------------------------
                     */
                    $description =
                        trim(
                            (string) (
                                $service['description']
                                ??
                                ''
                            )
                        );


                    /**
                     * ------------------------------------------------
                     * ETD
                     * ------------------------------------------------
                     */
                    $etd =
                        trim(
                            (string) (
                                $service['etd']
                                ??
                                '-'
                            )
                        );


                    /**
                     * ------------------------------------------------
                     * COST
                     * ------------------------------------------------
                     */
                    $cost =
                        (int) (
                            $service['cost']
                            ??
                            $service['price']
                            ??
                            0
                        );


                    /**
                     * Jangan masukkan biaya negatif.
                     */
                    if ($cost < 0) {

                        continue;
                    }


                    /**
                     * =================================================
                     * CHECKOUT RESPONSE
                     * =================================================
                     */
                    $shippingResults[] = [

                        'name' =>
                            $courierName,

                        'courier' =>
                            $serviceCourier,

                        'courier_name' =>
                            $courierName,

                        'code' =>
                            $serviceCourier,

                        'service' =>
                            $serviceCode,

                        'service_name' =>
                            $serviceCode,

                        'description' =>
                            $description,

                        'cost' =>
                            $cost,

                        'price' =>
                            $cost,

                        'etd' =>
                            $etd,

                        'weight' =>
                            $weight,
                    ];
                }
            }


            /**
             * ====================================================
             * TIDAK ADA SERVICE
             * ====================================================
             */
            if (
                empty(
                    $shippingResults
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | SEMUA KURIR GAGAL vs MEMANG TIDAK ADA LAYANAN
                |--------------------------------------------------------------------------
                |
                | Sebelumnya kondisi "semua kurir error" dan "memang tidak ada
                | layanan ke wilayah ini" dikirim dengan pesan yang sama persis,
                | sehingga error asli (API key salah, RajaOngkir down, dll)
                | tersembunyi dan terlihat seperti kondisi normal.
                |
                */
                if (!empty($courierErrors)) {

                    Log::error(
                        'Semua kurir gagal saat menghitung ongkos kirim.',
                        [
                            'origin' => $origin,
                            'destination' => $destination,
                            'weight' => $weight,
                            'errors' => $courierErrors,
                        ]
                    );

                    $firstError = reset($courierErrors);

                    return response()->json([
                        'success' => false,

                        'message' =>
                            $firstError['message'] ?? 'Gagal menghitung ongkos kirim.',

                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,

                        'data' => [],
                    ], $firstError['http_status'] ?? 502);
                }

                return response()->json([
                    'success' => true,

                    'message' =>
                        'Tidak ada layanan pengiriman yang tersedia untuk wilayah ini.',

                    'origin' =>
                        $origin,

                    'destination' =>
                        $destination,

                    'weight' =>
                        $weight,

                    'data' =>
                        [],
                ], 200);
            }


            /**
             * ====================================================
             * SORT HARGA
             * ====================================================
             */
            usort(
                $shippingResults,
                function (
                    array $a,
                    array $b
                ): int {

                    return
                        $a['cost']
                        <=>
                        $b['cost'];
                }
            );


            /**
             * ====================================================
             * RESPONSE SUCCESS
             * ====================================================
             */
            return response()->json([
                'success' => true,

                'message' =>
                    'Ongkos kirim berhasil dihitung.',

                'origin' =>
                    $origin,

                'destination' =>
                    $destination,

                'weight' =>
                    $weight,

                'data' =>
                    $shippingResults,

            ], 200);


        } catch (
            ValidationException $exception
        ) {

            /**
             * ====================================================
             * VALIDATION ERROR
             * ====================================================
             */
            return response()->json([
                'success' => false,

                'message' =>
                    'Data pengiriman tidak valid.',

                'errors' =>
                    $exception->errors(),

                'data' =>
                    [],
            ], 422);


        } catch (
            Throwable $exception
        ) {

            /**
             * ====================================================
             * UNEXPECTED ERROR
             * ====================================================
             */
            Log::error(
                'Shipping calculation exception',
                [
                    'message' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),
                ]
            );


            return response()->json([
                'success' => false,

                'message' =>
                    'Terjadi kesalahan saat menghitung ongkos kirim.',

                'data' =>
                    [],

                'debug' =>
                    app()->environment('local')
                        ? [
                            'message' =>
                                $exception->getMessage(),

                            'file' =>
                                $exception->getFile(),

                            'line' =>
                                $exception->getLine(),
                        ]
                        : null,

            ], 500);
        }
    }
}