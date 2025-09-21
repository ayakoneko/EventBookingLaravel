<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // 10 attendees
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
