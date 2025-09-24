<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a minimal, predictable set of users for local/dev/testing.
 *
 * Creates two organisers for ownership and authorisation scenarios and two attendees for booking/waitlist flows. 
 * Remaining users (eight attendees) is created via UserFactory.
 * Passwords are uniform for convenience in demos and feature tests.
 */

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2 organisers
        User::factory()->create([
            'name' => 'Organizer One',
            'email' => 'org1@example.test',
            'password' => Hash::make('password'),
            'type' => 'organiser',
            'consented_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Organizer Two',
            'email' => 'org2@example.test',
            'password' => Hash::make('password'),
            'type' => 'organiser',
            'consented_at' => now(),
        ]);

        // 10 attendees (2 prefilled, 8 from factory)
        User::factory()->create([
            'name' => 'Attendee One',
            'email' => 'aten1@example.test',
            'password' => Hash::make('password'),
            'type' => 'attendee',
            'consented_at' => now(),
        ]);
        User::factory()->create([
            'name' => 'Attendee Two',
            'email' => 'aten2@example.test',
            'password' => Hash::make('password'),
            'type' => 'attendee',
            'consented_at' => now(),
        ]);
        User::factory(8)->attendee()->create([
            'password' => Hash::make('password'),
            'consented_at' => now(),
        ]);
    }
}
