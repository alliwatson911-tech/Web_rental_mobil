<?php

namespace App\Exceptions;

use App\Enums\BookingStatus;
use RuntimeException;

class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(BookingStatus $from, BookingStatus $to)
    {
        parent::__construct("Status pesanan tidak bisa diubah dari \"{$from->label()}\" ke \"{$to->label()}\".");
    }
}
