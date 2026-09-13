<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'court_id',
        'booking_code',
        'booking_date',
        'start_time',
        'end_time',
        'duration_hours',
        'total_price',
        'deposit_amount',
        'deposit_percentage',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'refund_amount',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'duration_hours' => 'decimal:2',
            'total_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'deposit_percentage' => 'integer',
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Scope query to only include bookings occupying time slots.
     */
    public function scopeActiveBookings(Builder $query): Builder
    {
        return $query->whereIn('status', BookingStatus::activeStatuses());
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === BookingStatus::AwaitingPayment;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::Confirmed;
    }

    public function isCheckedIn(): bool
    {
        return $this->status === BookingStatus::CheckedIn;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::Cancelled;
    }

    public function isExpired(): bool
    {
        return $this->status === BookingStatus::Expired;
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canTransitionTo(BookingStatus::Cancelled);
    }

    public function canBeReviewed(): bool
    {
        return $this->status === BookingStatus::Completed && $this->review === null;
    }
}
