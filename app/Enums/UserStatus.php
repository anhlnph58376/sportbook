<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Hoạt động',
            self::Locked => 'Bị khóa',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
