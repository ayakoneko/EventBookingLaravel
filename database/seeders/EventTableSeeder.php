<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Seeds a set of future-dated events (15) to populate listings and dashboards.
 *
 * Relies on the Event factory to assign a valid organiser_id and to generate
 * date ranges in the future, ensuring “upcoming events” views have data.
 */

class EventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 15 future events
        Event::factory(15)->create();
    }
}
