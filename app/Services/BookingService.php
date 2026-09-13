<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\CourtStatus;
use App\Enums\PaymentStatus;
use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use App\Exceptions\BookingConflictException;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\SystemConfiguration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    /**
     * Create a new booking safely with database transaction and row-level locking.
     *
     * @param array{
     *     court_id: int,
     *     booking_date: string,
     *     start_time: string,
     *     end_time: string,
     *     notes?: ?string
     * } $data
     *
     * @throws BookingConflictException
     * @throws InvalidArgumentException
     */
    public function createBooking(User $user, array $data): Booking
    {
        $date = Carbon::parse($data['booking_date']);
        $startTime = Carbon::parse($data['booking_date'].' '.$data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($data['booking_date'].' '.$data['end_time'])->format('H:i:s');

        // Validation 1: Cannot book in past
        if ($date->isPast() && ! $date->isToday()) {
            throw new InvalidArgumentException('Không thể đặt sân trong quá khứ.');
        }

        if ($date->isToday()) {
            $slotStart = Carbon::parse($data['booking_date'].' '.$startTime);
            if ($slotStart->isPast()) {
                throw new InvalidArgumentException('Khung giờ được chọn đã trôi qua.');
            }
        }

        if ($endTime <= $startTime) {
            throw new InvalidArgumentException('Giờ kết thúc phải lớn hơn giờ bắt đầu.');
        }

        return DB::transaction(function () use ($user, $data, $startTime, $endTime) {
            // Lock court row to prevent concurrent race condition modifications
            $court = Court::where('id', $data['court_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($court->status !== CourtStatus::Active) {
                throw new InvalidArgumentException('Sân hiện không hoạt động hoặc đang bảo trì.');
            }

            $venue = $court->venue;
            if ($venue->status !== VenueStatus::Active || $venue->verification_status !== VenueVerificationStatus::Approved) {
                throw new InvalidArgumentException('Cơ sở thể thao hiện chưa sẵn sàng đón khách.');
            }

            // Check holidays
            if ($venue->holidays()->where('date', $data['booking_date'])->exists()) {
                throw new InvalidArgumentException('Cơ sở thể thao đóng cửa nghỉ lễ vào ngày này.');
            }

            // Concurrency Lock: Check if any active booking overlaps the requested slot
            $conflict = Booking::where('court_id', $court->id)
                ->where('booking_date', $data['booking_date'])
                ->activeBookings()
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw new BookingConflictException;
            }

            // Calculate pricing
            $priceData = $this->pricingService->calculateBookingPrice(
                $court,
                $data['booking_date'],
                $startTime,
                $endTime
            );

            $paymentWindowMinutes = (int) SystemConfiguration::getValue('booking_payment_window_minutes', 30);
            $expiresAt = now()->addMinutes($paymentWindowMinutes);

            // Generate unique booking code e.g. SB-20260913-AB12CD
            $dateCode = Carbon::parse($data['booking_date'])->format('Ymd');
            $randomCode = strtoupper(Str::random(6));
            $bookingCode = "SB-{$dateCode}-{$randomCode}";

            // Create Booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'court_id' => $court->id,
                'booking_code' => $bookingCode,
                'booking_date' => $data['booking_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_hours' => $priceData['duration_hours'],
                'total_price' => $priceData['total_price'],
                'deposit_amount' => $priceData['deposit_amount'],
                'deposit_percentage' => $priceData['deposit_percentage'],
                'status' => BookingStatus::AwaitingPayment,
                'expires_at' => $expiresAt,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create pending payment record for deposit
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_id' => null,
                'amount' => $booking->deposit_amount,
                'payment_method' => 'mock',
                'status' => PaymentStatus::Pending,
            ]);

            return $booking->load(['court.venue', 'payment']);
        });
    }

    /**
     * Check in player by venue owner.
     */
    public function checkIn(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Confirmed) {
            throw new InvalidArgumentException('Chỉ có thể check-in các đơn đặt sân đã xác nhận (Confirmed).');
        }

        $booking->update([
            'status' => BookingStatus::CheckedIn,
        ]);

        return $booking;
    }

    /**
     * Complete booking session.
     */
    public function complete(Booking $booking): Booking
    {
        if (! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::CheckedIn])) {
            throw new InvalidArgumentException('Không thể hoàn tất đơn đặt sân ở trạng thái hiện tại.');
        }

        $booking->update([
            'status' => BookingStatus::Completed,
        ]);

        return $booking;
    }

    /**
     * Reject booking by venue owner.
     */
    public function reject(Booking $booking, string $reason): Booking
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::AwaitingPayment, BookingStatus::Confirmed])) {
            throw new InvalidArgumentException('Không thể từ chối đơn đặt sân ở trạng thái hiện tại.');
        }

        DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => BookingStatus::Rejected,
                'cancellation_reason' => $reason,
            ]);

            // Refund deposit if already confirmed/paid
            if ($booking->payment && $booking->payment->isCompleted()) {
                $booking->payment->update([
                    'status' => PaymentStatus::Refunded,
                    'refund_amount' => $booking->payment->amount,
                    'refunded_at' => now(),
                ]);

                $booking->update([
                    'refund_amount' => $booking->payment->amount,
                ]);
            }
        });

        return $booking;
    }
}
