<?php

namespace App\Enums;

enum DayType: string
{
    case Weekday = 'weekday';
    case Weekend = 'weekend';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Weekday => 'Ngày thường',
            self::Weekend => 'Cuối tuần',
            self::All => 'Tất cả',
        };
    }

    /**
     * Check if this day type applies to the given Carbon day of week.
     * Carbon: 0=Sunday, 1=Monday, ..., 6=Saturday
     */
    public function appliesTo(int $carbonDayOfWeek): bool
    {
        return match ($this) {
            self::All => true,
            self::Weekend => in_array($carbonDayOfWeek, [0, 6]),   // Sun, Sat
            self::Weekday => ! in_array($carbonDayOfWeek, [0, 6]),
        };
    }
}
