<?php

namespace App\Services;

use App\Enums\DayType;
use App\Models\Court;
use App\Models\PricingRule;
use App\Models\SystemConfiguration;
use Carbon\Carbon;
use InvalidArgumentException;

class PricingService
{
    /**
     * Calculate booking price and deposit amount for a court and time window.
     *
     * @return array{duration_hours: float, total_price: float, deposit_amount: float, deposit_percentage: int}
     */
    public function calculateBookingPrice(
        Court $court,
        string $dateString,
        string $startTimeString,
        string $endTimeString
    ): array {
        $date = Carbon::parse($dateString);
        $start = Carbon::parse($dateString.' '.$startTimeString);
        $end = Carbon::parse($dateString.' '.$endTimeString);

        if ($end->lte($start)) {
            throw new InvalidArgumentException('Giờ kết thúc phải sau giờ bắt đầu.');
        }

        $durationMinutes = $start->diffInMinutes($end);
        $durationHours = round($durationMinutes / 60, 2);

        $dayType = $date->isWeekend() ? DayType::Weekend : DayType::Weekday;

        // Fetch pricing rules for court matching day type or all
        $rules = $court->pricingRules()
            ->active()
            ->whereIn('day_type', [$dayType->value, DayType::All->value])
            ->get();

        // Calculate rate based on interval pricing
        $totalPrice = 0.0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $slotTime = $current->format('H:i:s');

            // Find matching rule for current slot time
            $matchedRule = $rules->first(function (PricingRule $rule) use ($slotTime) {
                return $slotTime >= $rule->start_time && $slotTime < $rule->end_time;
            });

            // Default fallback rate if no explicit rule configured (e.g. 200,000 VND/hr)
            $ratePerHour = $matchedRule ? (float) $matchedRule->price_per_hour : 200000.00;

            // Price for 30-min block = rate / 2
            $totalPrice += ($ratePerHour / 2);
            $current->addMinutes(30);
        }

        // Get deposit percentage from configuration or default to 30%
        $depositPercentage = (int) SystemConfiguration::getValue('default_deposit_percentage', 30);
        $depositAmount = round(($totalPrice * $depositPercentage) / 100, 2);

        return [
            'duration_hours' => $durationHours,
            'total_price' => round($totalPrice, 2),
            'deposit_amount' => $depositAmount,
            'deposit_percentage' => $depositPercentage,
        ];
    }
}
