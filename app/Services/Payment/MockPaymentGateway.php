<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function initiatePayment(Payment $payment, array $options = []): array
    {
        $mockToken = Str::random(32);
        $checkoutUrl = url("/api/v1/payments/mock/checkout?token={$mockToken}&payment_id={$payment->id}");

        return [
            'checkout_url' => $checkoutUrl,
            'token' => $mockToken,
            'amount' => $payment->amount,
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
            'provider' => 'mock',
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        // Mock gateway allows verification as long as secret or valid structure is present
        return isset($payload['payment_id']);
    }

    public function processCallback(array $payload): array
    {
        $status = $payload['status'] ?? 'success';
        $success = $status === 'success';

        return [
            'success' => $success,
            'transaction_id' => $payload['transaction_id'] ?? ('MOCK-'.strtoupper(Str::random(12))),
            'amount' => (float) ($payload['amount'] ?? 0),
            'message' => $success ? 'Thanh toán thành công qua Mock Gateway.' : 'Thanh toán thất bại.',
            'raw_data' => $payload,
        ];
    }

    public function refund(Payment $payment, float $amount): array
    {
        return [
            'success' => true,
            'refund_id' => 'REF-'.strtoupper(Str::random(10)),
            'message' => 'Hoàn tiền thành công qua Mock Gateway.',
        ];
    }
}
