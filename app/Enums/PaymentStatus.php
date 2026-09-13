<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ thanh toán',
            self::Completed => 'Đã thanh toán',
            self::Failed => 'Thất bại',
            self::Refunded => 'Đã hoàn tiền',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Completed;
    }
}
