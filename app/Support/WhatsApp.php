<?php

namespace App\Support;

class WhatsApp
{
    /** Buat tautan wa.me dengan pesan yang sudah terisi. */
    public static function link(string $number, string $message = ''): string
    {
        $number = preg_replace('/\D+/', '', $number);

        return 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    /** Tampilkan nomor 6282197233174 sebagai +62 821-9723-3174. */
    public static function display(string $number): string
    {
        $n = preg_replace('/\D+/', '', $number);
        if (str_starts_with($n, '62')) {
            $local = substr($n, 2);

            return '+62 '.trim(implode('-', [substr($local, 0, 3), substr($local, 3, 4), substr($local, 7)]), '-');
        }

        return $n;
    }

    /** @return array<int, string> */
    public static function adminNumbers(): array
    {
        return config('padaidi.admin_whatsapp', []);
    }
}
