<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireUnpaidBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): int
    {
        $expiredCount = 0;

        $expiredBookings = Booking::where('status', BookingStatus::AwaitingPayment)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => BookingStatus::Expired,
            ]);

            $expiredCount++;
        }

        if ($expiredCount > 0) {
            Log::info("ExpireUnpaidBookings: {$expiredCount} unpaid bookings were marked as expired.");
        }

        return $expiredCount;
    }
}
