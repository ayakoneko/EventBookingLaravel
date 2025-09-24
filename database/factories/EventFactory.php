<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for seeding Event records.
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 * 
 * Uses a pool of titles/locations/images for realistic UI demos, 
 * and generates future-dated schedules to populate "upcoming events" listings.
 */

class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     * @return array<string,mixed> Attribute map for a default Event.
     * 
     * Ensures each event has an organiser, future start/end times, and
     * either in-person or online metadata. Pulls a preset for consistent
     * visuals (e.g., stable images) in seeded environments.
     */
    public function definition(): array
    {   
        //Event Pool for Title/Location/Image for consistent demo runs
        static $pool = null;
        if ($pool === null) {
            $pool = collect([
                ['title' => 'Brisbane Career Fair', 'location' => 'Brisbane', 'is_online' => false, 'image_path' => 'images/CareerFair.png'],
                ['title' => 'Sydney Music Live Night','location' => 'Sydney', 'is_online' => false, 'image_path' => 'images/MusicLive.png'],
                ['title' => 'Melbourne Networking Evening', 'location' => 'Melbourne', 'is_online' => false, 'image_path' => 'images/NetworkingEvent.png'],
                ['title' => 'Perth City Fun Run 10K', 'location' => 'Perth', 'is_online' => false, 'image_path' => 'images/RunningEvent.png'],
                ['title' => 'Darwin Social Mixer', 'location' => 'Darwin', 'is_online' => false, 'image_path' => 'images/SocialEvent.png'],
                ['title' => 'National Tech Webinar 2025', 'location' => 'Online', 'is_online' => true, 'image_path' => 'images/Webiner.png'],
                ['title' => 'Adelaide Wine & Cheese Night', 'location' => 'Adelaide', 'is_online' => false, 'image_path' => 'images/WineParty.png'],
                ['title' => 'Canberra Sunrise Yoga', 'location' => 'Canberra', 'is_online' => false, 'image_path' => 'images/YogaEvent.png'],
                ['title' => 'Gold Coast Yacht Party', 'location' => 'Gold Coast', 'is_online' => false, 'image_path' => 'images/YotParty.png'],
                ['title' => 'Griffith Career Expo', 'location' => 'Gold Coast', 'is_online' => false, 'image_path' => 'images/CareerFair.png'],
                ['title' => 'Wollongong Indie Music Live', 'location' => 'Wollongong', 'is_online' => false, 'image_path' => 'images/MusicLive.png'],
                ['title' => 'Geelong Startups Networking', 'location' => 'Geelong', 'is_online' => false, 'image_path' => 'images/NetworkingEvent.png'],
                ['title' => 'Australia-Wide Virtual Yoga Retreat', 'location' => 'Online', 'is_online' => true, 'image_path' => 'images/YogaEvent.png'],
                ['title' => 'Sunshine Coast Family Fun Run', 'location' => 'Sunshine Coast', 'is_online' => false, 'image_path' => 'images/RunningEvent.png'],
                ['title' => 'Townsville Community Social', 'location' => 'Townsville', 'is_online' => false, 'image_path' => 'images/SocialEvent.png'],
            ]);
        }

        // Pull the next preset; if the pool empties, cycle to keep factories resilient.
        $preset = $pool->shift();
        $title = $preset['title'];
        $isOnline = $preset['is_online'];

        // Ensure an organiser exists; prefer existing, otherwise create one on the fly.
        $organiserId = User::where('type', 'organiser')->inRandomOrder()->value('id')
            ?? User::factory()->organiser()->create()->id;

        // Schedule: random future start between 1–60 days, on the hour or half-hour, 1–4h duration.
        $start = now()->addDays(fake()->numberBetween(1, 60))
                      ->setTime(fake()->numberBetween(9, 20), [0,30][rand(0,1)]);
        $end   = (clone $start)->addHours(fake()->numberBetween(1, 4));

        return [
            'title'        => $title, 
            'description'  => fake()->paragraph(7, true),
            'organiser_id' => $organiserId,
            'starts_at'    => $start,
            'ends_at'      => $end,
            'is_online'    => $isOnline,
            'location'     => $preset['location'],
            'online_url'   => $isOnline ? fake()->url() : null,
            'capacity'     => fake()->numberBetween(10, 50),
            'price_cents'  => fake()->randomElement([0, 1500, 2500, 5000]),
            'currency'     => 'AUD',
            'image_path'   => $preset['image_path'],
        ];
    }
}
