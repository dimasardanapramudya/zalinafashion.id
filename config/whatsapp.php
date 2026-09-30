<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */

    'app_name' => env(
        'WHATSAPP_APP_NAME',
        'Zalina Fashion'
    ),

    'enabled' => filter_var(
        env('WHATSAPP_ENABLED', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business Account
    |--------------------------------------------------------------------------
    */

    'business_account_id' => env(
        'WHATSAPP_BUSINESS_ACCOUNT_ID'
    ),

    'phone_number_id' => env(
        'WHATSAPP_PHONE_NUMBER_ID'
    ),

    /*
    |--------------------------------------------------------------------------
    | Meta Graph API
    |--------------------------------------------------------------------------
    */

    'api_version' => env(
        'WHATSAPP_API_VERSION',
        'v25.0'
    ),

    'base_url' => rtrim(
        env(
            'WHATSAPP_BASE_URL',
            'https://graph.facebook.com'
        ),
        '/'
    ),

    'access_token' => env(
        'WHATSAPP_ACCESS_TOKEN'
    ),

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    */

    'webhook_url' => env(
        'WHATSAPP_WEBHOOK_URL'
    ),

    'verify_token' => env(
        'WHATSAPP_VERIFY_TOKEN'
    ),

    'webhook_secret' => env(
        'WHATSAPP_WEBHOOK_SECRET'
    ),

    /*
    |--------------------------------------------------------------------------
    | Recipient Numbers
    |--------------------------------------------------------------------------
    |
    | Gunakan format internasional tanpa tanda +, spasi, atau tanda -.
    |
    | Contoh:
    | 62895322389911,6281331883456
    |
    | WHATSAPP_ADMIN_NUMBERS digunakan untuk mengirim notifikasi
    | yang sama ke beberapa nomor sekaligus.
    |
    */

    'admin_numbers' => array_values(
        array_filter(
            array_map(
                static function ($number) {
                    return preg_replace(
                        '/[^0-9]/',
                        '',
                        trim($number)
                    );
                },
                explode(
                    ',',
                    env('WHATSAPP_ADMIN_NUMBERS', '')
                )
            )
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Backward Compatibility
    |--------------------------------------------------------------------------
    |
    | Tetap dipertahankan agar kode lama yang menggunakan
    | config('whatsapp.admin_number') dan config('whatsapp.test_number')
    | tidak langsung mengalami error.
    |
    */

    'admin_number' => env(
        'WHATSAPP_ADMIN_NUMBER'
    ),

    'test_number' => env(
        'WHATSAPP_TEST_NUMBER'
    ),

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env(
        'WHATSAPP_TIMEOUT',
        30
    ),

    'connect_timeout' => (int) env(
        'WHATSAPP_CONNECT_TIMEOUT',
        10
    ),

    'retry_times' => (int) env(
        'WHATSAPP_RETRY_TIMES',
        2
    ),

    'retry_sleep' => (int) env(
        'WHATSAPP_RETRY_SLEEP',
        500
    ),

    /*
    |--------------------------------------------------------------------------
    | Default Message Settings
    |--------------------------------------------------------------------------
    */

    'preview_url' => filter_var(
        env(
            'WHATSAPP_PREVIEW_URL',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'default_language' => env(
        'WHATSAPP_DEFAULT_LANGUAGE',
        'id'
    ),

    'default_country_code' => env(
        'WHATSAPP_DEFAULT_COUNTRY_CODE',
        '62'
    ),

    /*
    |--------------------------------------------------------------------------
    | Notification Master Switch
    |--------------------------------------------------------------------------
    */

    'notifications_enabled' => filter_var(
        env(
            'WHATSAPP_NOTIFICATIONS_ENABLED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Order Notifications
    |--------------------------------------------------------------------------
    */

    'notify_order' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_created' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_CREATED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_confirmed' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_CONFIRMED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_processing' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_PROCESSING',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_shipped' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_SHIPPED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_completed' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_COMPLETED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_order_cancelled' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ORDER_CANCELLED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Payment Notifications
    |--------------------------------------------------------------------------
    */

    'notify_payment' => filter_var(
        env(
            'WHATSAPP_NOTIFY_PAYMENT',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_payment_pending' => filter_var(
        env(
            'WHATSAPP_NOTIFY_PAYMENT_PENDING',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_payment_success' => filter_var(
        env(
            'WHATSAPP_NOTIFY_PAYMENT_SUCCESS',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_payment_failed' => filter_var(
        env(
            'WHATSAPP_NOTIFY_PAYMENT_FAILED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Customer Notifications
    |--------------------------------------------------------------------------
    */

    'notify_customer' => filter_var(
        env(
            'WHATSAPP_NOTIFY_CUSTOMER',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_abandoned_cart' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ABANDONED_CART',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_customer_registration' => filter_var(
        env(
            'WHATSAPP_NOTIFY_CUSTOMER_REGISTRATION',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Admin Notifications
    |--------------------------------------------------------------------------
    */

    'notify_admin' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ADMIN',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_admin_new_order' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ADMIN_NEW_ORDER',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_admin_payment' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ADMIN_PAYMENT',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'notify_admin_new_customer' => filter_var(
        env(
            'WHATSAPP_NOTIFY_ADMIN_NEW_CUSTOMER',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Template Message
    |--------------------------------------------------------------------------
    */

    'template_namespace' => env(
        'WHATSAPP_TEMPLATE_NAMESPACE'
    ),

    'template_language' => env(
        'WHATSAPP_TEMPLATE_LANGUAGE',
        'id'
    ),

    'template_order_created' => env(
        'WHATSAPP_TEMPLATE_ORDER_CREATED',
        'order_created'
    ),

    'template_payment_success' => env(
        'WHATSAPP_TEMPLATE_PAYMENT_SUCCESS',
        'payment_success'
    ),

    'template_order_shipped' => env(
        'WHATSAPP_TEMPLATE_ORDER_SHIPPED',
        'order_shipped'
    ),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    'logging_enabled' => filter_var(
        env(
            'WHATSAPP_LOGGING_ENABLED',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'log_channel' => env(
        'WHATSAPP_LOG_CHANNEL',
        'stack'
    ),

    'log_request_body' => filter_var(
        env(
            'WHATSAPP_LOG_REQUEST_BODY',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'log_response_body' => filter_var(
        env(
            'WHATSAPP_LOG_RESPONSE_BODY',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue_enabled' => filter_var(
        env(
            'WHATSAPP_QUEUE_ENABLED',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'queue_connection' => env(
        'WHATSAPP_QUEUE_CONNECTION',
        env('QUEUE_CONNECTION', 'database')
    ),

    'queue_name' => env(
        'WHATSAPP_QUEUE_NAME',
        'whatsapp'
    ),

    /*
    |--------------------------------------------------------------------------
    | Safety
    |--------------------------------------------------------------------------
    */

    'dry_run' => filter_var(
        env(
            'WHATSAPP_DRY_RUN',
            false
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

    'mask_phone_numbers' => filter_var(
        env(
            'WHATSAPP_MASK_PHONE_NUMBERS',
            true
        ),
        FILTER_VALIDATE_BOOLEAN
    ),

];

