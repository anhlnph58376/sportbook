<?php

namespace App\Enums;

enum CourtStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case UnderMaintenance = 'under_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Đang hoạt động',
            self::Inactive => 'Tạm dừng',
            self::UnderMaintenance => 'Đang bảo trì',
        };
    }

    public function isBookable(): bool
    {
        return $this === self::Active;
    }
}
