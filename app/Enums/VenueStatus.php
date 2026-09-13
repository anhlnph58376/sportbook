<?php

namespace App\Enums;

enum VenueStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::Active => 'Đang hoạt động',
            self::Inactive => 'Tạm dừng',
            self::Suspended => 'Bị đình chỉ',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Active;
    }
}
