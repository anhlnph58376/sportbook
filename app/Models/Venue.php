<?php

namespace App\Models;

use App\Enums\VenueStatus;
use App\Enums\VenueVerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'address',
        'province',
        'district',
        'ward',
        'latitude',
        'longitude',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'status',
        'verification_status',
        'rejection_reason',
        'average_rating',
        'total_reviews',
    ];

    protected function casts(): array
    {
        return [
            'status' => VenueStatus::class,
            'verification_status' => VenueVerificationStatus::class,
            'average_rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'total_reviews' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'venue_sport');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'venue_amenity');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VenueImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(VenueImage::class)->where('is_primary', true);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VenueDocument::class);
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class)->orderBy('day_of_week');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(VenueHoliday::class)->orderBy('date');
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Scope query to only include publicly active and approved venues.
     */
    public function scopeActiveAndApproved(Builder $query): Builder
    {
        return $query->where('status', VenueStatus::Active)
            ->where('verification_status', VenueVerificationStatus::Approved);
    }

    /**
     * Recalculate average rating and review count.
     */
    public function recalculateRating(): void
    {
        $stats = $this->reviews()
            ->where('is_visible', true)
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg_rating')
            ->first();

        $this->update([
            'total_reviews' => $stats->total ?? 0,
            'average_rating' => round($stats->avg_rating ?? 0, 2),
        ]);
    }
}
