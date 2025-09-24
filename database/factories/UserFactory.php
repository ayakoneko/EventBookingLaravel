<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


/**
 * Factory for seeding User records.
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * Defaults users to the "attendee" role with a verified email and 
 * a uniform demo password to simplify local logins and feature tests.
 */

class UserFactory extends Factory
{
    /**
     * The current hashed password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     * Creates a verified attendee with fake() and 
     * a convenient demo password and an immediate consent timestamp
     * @return array<string,mixed> Attribute map for a default User.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'type' => 'attendee', // default role
            'consented_at' => now(),
        ];
    }

    /** State: organiser */
    public function organiser(): static
    {
        return $this->state(fn () => ['type' => 'organiser']);
    }

    /** State: attendee (explicit) */
    public function attendee(): static
    {
        return $this->state(fn () => ['type' => 'attendee']);
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
}
