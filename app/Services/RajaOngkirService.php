<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class RajaOngkirService
{
    /**
     * Base URL API.
     */
    protected string $baseUrl;

    /**
     * API Key.
     */
    protected string $apiKey;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.rajaongkir.base_url'),
            '/'
        );

        $this->apiKey = config(
            'services.rajaongkir.api_key'
        );

        if (empty($this->baseUrl)) {
            throw new RuntimeException(
                'RAJAONGKIR_BASE_URL belum dikonfigurasi.'
            );
        }

        if (empty($this->apiKey)) {
            throw new RuntimeException(
                'RAJAONGKIR_API_KEY belum dikonfigurasi.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    protected function client()
    {
        return Http::withHeaders([

            'key' => $this->apiKey,

            'Accept' => 'application/json',

        ])
            ->timeout(20)
            ->connectTimeout(10);
    }


    /*
    |--------------------------------------------------------------------------
    | Search Domestic Destination
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | searchDestination('Gresik')
    |
    */

    public function searchDestination(
        string $search,
        int $limit = 10,
        int $offset = 0
    ): array {

        $search = trim($search);

        if ($search === '') {
            return [];
        }


        $cacheKey =
            'rajaongkir_destination_' .
            md5(
                Str::lower(
                    $search .
                    '_' .
                    $limit .
                    '_' .
                    $offset
                )
            );


        $minutes = (int) config(
            'services.rajaongkir.destination_cache_minutes',
            60
        );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes($minutes),
            function () use (
                $search,
                $limit,
                $offset
            ) {

                $response =
                    $this->client()
                        ->get(
                            $this->baseUrl .
                            '/destination/domestic-destination',
                            [

                                'search' =>
                                    $search,

                                'limit' =>
                                    $limit,

                                'offset' =>
                                    $offset,

                            ]
                        );


                if (!$response->successful()) {

                    throw new RuntimeException(
                        $this->getErrorMessage(
                            $response->json(),
                            'Gagal mengambil data tujuan pengiriman.'
                        )
                    );
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

                    throw new RuntimeException(
                        $this->getErrorMessage(
                            $json,
                            'Pencarian wilayah pengiriman gagal.'
                        )
                    );
                }


                return
                    $json['data'] ??
                    [];

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Destination By ID
    |--------------------------------------------------------------------------
    */

    public function getDestination(
        int|string $destinationId
    ): ?array {

        $results =
            $this->searchDestination(
                (string) $destinationId,
                20,
                0
            );


        foreach ($results as $destination) {

            if (
                (string) (
                    $destination['id'] ??
                    ''
                )
                ===
                (string) $destinationId
            ) {

                return $destination;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Shipping Cost
    |--------------------------------------------------------------------------
    |
    | RajaOngkir Komerce:
    | POST /calculate/domestic-cost
    |
    */

    public function calculateDomesticCost(
        int|string $origin,
        int|string $destination,
        int $weight,
        string|array $courier
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Normalize Weight
        |--------------------------------------------------------------------------
        */

        $minimumWeight =
            (int) config(
                'services.rajaongkir.minimum_weight',
                100
            );


        $weight =
            max(
                $weight,
                $minimumWeight
            );


        /*
        |--------------------------------------------------------------------------
        | Normalize Courier
        |--------------------------------------------------------------------------
        */

        if (
            is_array($courier)
        ) {

            $courier =
                implode(
                    ':',
                    $courier
                );
        }


        $cacheKey =
            'rajaongkir_cost_' .
            md5(
                implode(
                    '|',
                    [

                        $origin,
                        $destination,
                        $weight,
                        $courier,

                    ]
                )
            );


        $minutes =
            (int) config(
                'services.rajaongkir.cost_cache_minutes',
                15
            );


        return Cache::remember(
            $cacheKey,
            now()->addMinutes($minutes),
            function () use (
                $origin,
                $destination,
                $weight,
                $courier
            ) {

                $response =
                    $this->client()
                        ->post(
                            $this->baseUrl .
                            '/calculate/domestic-cost',
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


                if (
                    !$response->successful()
                ) {

                    throw new RuntimeException(
                        $this->getErrorMessage(
                            $response->json(),
                            'Gagal menghitung biaya pengiriman.'
                        )
                    );
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

                    throw new RuntimeException(
                        $this->getErrorMessage(
                            $json,
                            'Perhitungan ongkir gagal.'
                        )
                    );
                }


                return
                    $json['data'] ??
                    [];

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Test API
    |--------------------------------------------------------------------------
    */

    public function testConnection(): array
    {
        return
            $this->searchDestination(
                'Gresik',
                1,
                0
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Error Message
    |--------------------------------------------------------------------------
    */

    protected function getErrorMessage(
        ?array $json,
        string $fallback
    ): string {

        if (
            !is_array(
                $json
            )
        ) {

            return $fallback;
        }


        return
            $json['meta']['message']
            ??
            $json['message']
            ??
            $fallback;
    }
}