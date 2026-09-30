<?php

$phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID');
$accessToken = getenv('WHATSAPP_ACCESS_TOKEN');
$apiVersion = getenv('WHATSAPP_API_VERSION') ?: 'v25.0';
$recipient = getenv('WHATSAPP_TEST_NUMBER');

$url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";

$data = [
    'messaging_product' => 'whatsapp',
    'to' => $recipient,
    'type' => 'text',
    'text' => [
        'preview_url' => false,
        'body' => 'Halo, ini pesan test dari WhatsApp API Zalina Fashion.',
    ],
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch) . PHP_EOL;
} else {
    echo "HTTP: {$httpCode}" . PHP_EOL;
    echo $response . PHP_EOL;
}

curl_close($ch);
