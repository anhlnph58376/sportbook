<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\SystemConfiguration;
use App\Notifications\BookingCancelledNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CancellationService
{
    /**
     * Cancel a booking and calculate refund based on cancellation policy.
     *
     * @return array{booking: Booking, refund_amount: float, refund_percentage: int}
     */
    public function cancelBooking(Booking $booking, string $reason): array
    {
        if (! $booking->canBeCancelled()) {
            throw new InvalidArgumentException('Đơn đặt sân không thể hủy ở trạng thái hiện tại ('.$booking->status->label().').');
        }

        $dateString = $booking->booking_date instanceof \DateTimeInterface
            ? $booking->booking_date->format('Y-m-d')
            : substr((string) $booking->booking_date, 0, 10);

        $bookingStartTime = Carbon::parse($dateString.' '.$booking->start_time);
        $now = now();

        if ($bookingStartTime->isPast()) {
            throw new InvalidArgumentException('Không thể hủy đơn đặt sân khi thời gian đã bắt đầu.');
        }

        $hoursUntilBooking = $now->diffInHours($bookingStartTime, false);

        $fullRefundHours = (int) SystemConfiguration::getValue('cancellation_full_refund_hours', 24);
        $halfRefundHours = (int) SystemConfiguration::getValue('cancellation_half_refund_hours', 12);

        $refundPercentage = 0;
        $refundAmount = 0.0;

        // If payment was already completed, calculate refund
        if ($booking->payment && $booking->payment->isCompleted()) {
            if ($hoursUntilBooking >= $fullRefundHours) {
                $refundPercentage = 100;
            } elseif ($hoursUntilBooking >= $halfRefundHours) {
                $refundPercentage = 50;
            } else {
                $refundPercentage = 0;
            }

            $refundAmount = round(($booking->payment->amount * $refundPercentage) / 100, 2);
        }

        DB::transaction(function () use ($booking, $reason, $refundAmount) {
            $booking->update([
                'status' => BookingStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'refund_amount' => $refundAmount,
            ]);

            if ($booking->payment && $refundAmount > 0) {
                $booking->payment->update([
                    'status' => PaymentStatus::Refunded,
                    'refund_amount' => $refundAmount,
                    'refunded_at' => now(),
                ]);
            }
        });

        // Notify player and venue owner
        $booking->loadMissing(['court.venue.owner', 'user']);
        if ($booking->user) {
            $booking->user->notify(new BookingCancelledNotification($booking, $refundAmount));
        }
        if ($booking->court?->venue?->owner) {
            $booking->court->venue->owner->notify(new BookingCancelledNotification($booking, $refundAmount));
        }

        return [
            'booking' => $booking->fresh(['court.venue', 'payment']),
            'refund_amount' => $refundAmount,
            'refund_percentage' => $refundPercentage,
        ];
    }
}
