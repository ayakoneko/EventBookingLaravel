<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganiserActionsTest extends TestCase
{
    use RefreshDatabase;

    /* =========================== Paths ============================ */
    private const EVENT_RESOURCE_PATH = '/events';
    private const DASHBOARD_PATH      = '/organiser/dashboard';

    /* =========================== Helpers ========================== */

    /** Create an organiser */
    private function organiser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'name'     => 'Org ' . Str::random(6),
            'email'    => strtolower('org' . Str::random(10) . '@example.test'),
            'type'     => 'organiser',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    /** Create an attendee */
    private function attendee(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'name'     => 'Aten ' . Str::random(6),
            'email'    => strtolower('aten' . Str::random(10) . '@example.test'),
            'type'     => 'attendee',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    /** Create an Event directly (unguarded to avoid mass-assignment issues) */
    private function makeEvent(array $overrides = []): Event
    {
        $organiser = $overrides['organiser'] ?? $this->organiser();

        $base = [
            'title'        => 'Test Event ' . Str::random(5),
            'description'  => 'A test event',
            'starts_at'    => Carbon::now()->addDays(7),
            'ends_at'      => Carbon::now()->addDays(8),
            'location'     => 'Test Hall',
            'capacity'     => 30,
            'price_cents'  => 0,
            'is_online'    => false,
            'organiser_id' => $organiser->id,
        ];

        unset($overrides['organiser']);

        return Model::unguarded(function () use ($base, $overrides) {
            return Event::query()->create(array_merge($base, $overrides));
        });
    }

    /** A valid payload matching controller validation (Y-m-d\TH:i) */
    private function validEventPayload(array $overrides = []): array
    {
        $base = [
            'title'        => 'My Fresh Event',
            'description'  => 'Great event',
            'starts_at'    => Carbon::now()->addDays(3)->format('Y-m-d\TH:i'),
            'ends_at'      => Carbon::now()->addDays(4)->format('Y-m-d\TH:i'),
            'is_online'    => 1,
            'online_url'   => 'https://example.test/room',
            'location'     => null, // controller will set "Online" when is_online = true
            'capacity'     => 25,
            'price_cents'  => 0,
            'image_path'   => null,
        ];

        return array_merge($base, $overrides);
    }


    /* =========================== Tests ============================ */

    /** Requirement: An Organiser can log in and view their specific dashboard. */
    public function test_an_organiser_can_log_in_and_view_their_specific_dashboard(): void
    {
        $org = $this->organiser();

        $this->actingAs($org)
            ->get(self::DASHBOARD_PATH)
            ->assertOk()
            ->assertViewIs('dashboard.index')
            ->assertViewHasAll(['report', 'user'])
            ->assertSee(e($org->name));
    }

    /** Requirement: An Organiser can successfully create an event with valid data. */
    public function test_an_organiser_can_successfully_create_an_event_with_valid_data(): void
    {
        $org = $this->organiser();
        $payload = $this->validEventPayload();

        $resp = $this->actingAs($org)->post(self::EVENT_RESOURCE_PATH, $payload);

        $resp->assertRedirect();               // redirects to events.show
        $resp->assertSessionHasNoErrors();
        $resp->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'title'        => $payload['title'],
            'organiser_id' => $org->id,
            'capacity'     => $payload['capacity'],
            'price_cents'  => $payload['price_cents'],
            'is_online'    => 1,
            'location'    => 'Online',
        ]);
    }

    /** Requirement: An Organiser gets validation errors for invalid event data. */
    public function test_an_organiser_receives_validation_errors_for_invalid_event_data(): void
    {
        $org = $this->organiser();

        // invalid: empty title, past start, end before start, online without url
        $invalid = $this->validEventPayload([
            'title'      => '',
            'starts_at'  => Carbon::now()->subDay()->format('Y-m-d\TH:i'),
            'ends_at'    => Carbon::now()->subDays(2)->format('Y-m-d\TH:i'),
            'online_url' => '',
        ]);

        $resp = $this->actingAs($org)->post(self::EVENT_RESOURCE_PATH, $invalid);

        $resp->assertRedirect(); // back with errors

        // 1) Assert the fields that have errors
        $resp->assertSessionHasErrors(['title', 'starts_at', 'ends_at', 'online_url']);
        
        // 2) Read the MessageBag and assert the specific messages we care about
        $bag = session('errors')->getBag('default');
        
        $this->assertContains('The title field is required.', $bag->get('title'));
        $this->assertContains('The start time must be in the future.', $bag->get('starts_at'));
        $this->assertContains('The end time must be after the start time.', $bag->get('ends_at'));
        $this->assertContains('Online URL is required when the event is online.', $bag->get('online_url'));       
    }

    /** Requirement: An Organiser can successfully update an event they own. */
    public function test_an_organiser_can_successfully_update_an_event_they_own(): void
    {
        $org   = $this->organiser();
        $event = $this->makeEvent(['organiser' => $org]);

        $update = $this->validEventPayload([
            'title'      => 'Updated Title',
            'capacity'   => 50,
            'price_cents'=> 1500,
            'online_url' => 'https://example.test/new-room',
            'starts_at'  => Carbon::now()->addDays(5)->format('Y-m-d\TH:i'),
            'ends_at'    => Carbon::now()->addDays(6)->format('Y-m-d\TH:i'),
        ]);

        $resp = $this->actingAs($org)
            ->put(self::EVENT_RESOURCE_PATH . '/' . $event->id, $update);

        $resp->assertRedirect();
        $resp->assertSessionHasNoErrors();
        $resp->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'id'           => $event->id,
            'title'        => 'Updated Title',
            'capacity'     => 50,
            'price_cents'  => 1500,
            'organiser_id' => $org->id,
        ]);
    }

    /** Requirement: An Organiser cannot update an event created by another Organiser. */
    public function test_an_organiser_cannot_update_an_event_created_by_another_organiser(): void
    {
        $owner = $this->organiser();
        $other = $this->organiser();

        $event = $this->makeEvent(['organiser' => $owner]);

        $attempt = $this->validEventPayload(['title' => 'Illegal Update']);

        $this->actingAs($other)
            ->put(self::EVENT_RESOURCE_PATH . '/' . $event->id, $attempt)
            ->assertForbidden();

        $this->assertDatabaseMissing('events', [
            'id'    => $event->id,
            'title' => 'Illegal Update',
        ]);
    }

    /** Requirement: An Organiser can delete an event they own with no bookings. */
    public function test_an_organiser_can_delete_an_event_they_own_that_has_no_bookings(): void
    {
        $org   = $this->organiser();
        $event = $this->makeEvent(['organiser' => $org]);

        $resp = $this->actingAs($org)->delete(self::EVENT_RESOURCE_PATH . '/' . $event->id);

        $resp->assertRedirect();     // to events.index
        $resp->assertSessionHas('success');
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** Requirement: An Organiser cannot delete an event with active bookings. */
    public function test_an_organiser_cannot_delete_an_event_that_has_active_bookings(): void
    {
        $org    = $this->organiser();
        $event  = $this->makeEvent(['organiser' => $org]);

        // Add a booking (any booking blocks deletion per controller)
        $att = $this->attendee();

        Model::unguarded(function () use ($att, $event) {
            Booking::create([
                'user_id'  => $att->id,
                'event_id' => $event->id,
                'status'   => 'confirmed',
            ]);
        });

        $resp = $this->actingAs($org)->delete(self::EVENT_RESOURCE_PATH . '/' . $event->id);

        $resp->assertRedirect();           // back()
        $resp->assertSessionHas('error', 'This event has bookings and cannot be deleted.');
        $this->assertDatabaseHas('events', ['id' => $event->id]); // still exists
    
    }
}
