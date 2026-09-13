<?php

namespace App\Enums;

enum VenueVerificationStatus: string
{
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingReview => 'Chờ phê duyệt',
            self::Approved => 'Đã phê duyệt',
            self::Rejected => 'Bị từ chối',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }
}
