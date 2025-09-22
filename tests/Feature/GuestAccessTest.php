<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrganiser(): User
    {
        // Registration creates attendees; set organiser explicitly for tests.
        $user = User::factory()->create();
        $user->type = 'organiser';
        $user->save();

        return $user;
    }

    private function eventAttrs(User $organiser, array $overrides = []): array
    {
        return array_merge([
            'organiser_id' => $organiser->id,
            'title'        => 'Sample Event',
            'description'  => null,
            'starts_at'    => Carbon::now()->addDays(1),
            'ends_at'      => Carbon::now()->addDays(1)->addHours(2),
            'is_online'    => false,
            'location'     => 'Main Hall',
            'online_url'   => null,
            'capacity'     => 10,
            'price_cents'  => 0,
            'currency'     => 'AUD',
            'image_path'   => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides);
    }

    /**
     * Requirement:
     * A guest can view the paginated list of upcoming events.
     */
    public function test_a_guest_can_view_the_paginated_list_of_upcoming_events(): void
    {
        $organiser = $this->makeOrganiser();

        // Create 12 future events; your controller paginates to 8 per page 
        $future = [];
        for ($i = 1; $i <= 12; $i++) {
            $future[] = Event::create($this->eventAttrs($organiser, [
                'title'     => "FUTURE #$i",
                'starts_at' => Carbon::now()->addDays($i),
                'ends_at'   => Carbon::now()->addDays($i)->addHours(2),
            ]));
        }
        $allFutureTitles = array_map(fn($e) => $e->title, $future);

        $response = $this->get(route('events.index'));
        $response->assertOk();

        // Capture the paginator from the view so we assert against the ACTUAL page 1 items.
        $page1 = null;
        $response->assertViewHas('events', function ($events) use (&$page1) {
            $page1 = $events;
            return $events instanceof \Illuminate\Pagination\LengthAwarePaginator
                && $events->perPage() === 8;
        });

        $page1Titles = $page1->pluck('title')->all();

        // Page 1 should show exactly those 8 titles
        foreach ($page1Titles as $title) {
            $response->assertSeeText($title);
        }

        // And should NOT show the others
        $otherTitles = array_values(array_diff($allFutureTitles, $page1Titles));
        foreach ($otherTitles as $title) {
            $response->assertDontSeeText($title);
        }
    }

    /**
     * Requirement:
     * A guest can view the details page for a specific event.
     */
    public function test_a_guest_can_view_a_specific_event_details_page(): void
    {
        $organiser = $this->makeOrganiser();

        $event = Event::create($this->eventAttrs($organiser, [
            'title'       => 'Public Tech Talk',
            'description' => 'A great talk about Laravel testing.',
            'starts_at'   => Carbon::now()->addDays(3),
            'ends_at'     => Carbon::now()->addDays(3)->addHours(2),
        ]));

        $response = $this->get(route('events.show', $event));
        $response->assertOk();
        $response->assertSeeText('Public Tech Talk');
        $response->assertSeeText('A great talk about Laravel testing.');
        $response->assertSeeText('Main Hall'); // location required in schema
    }

    /**
     * Requirement:
     * A guest is redirected to the login page for authenticated routes.
     */
    public function test_a_guest_is_redirected_when_accessing_protected_routes(): void
    {
        $organiser = $this->makeOrganiser();
        $event     = Event::create($this->eventAttrs($organiser));

        // Route-based checks (guarded by Route::has)
        $protected = [];

        if (Route::has('events.create')) {
            $protected[] = ['method' => 'get',  'uri' => route('events.create')];
        }
        if (Route::has('events.store')) {
            $protected[] = ['method' => 'post', 'uri' => route('events.store'), 'data' => []];
        }
        if (Route::has('events.edit')) {
            $protected[] = ['method' => 'get',  'uri' => route('events.edit', $event)];
        }
        if (Route::has('bookings.index')) {
            $protected[] = ['method' => 'get',  'uri' => route('bookings.index')];
        }
        if (Route::has('bookings.store')) {
            $protected[] = [
                'method' => 'post',
                'uri'    => route('bookings.store'),
                'data'   => ['event_id' => $event->id],
            ];
        }
        if (Route::has('waitlists.store')) {
            $protected[] = [
                'method' => 'post',
                'uri'    => route('waitlists.store'),
                'data'   => ['event_id' => $event->id],
            ];
        }

        if (empty($protected)) {
            $this->markTestSkipped('No protected routes found. Adjust route names to match routes/web.php.');
        }

        foreach ($protected as $r) {
            $method = strtolower($r['method']);
            $data   = $r['data'] ?? [];
            $resp   = $this->$method($r['uri'], $data);
            $resp->assertRedirect(route('login'));
        }

        // Navbar link checks on a public page
        $response = $this->get(route('events.index'));
        $response->assertOk();

        if (Route::has('events.create')) {
            $response->assertSeeText('Create Event');
            $response->assertSee('href="'.e(route('events.create')).'"', false);
            $this->get(route('events.create'))->assertRedirect(route('login'));
        }

        if (Route::has('bookings.index')) {
            $response->assertSeeText('My Bookings');
            $response->assertSee('href="'.e(route('bookings.index')).'"', false);
            $this->get(route('bookings.index'))->assertRedirect(route('login'));
        }

    }

    /**
     * Requirement:
     * A guest viewing an event details page cannot see action buttons.
     */
    public function test_a_guest_cannot_see_action_buttons_on_event_details_page(): void
    {
        $organiser = $this->makeOrganiser();

        $event = Event::create($this->eventAttrs($organiser, [
            'title' => 'Guest View Event',
        ]));

        $response = $this->get(route('events.show', $event));
        $response->assertOk();

        $notAllowed = [
             // Organiser-only
            'Edit',
            'Delete',
            'View Waitlist',

            // Attendee states
            'Already Booked',
            'Cancel',
            'Claim your seat',              // prefix (date varies)
            'Book Now',
            'Seat held for waitlist until', // prefix (date varies)
            'Join waitlist',
            "You're on the waitlist",
            "You’re on the waitlist",       // curly apostrophe in Blade
            'Leave Waitlist',
        ];

        foreach ($notAllowed as $text) {
            $response->assertDontSeeText($text);
        }
    }
}
