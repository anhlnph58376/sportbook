<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '09'.fake()->numerify('########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => null,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that user account is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Locked,
        ]);
    }

    /**
     * Attach role after creating user.
     */
    public function withRole(string $roleName): static
    {
        return $this->afterCreating(function (User $user) use ($roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucfirst(str_replace('_', ' ', $roleName))]
            );
            $user->roles()->syncWithoutDetaching([$role->id]);
        });
    }

    public function player(): static
    {
        return $this->withRole('player');
    }

    public function venueOwner(): static
    {
        return $this->withRole('venue_owner');
    }

    public function admin(): static
    {
        return $this->withRole('admin');
    }
}
