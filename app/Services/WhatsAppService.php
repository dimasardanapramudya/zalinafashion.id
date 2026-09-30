<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    protected string $baseUrl;

    protected string $apiVersion;

    protected string $phoneNumberId;

    protected string $accessToken;

    protected int $timeout;

    protected int $connectTimeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config(
                'whatsapp.base_url',
                'https://graph.facebook.com'
            ),
            '/'
        );

        $this->apiVersion = trim(
            (string) config(
                'whatsapp.api_version',
                'v25.0'
            )
        );

        $this->phoneNumberId = trim(
            (string) config(
                'whatsapp.phone_number_id'
            )
        );

        $this->accessToken = trim(
            (string) config(
                'whatsapp.access_token'
            )
        );

        $this->timeout = (int) config(
            'whatsapp.timeout',
            30
        );

        $this->connectTimeout = (int) config(
            'whatsapp.connect_timeout',
            10
        );
    }

    /**
     * Menghasilkan URL endpoint WhatsApp Cloud API.
     */
    protected function endpoint(): string
    {
        return "{$this->baseUrl}/{$this->apiVersion}/{$this->phoneNumberId}/messages";
    }

    /**
     * Memastikan konfigurasi WhatsApp sudah lengkap.
     */
    protected function validateConfiguration(): void
    {
        if ($this->phoneNumberId === '') {
            throw new RuntimeException(
                'Konfigurasi whatsapp.phone_number_id belum diisi.'
            );
        }

        if ($this->accessToken === '') {
            throw new RuntimeException(
                'Konfigurasi whatsapp.access_token belum diisi.'
            );
        }

        if ($this->apiVersion === '') {
            throw new RuntimeException(
                'Konfigurasi whatsapp.api_version belum diisi.'
            );
        }
    }

    /**
     * Membersihkan dan menormalisasi nomor WhatsApp.
     *
     * Contoh:
     * 0895322389911 menjadi 62895322389911
     * +62895322389911 menjadi 62895322389911
     */
    protected function normalizePhoneNumber(
        string $phoneNumber
    ): string {
        $phoneNumber = preg_replace(
            '/[^0-9]/',
            '',
            trim($phoneNumber)
        );

        if (
            !is_string($phoneNumber) ||
            $phoneNumber === ''
        ) {
            throw new RuntimeException(
                'Nomor WhatsApp penerima tidak valid.'
            );
        }

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '62' . substr(
                $phoneNumber,
                1
            );
        }

        if (
            !str_starts_with($phoneNumber, '62') ||
            strlen($phoneNumber) < 10
        ) {
            throw new RuntimeException(
                "Format nomor WhatsApp tidak valid: {$phoneNumber}"
            );
        }

        return $phoneNumber;
    }

    /**
     * Mengambil seluruh nomor admin dari konfigurasi.
     */
    protected function getAdminNumbers(): array
    {
        $adminNumbers = config(
            'whatsapp.admin_numbers',
            []
        );

        if (!is_array($adminNumbers)) {
            $adminNumbers = [
                $adminNumbers,
            ];
        }

        $adminNumbers = array_map(
            function ($number) {
                return $this->normalizePhoneNumber(
                    (string) $number
                );
            },
            $adminNumbers
        );

        $adminNumbers = array_values(
            array_unique(
                array_filter($adminNumbers)
            )
        );

        /*
         * Fallback untuk konfigurasi lama:
         * whatsapp.admin_number
         */
        if ($adminNumbers === []) {
            $legacyAdminNumber = config(
                'whatsapp.admin_number'
            );

            if (
                is_string($legacyAdminNumber) &&
                trim($legacyAdminNumber) !== ''
            ) {
                $adminNumbers[] = $this->normalizePhoneNumber(
                    $legacyAdminNumber
                );
            }
        }

        if ($adminNumbers === []) {
            throw new RuntimeException(
                'WHATSAPP_ADMIN_NUMBERS belum diisi.'
            );
        }

        return $adminNumbers;
    }

    /**
     * Mengirim request pesan teks ke satu nomor.
     */
    protected function sendTextRequest(
        string $recipient,
        string $message
    ): Response {
        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->post($this->endpoint(), [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'preview_url' => (bool) config(
                        'whatsapp.preview_url',
                        false
                    ),
                    'body' => $message,
                ],
            ]);
    }

    /**
     * Mengirim pesan teks biasa ke satu nomor.
     *
     * Method ini tetap dipertahankan agar kompatibel
     * dengan pemanggilan dari controller atau service lain.
     */
    public function sendText(
        string $recipient,
        string $message
    ): Response {
        $this->validateConfiguration();

        $recipient = $this->normalizePhoneNumber(
            $recipient
        );

        $message = trim($message);

        if ($message === '') {
            throw new RuntimeException(
                'Isi pesan WhatsApp tidak boleh kosong.'
            );
        }

        return $this->sendTextRequest(
            $recipient,
            $message
        );
    }

    /**
     * Mengirim pesan ke seluruh nomor admin.
     *
     * Pesan akan dikirim satu per satu ke setiap nomor
     * yang terdapat pada WHATSAPP_ADMIN_NUMBERS.
     *
     * Method mengembalikan response terakhir agar tetap
     * kompatibel dengan kode lama yang mengharapkan Response.
     */
    public function sendToAdmin(
        string $message
    ): Response {
        $this->validateConfiguration();

        $message = trim($message);

        if ($message === '') {
            throw new RuntimeException(
                'Isi pesan WhatsApp tidak boleh kosong.'
            );
        }

        $adminNumbers = $this->getAdminNumbers();

        $lastResponse = null;

        foreach ($adminNumbers as $adminNumber) {
            $lastResponse = $this->sendText(
                $adminNumber,
                $message
            );

            /*
             * Jika salah satu pengiriman gagal,
             * hentikan proses dan lempar error.
             */
            if ($lastResponse->failed()) {
                throw new RuntimeException(
                    'Gagal mengirim WhatsApp ke nomor admin '
                    . $adminNumber
                    . '. Response: '
                    . $lastResponse->body()
                );
            }
        }

        if (!$lastResponse instanceof Response) {
            throw new RuntimeException(
                'Tidak ada response WhatsApp yang diterima.'
            );
        }

        return $lastResponse;
    }

    /**
     * Mengirim pesan ke nomor testing.
     */
    public function sendTestMessage(
        string $message = 'Halo, ini pesan test dari WhatsApp API Zalina Fashion.'
    ): Response {
        $recipient = config(
            'whatsapp.test_number'
        );

        if (
            !is_string($recipient) ||
            trim($recipient) === ''
        ) {
            throw new RuntimeException(
                'WHATSAPP_TEST_NUMBER belum diisi.'
            );
        }

        return $this->sendText(
            $recipient,
            $message
        );
    }

    /**
     * Mengirim pesan hanya jika notifikasi diaktifkan.
     */
    public function sendNotification(
        string $recipient,
        string $message
    ): ?Response {
        if (
            !(bool) config(
                'whatsapp.enabled',
                true
            )
        ) {
            return null;
        }

        if (
            !(bool) config(
                'whatsapp.notifications_enabled',
                true
            )
        ) {
            return null;
        }

        return $this->sendText(
            $recipient,
            $message
        );
    }

    /**
     * Mengirim notifikasi ke seluruh admin
     * hanya jika notifikasi diaktifkan.
     */
    public function sendAdminNotification(
        string $message
    ): ?Response {
        if (
            !(bool) config(
                'whatsapp.enabled',
                true
            )
        ) {
            return null;
        }

        if (
            !(bool) config(
                'whatsapp.notifications_enabled',
                true
            )
        ) {
            return null;
        }

        return $this->sendToAdmin(
            $message
        );
    }

    /**
     * Mengirim template WhatsApp ke satu nomor.
     */
    public function sendTemplate(
        string $recipient,
        string $templateName,
        string $languageCode = 'id',
        array $parameters = []
    ): Response {
        $this->validateConfiguration();

        $recipient = $this->normalizePhoneNumber(
            $recipient
        );

        $templateName = trim($templateName);

        if ($templateName === '') {
            throw new RuntimeException(
                'Nama template WhatsApp tidak boleh kosong.'
            );
        }

        $languageCode = trim($languageCode);

        if ($languageCode === '') {
            throw new RuntimeException(
                'Kode bahasa template WhatsApp tidak boleh kosong.'
            );
        }

        $components = [];

        if ($parameters !== []) {
            $bodyParameters = [];

            foreach ($parameters as $parameter) {
                $bodyParameters[] = [
                    'type' => 'text',
                    'text' => (string) $parameter,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipient,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if ($components !== []) {
            $payload['template']['components'] = $components;
        }

        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->post(
                $this->endpoint(),
                $payload
            );
    }

    /**
     * Mengambil endpoint untuk kebutuhan debugging.
     */
    public function getEndpoint(): string
    {
        return $this->endpoint();
    }
}