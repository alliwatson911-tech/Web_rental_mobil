<?php

use App\Support\Rupiah;
use Carbon\CarbonImmutable;

if (! function_exists('rupiah')) {
    /** Format Rupiah tanpa desimal: rupiah(1250000) => "Rp 1.250.000". */
    function rupiah(int|float|null $amount): string
    {
        return Rupiah::format($amount);
    }
}

if (! function_exists('tanggal')) {
    /** Format tanggal Indonesia: tanggal('2025-08-17') => "17 Agu 2025". */
    function tanggal(mixed $date, string $format = 'd M Y'): string
    {
        if ($date === null || $date === '') {
            return '-';
        }

        return CarbonImmutable::parse($date)->locale(app()->getLocale())->translatedFormat($format);
    }
}
