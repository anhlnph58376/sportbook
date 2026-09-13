<?php

namespace App\Services\Payment;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initiate payment and return checkout URL or instructions.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function initiatePayment(Payment $payment, array $options = []): array;

    /**
     * Verify authenticity of webhook signature / payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyWebhook(array $payload, ?string $signature = null): bool;

    /**
     * Process webhook / callback payload into normalized transaction result.
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     success: bool,
     *     transaction_id: string,
     *     amount: float,
     *     message: string,
     *     raw_data: array<string, mixed>
     * }
     */
    public function processCallback(array $payload): array;

    /**
     * Process refund for a transaction.
     *
     * @return array{success: bool, refund_id: ?string, message: string}
     */
    public function refund(Payment $payment, float $amount): array;
}
