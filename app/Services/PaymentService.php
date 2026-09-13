<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Initiate payment transaction and get checkout URL.
     *
     * @return array<string, mixed>
     */
    public function initiateBookingPayment(Booking $booking, string $provider = 'mock'): array
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::AwaitingPayment])) {
            throw new InvalidArgumentException('Đơn đặt sân không ở trạng thái chờ thanh toán.');
        }

        if ($booking->expires_at && $booking->expires_at->isPast()) {
            $booking->update(['status' => BookingStatus::Expired]);
            throw new InvalidArgumentException('Đơn đặt sân này đã hết hạn thanh toán.');
        }

        $payment = $booking->payment;
        if (! $payment) {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $booking->deposit_amount,
                'payment_method' => $provider,
                'status' => PaymentStatus::Pending,
            ]);
        } else {
            $payment->update([
                'payment_method' => $provider,
                'amount' => $booking->deposit_amount,
            ]);
        }

        $gateway = PaymentGatewayFactory::make($provider);

        return $gateway->initiatePayment($payment);
    }

    /**
     * Process webhook callback and confirm booking on success.
     *
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, message: string, booking: ?Booking}
     */
    public function handleWebhook(string $provider, array $payload, ?string $signature = null): array
    {
        $gateway = PaymentGatewayFactory::make($provider);

        if (! $gateway->verifyWebhook($payload, $signature)) {
            throw new InvalidArgumentException('Chữ ký xác thực webhook không hợp lệ.');
        }

        $result = $gateway->processCallback($payload);

        $paymentId = $payload['payment_id'] ?? null;
        $payment = Payment::with('booking')->find($paymentId);

        if (! $payment) {
            throw new InvalidArgumentException('Không tìm thấy giao dịch thanh toán.');
        }

        $booking = $payment->booking;

        DB::transaction(function () use ($payment, $booking, $result) {
            if ($result['success']) {
                $payment->update([
                    'transaction_id' => $result['transaction_id'],
                    'status' => PaymentStatus::Completed,
                    'paid_at' => now(),
                    'provider_response' => $result['raw_data'],
                ]);

                $booking->update([
                    'status' => BookingStatus::Confirmed,
                    'expires_at' => null,
                ]);

                // Send notification to player and owner
                $booking->loadMissing(['court.venue.owner', 'user']);
                if ($booking->user) {
                    $booking->user->notify(new BookingConfirmedNotification($booking));
                }
                if ($booking->court?->venue?->owner) {
                    $booking->court->venue->owner->notify(new BookingConfirmedNotification($booking));
                }
            } else {
                $payment->update([
                    'status' => PaymentStatus::Failed,
                    'provider_response' => $result['raw_data'],
                ]);
            }
        });

        return [
            'success' => $result['success'],
            'message' => $result['message'],
            'booking' => $booking->fresh(['court.venue', 'payment']),
        ];
    }
}
