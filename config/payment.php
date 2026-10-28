<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Driver aktif untuk pemrosesan pembayaran online. Pilihan: 'fake', 'xendit'.
    | Driver 'fake' digunakan untuk pengujian otomatis dan lingkungan lokal
    | tanpa memerlukan internet atau kunci API pihak ketiga.
    |
    */
    'driver' => env('PAYMENT_GATEWAY_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | Batas Waktu Kedaluwarsa Tagihan (Jam)
    |--------------------------------------------------------------------------
    */
    'expiry_hours' => (int) env('PAYMENT_EXPIRY_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Persentase Uang Muka (Down Payment / DP)
    |--------------------------------------------------------------------------
    */
    'dp_percentage' => (int) env('PAYMENT_DP_PERCENTAGE', 30),

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Xendit Payment Gateway
    |--------------------------------------------------------------------------
    */
    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY', ''),
        'public_key' => env('XENDIT_PUBLIC_KEY', ''),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', 'padaidi-xendit-webhook-token'),
        'is_production' => (bool) env('XENDIT_IS_PRODUCTION', false),
        'base_url' => env('XENDIT_IS_PRODUCTION', false)
            ? 'https://api.xendit.co'
            : 'https://api.xendit.co',
    ],

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Fake Driver (Testing & Local)
    |--------------------------------------------------------------------------
    */
    'fake' => [
        'auto_success' => (bool) env('FAKE_PAYMENT_AUTO_SUCCESS', false),
    ],
];

