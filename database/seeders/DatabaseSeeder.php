<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users first so events can safely reference organisers via FK.
        $this->call([
            UserTableSeeder::class,
            EventTableSeeder::class,
        ]);
    }
}
