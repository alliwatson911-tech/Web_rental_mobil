<?php

namespace App\Support;

class Rupiah
{
    /** Format angka menjadi "Rp 1.250.000" (tanpa desimal). */
    public static function format(int|float|null $amount): string
    {
        return 'Rp '.number_format((int) round((float) $amount), 0, ',', '.');
    }
}
