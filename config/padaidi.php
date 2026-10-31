<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi usaha Padaidi Rental Mobil
|--------------------------------------------------------------------------
| Semua nilai bisa diubah lewat file .env tanpa menyentuh kode.
| Angka pada bagian "terms" adalah CONTOH dan perlu dikonfirmasi pemilik usaha.
*/

return [
    'name' => 'Padaidi Rental Mobil',

    'address' => env('PADAIDI_ADDRESS', 'Jl. Perintis Kemerdekaan No.1, Daya, Kec. Biringkanaya, Kota Makassar, Sulawesi Selatan'),
    'hours' => env('PADAIDI_HOURS', 'Buka 24 jam'),
    'email' => env('PADAIDI_EMAIL', 'info@padaidi.test'),
    'instagram' => env('PADAIDI_INSTAGRAM', 'https://www.instagram.com/cv.padaidirentcarmks/'),
    'instagram_handle' => env('PADAIDI_INSTAGRAM_HANDLE', '@cv.padaidirentcarmks'),

    // Nomor WhatsApp admin, format internasional tanpa tanda + (contoh: 6282197233174).
    'admin_whatsapp' => array_values(array_filter(array_map(
        fn ($n) => preg_replace('/\D+/', '', $n),
        explode(',', (string) env('PADAIDI_ADMIN_WHATSAPP', '6282197233174,6282235585445'))
    ))),

    'bank' => [
        'name' => env('PADAIDI_BANK_NAME', 'Bank Contoh'),
        'account' => env('PADAIDI_BANK_ACCOUNT', '0000000000'),
        'holder' => env('PADAIDI_BANK_HOLDER', 'Padaidi Rental Mobil'),
    ],

    // Pesanan berstatus pending otomatis dibatalkan setelah sekian jam.
    'pending_timeout_hours' => (int) env('PADAIDI_PENDING_TIMEOUT_HOURS', 5),

    // Syarat sewa (CONTOH - perlu dikonfirmasi pemilik usaha).
    'terms' => [
        'deposit' => (int) env('PADAIDI_DEPOSIT', 500000),
        'km_limit_per_day' => (int) env('PADAIDI_KM_LIMIT_PER_DAY', 250),
        'km_over_fee' => (int) env('PADAIDI_KM_OVER_FEE', 2000),
        'late_fee_per_hour' => (int) env('PADAIDI_LATE_FEE_PER_HOUR', 50000),
        'late_grace_hours' => (int) env('PADAIDI_LATE_GRACE_HOURS', 1),
        'min_age' => (int) env('PADAIDI_MIN_AGE', 21),
        'min_license_years' => (int) env('PADAIDI_MIN_LICENSE_YEARS', 1),
    ],

    // Ukuran maksimal file bukti transfer & foto mobil (kilobyte).
    'max_upload_kb' => 4096,
];
