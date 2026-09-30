<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    /**
     * Base URL RajaOngkir Komerce.
     */
    protected string $baseUrl;

    /**
     * API Key RajaOngkir.
     */
    protected string $apiKey;

    /**
     * Origin ID toko.
     */
    protected ?string $originId;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config(
                'services.rajaongkir.base_url'
            ),
            '/'
        );

        $this->apiKey = (string) config(
            'services.rajaongkir.api_key'
        );

        $this->originId = config(
            'services.rajaongkir.origin_id'
        );
    }


    /**
     * HTTP Client RajaOngkir.
     */
    protected function client()
    {
        return Http::withHeaders([
            'key' => $this->apiKey,
            'Accept' => 'application/json',
        ])
            ->timeout(30)
            ->retry(2, 500);
    }


    /**
     * Cari destination domestik.
     */
    public function destinations(
        string $search,
        int $limit = 20,
        int $offset = 0
    ): array {

        $search = trim($search);

        if (empty($search)) {
            return [];
        }


        $cacheMinutes = (int) config(
            'services.rajaongkir.destination_cache_minutes',
            60
        );


        $cacheKey =
            'rajaongkir_destination_' .
            md5(
                strtolower($search) .
                '_' .
                $limit .
                '_' .
                $offset
            );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes(
                $cacheMinutes
            ),
            function () use (
                $search,
                $limit,
                $offset
            ) {

                try {

                    $response = $this->client()
                        ->get(
                            $this->baseUrl .
                            '/destination/domestic-destination',
                            [
                                'search' => $search,
                                'limit' => $limit,
                                'offset' => $offset,
                            ]
                        );


                    if (!$response->successful()) {

                        Log::error(
                            'RajaOngkir destination request failed',
                            [
                                'status' =>
                                    $response->status(),

                                'body' =>
                                    $response->body(),
                            ]
                        );

                        return [];
                    }


                    $json =
                        $response->json();


                    if (
                        !isset(
                            $json['meta']['status']
                        ) ||
                        $json['meta']['status'] !==
                        'success'
                    ) {

                        Log::warning(
                            'RajaOngkir destination API failed',
                            [
                                'response' =>
                                    $json,
                            ]
                        );

                        return [];
                    }


                    return
                        $json['data']
                        ?? [];

                } catch (
                    \Throwable $e
                ) {

                    Log::error(
                        'RajaOngkir destination exception',
                        [
                            'message' =>
                                $e->getMessage(),
                        ]
                    );

                    return [];
                }
            }
        );
    }


    /**
     * Hitung ongkir.
     *
     * Endpoint cost Komerce.
     */
    public function calculate(
        int|string $destinationId,
        int $weight,
        ?string $courier = null
    ): array {

        if (
            empty(
                $this->originId
            )
        ) {

            return [
                'success' => false,

                'message' =>
                    'Origin RajaOngkir belum dikonfigurasi.',

                'data' => [],
            ];
        }


        if (
            empty(
                $destinationId
            )
        ) {

            return [
                'success' => false,

                'message' =>
                    'Destination belum dipilih.',

                'data' => [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Minimum Weight
        |--------------------------------------------------------------------------
        */

        $minimumWeight = (int) config(
            'services.rajaongkir.minimum_weight',
            100
        );


        $weight = max(
            $weight,
            $minimumWeight
        );


        /*
        |--------------------------------------------------------------------------
        | Couriers
        |--------------------------------------------------------------------------
        */

        if (!$courier) {

            $courier = (string) config(
                'services.rajaongkir.couriers',
                'jne,jnt,sicepat'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cache
        |--------------------------------------------------------------------------
        */

        $cacheMinutes = (int) config(
            'services.rajaongkir.cost_cache_minutes',
            15
        );


        $cacheKey =
            'rajaongkir_cost_' .
            md5(
                $this->originId .
                '_' .
                $destinationId .
                '_' .
                $weight .
                '_' .
                $courier
            );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes(
                $cacheMinutes
            ),
            function () use (
                $destinationId,
                $weight,
                $courier
            ) {

                try {

                    $response =
                        $this->client()
                        ->post(
                            $this->baseUrl .
                            '/calculate/domestic-cost',
                            [
                                'origin' =>
                                    (int) $this->originId,

                                'destination' =>
                                    (int) $destinationId,

                                'weight' =>
                                    $weight,

                                'courier' =>
                                    $courier,
                            ]
                        );


                    $json =
                        $response->json();


                    if (
                        !$response->successful()
                    ) {

                        Log::error(
                            'RajaOngkir cost request failed',
                            [
                                'status' =>
                                    $response->status(),

                                'response' =>
                                    $json,
                            ]
                        );


                        return [
                            'success' =>
                                false,

                            'message' =>
                                $json['meta']['message']
                                ?? 'Gagal menghitung ongkir.',

                            'data' =>
                                [],
                        ];
                    }


                    if (
                        !isset(
                            $json['meta']['status']
                        ) ||
                        $json['meta']['status'] !==
                        'success'
                    ) {

                        return [
                            'success' =>
                                false,

                            'message' =>
                                $json['meta']['message']
                                ?? 'RajaOngkir tidak dapat menghitung ongkir.',

                            'data' =>
                                [],
                        ];
                    }


                    return [
                        'success' =>
                            true,

                        'message' =>
                            $json['meta']['message']
                            ?? 'Ongkir berhasil dihitung.',

                        'data' =>
                            $json['data']
                            ?? [],
                    ];

                } catch (
                    \Throwable $e
                ) {

                    Log::error(
                        'RajaOngkir cost exception',
                        [
                            'message' =>
                                $e->getMessage(),

                            'origin' =>
                                $this->originId,

                            'destination' =>
                                $destinationId,

                            'weight' =>
                                $weight,
                        ]
                    );


                    return [
                        'success' =>
                            false,

                        'message' =>
                            'Terjadi kesalahan saat menghubungi layanan pengiriman.',

                        'data' =>
                            [],
                    ];
                }
            }
        );
    }


    /**
     * Ambil konfigurasi origin.
     */
    public function origin(): array
    {
        return [

            'id' =>
                $this->originId,

            'name' =>
                config(
                    'services.rajaongkir.origin_name'
                ),

            'city' =>
                config(
                    'services.rajaongkir.origin_city'
                ),

            'province' =>
                config(
                    'services.rajaongkir.origin_province'
                ),

        ];
    }
}