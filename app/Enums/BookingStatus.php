<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::AwaitingPayment => 'Chờ thanh toán',
            self::Confirmed => 'Đã xác nhận',
            self::CheckedIn => 'Đã check-in',
            self::Completed => 'Hoàn thành',
            self::Cancelled => 'Đã hủy',
            self::Expired => 'Đã hết hạn',
            self::Rejected => 'Bị từ chối',
        };
    }

    /**
     * Returns the valid next statuses from the current status.
     *
     * @return array<BookingStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::AwaitingPayment, self::Cancelled, self::Rejected],
            self::AwaitingPayment => [self::Confirmed, self::Cancelled, self::Expired, self::Rejected],
            self::Confirmed => [self::CheckedIn, self::Cancelled, self::Rejected],
            self::CheckedIn => [self::Completed],
            self::Completed => [],
            self::Cancelled => [],
            self::Expired => [],
            self::Rejected => [],
        };
    }

    public function canTransitionTo(BookingStatus $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    /**
     * Statuses that count as "active" (occupying a slot).
     *
     * @return array<BookingStatus>
     */
    public static function activeStatuses(): array
    {
        return [
            self::Pending,
            self::AwaitingPayment,
            self::Confirmed,
            self::CheckedIn,
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::Cancelled,
            self::Expired,
            self::Rejected,
        ], strict: true);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [
            self::AwaitingPayment,
            self::Confirmed,
        ], strict: true);
    }
}
