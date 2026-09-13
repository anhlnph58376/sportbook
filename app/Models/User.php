<?php

namespace App\Models;

use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    /**
     * Roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Venues owned by the user.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class, 'owner_id');
    }

    /**
     * Bookings made by the user as a player.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    /**
     * Reviews written by the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * Review replies posted by the user (as venue owner).
     */
    public function reviewReplies(): HasMany
    {
        return $this->hasMany(ReviewReply::class, 'user_id');
    }

    /**
     * Venues favorited by the user.
     */
    public function favoriteVenues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'favorites')->withTimestamps();
    }

    /**
     * Audit logs triggered by the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Determine if user has a specific role or any role in a list.
     *
     * @param  string|array<int, string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        $roleList = is_array($roles) ? $roles : [$roles];

        return $this->roles->pluck('name')->intersect($roleList)->isNotEmpty();
    }

    /**
     * Determine if user has administrator role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Determine if user has venue owner role.
     */
    public function isVenueOwner(): bool
    {
        return $this->hasRole('venue_owner');
    }

    /**
     * Determine if user has player role.
     */
    public function isPlayer(): bool
    {
        return $this->hasRole('player');
    }

    /**
     * Determine if user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    /**
     * Determine if user account is locked.
     */
    public function isLocked(): bool
    {
        return $this->status === UserStatus::Locked;
    }
}
