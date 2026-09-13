<?php

namespace App\Services;

use App\Enums\CourtStatus;
use App\Models\Booking;
use App\Models\Court;
use Carbon\Carbon;

class AvailabilityService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    /**
     * Get available time slots for a court on a specified date.
     *
     * @return array{is_closed: bool, reason: ?string, slots: list<array<string, mixed>>}
     */
    public function getCourtAvailability(Court $court, string $dateString): array
    {
        $date = Carbon::parse($dateString);

        if ($court->status !== CourtStatus::Active) {
            return [
                'is_closed' => true,
                'reason' => 'Sân hiện đang tạm ngưng hoạt động hoặc bảo trì.',
                'slots' => [],
            ];
        }

        $venue = $court->venue;

        // Check venue holidays
        $holiday = $venue->holidays()->where('date', $date->format('Y-m-d'))->first();
        if ($holiday) {
            return [
                'is_closed' => true,
                'reason' => 'Sân đóng cửa nghỉ lễ: '.($holiday->reason ?: 'Nghỉ theo lịch'),
                'slots' => [],
            ];
        }

        // Carbon dayOfWeekIso: 1 (Mon) -> 7 (Sun). Our schema uses 0 (Mon) -> 6 (Sun)
        $dayOfWeek = $date->dayOfWeekIso - 1;
        $operatingHour = $venue->operatingHours()->where('day_of_week', $dayOfWeek)->first();

        if ($operatingHour && $operatingHour->is_closed) {
            return [
                'is_closed' => true,
                'reason' => 'Sân không mở cửa vào ngày này.',
                'slots' => [],
            ];
        }

        $openTime = $operatingHour?->open_time ?: $venue->opening_time;
        $closeTime = $operatingHour?->close_time ?: $venue->closing_time;

        $venueOpen = Carbon::parse($dateString.' '.$openTime);
        $venueClose = Carbon::parse($dateString.' '.$closeTime);

        // Fetch active bookings for this court and date
        $existingBookings = Booking::where('court_id', $court->id)
            ->where('booking_date', $dateString)
            ->activeBookings()
            ->get();

        $slots = [];
        $current = $venueOpen->copy();

        while ($current->lt($venueClose)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addHour();

            if ($slotEnd->gt($venueClose)) {
                break;
            }

            $slotStartTime = $slotStart->format('H:i:s');
            $slotEndTime = $slotEnd->format('H:i:s');

            // Check if any existing booking overlaps this slot
            $isBooked = $existingBookings->contains(function (Booking $booking) use ($slotStartTime, $slotEndTime) {
                return $booking->start_time < $slotEndTime && $booking->end_time > $slotStartTime;
            });

            // If date is today, check if slot has already passed
            $isPast = $date->isToday() && $slotStart->lt(now());

            $priceInfo = $this->pricingService->calculateBookingPrice(
                $court,
                $dateString,
                $slotStartTime,
                $slotEndTime
            );

            $slots[] = [
                'start_time' => $slotStart->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'is_available' => ! $isBooked && ! $isPast,
                'is_booked' => $isBooked,
                'is_past' => $isPast,
                'price' => $priceInfo['total_price'],
                'deposit' => $priceInfo['deposit_amount'],
            ];

            $current->addHour();
        }

        return [
            'is_closed' => false,
            'reason' => null,
            'slots' => $slots,
        ];
    }
}
