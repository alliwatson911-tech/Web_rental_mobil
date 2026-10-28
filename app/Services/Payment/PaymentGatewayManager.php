<?php

namespace App\Services\Payment;

use App\Services\Payment\Drivers\FakePaymentGateway;
use App\Services\Payment\Drivers\XenditPaymentGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    protected ?PaymentGatewayInterface $customDriver = null;

    public function driver(?string $name = null): PaymentGatewayInterface
    {
        if ($this->customDriver !== null) {
            return $this->customDriver;
        }

        $driverName = $name ?: config('payment.driver', 'fake');

        return match ($driverName) {
            'fake' => app(FakePaymentGateway::class),
            'xendit' => app(XenditPaymentGateway::class),
            default => throw new InvalidArgumentException("Payment gateway driver [{$driverName}] tidak didukung."),
        };
    }

    /** Digunakan untuk testing mock / fake driver swapping */
    public function setDriver(PaymentGatewayInterface $gateway): self
    {
        $this->customDriver = $gateway;
        return $this;
    }
}

