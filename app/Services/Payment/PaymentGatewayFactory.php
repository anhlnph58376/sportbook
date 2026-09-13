<?php

namespace App\Services\Payment;

class PaymentGatewayFactory
{
    /**
     * Resolve payment gateway instance by provider name.
     */
    public static function make(string $provider): PaymentGatewayInterface
    {
        return match (strtolower($provider)) {
            'mock' => new MockPaymentGateway,
            default => new MockPaymentGateway,
        };
    }
}
