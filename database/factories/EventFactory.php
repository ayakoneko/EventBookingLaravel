<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $organiserId = User::where('type', 'organiser')->inRandomOrder()->value('id')
            ?? User::factory()->organiser()->create()->id;

        $title = fake()->unique()->sentence(3);

        $start = now()->addDays(fake()->numberBetween(1, 60))
                      ->setTime(fake()->numberBetween(9, 20), [0,30][rand(0,1)]);
        
        $end   = (clone $start)->addHours(fake()->numberBetween(1, 4));

        return [
            'title'        => $title, 
            'description'  => fake()->paragraph(),
            'organiser_id' => $organiserId,
            'starts_at'    => $start,
            'ends_at'      => $end,
            'location'     => fake()->randomElement(['Online', fake()->city()]),
            'is_online'    => false,
            'online_url'   => null,
            'capacity'     => fake()->numberBetween(20, 200),
            'price_cents'  => fake()->randomElement([0, 1500, 2500, 5000]),
            'currency'     => 'AUD',
            'image_path'   => null,
            'slug'         => Str::slug($title) . '-' . Str::lower(Str::random(6)),
        ];
    }
}
