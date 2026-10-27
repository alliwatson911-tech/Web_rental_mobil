<?php

namespace App\Exceptions;

use RuntimeException;

class CarUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Maaf, mobil ini sudah tidak tersedia pada tanggal yang dipilih. Silakan pilih tanggal atau mobil lain.')
    {
        parent::__construct($message);
    }
}
