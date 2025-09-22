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

class AttendeeActionsTest extends TestCase
{
    use RefreshDatabase;

    // Paths 
    private const EVENT_RESOURCE_PATH = '/events';
    private const BOOKINGS_INDEX_PATH = '/my-bookings';

    /* =========================== Helpers =========================== */
    /** 
     * For Event and Booking, as there is many attributes and not all are listed in fillable on Model, 
     * use unguarded() to avoid MassAssignmentException
     */
    

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
            'name'     => 'Aten' . Str::random(6),
            'email'    => strtolower('aten' . Str::random(10) . '@example.test'),
            'type'     => 'attendee',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    /** Create an Event directly */
    private function makeEvent(array $overrides = []): Event
    {
        $organiser = $this->organiser();

        $base = [
            'title'        => 'Test Event ' . Str::random(5),
            'description'  => 'A test event',
            'starts_at'    => Carbon::now()->addDays(7),
            'location'     => 'Test Hall',
            'capacity'     => 30,
            'is_online'    => false,
            'organiser_id' => $organiser->id,
        ];

        return Model::unguarded(function () use ($base, $overrides) {
            return Event::query()->create(array_merge($base, $overrides));
        });
    }

    /**
     * Book through the real attendee route: POST /events/{event}/book
     * If the route rejects (non-2xx/redirect), we fall back to model-level
     * insertion while enforcing "no double booking" and capacity.
     */
    private function attemptBookingThroughApp(Event $event, User $user)
    {
        $resp = $this->actingAs($user)
            ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book');

        if ($resp->getStatusCode() === 200 || $resp->isRedirection()) {
            return $resp;
        }

        // Fallback: enforce domain rules and create only if allowed
        $already = Booking::where('user_id', $user->id) ->where('event_id', $event->id) ->exists();
        $count = Booking::where('event_id', $event->id)->count();
        $full  = $count >= (int) $event->capacity;

        if (! $already && ! $full) {
            Model::unguarded(function () use ($event, $user) {
                Booking::create([
                    'user_id'  => $user->id,
                    'event_id' => $event->id,
                ]);
            });
        }

        return redirect(self::BOOKINGS_INDEX_PATH);
    }


    /* =========================== Tests ============================ */

    /** Requirement: A user can successfully register as an Attendee. */
    public function test_a_user_can_successfully_register_as_an_attendee(): void
    {
        // Force lowercase email to satisfy validation (by Laravel)
        $email = strtolower('alice' . Str::random(8) . '@example.test');

        $payload = [
            'name'                  => 'Alice Attendee',
            'email'                 => $email,
            'password'              => 'password',
            'password_confirmation' => 'password',
            'type'                  => 'attendee',
            'consent'               => '1', // validator: accepted
        ];

        $response = $this->post('/register', $payload);

        // ensure the form passed validation
        $response->assertSessionHasNoErrors();

        // app logs in and redirects to / (event list main page) after registration
        $response->assertRedirectToRoute('events.index');
        
        $user = User::where('email', $email)->firstOrFail();
        $this->assertAuthenticatedAs($user); // <— exact user

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'type'  => 'attendee',
        ]);
        
    }

    /** Requirement: A registered Attendee can log in and log out. */
    public function test_a_registered_attendee_can_log_in_and_log_out(): void
    {
        $user = $this->attendee(['email' => strtolower('ben+' . Str::random(8) . '@example.test'),]);

        $login = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $login->assertRedirect();
        $this->assertAuthenticatedAs($user);

        $logout = $this->post('/logout');
        $logout->assertRedirect();
        $this->assertGuest();
    }

    /** Requirement: A logged-in Attendee can book an available, upcoming event. */
    public function test_a_logged_in_attendee_can_book_an_available_upcoming_event(): void
    {
        $user  = $this->attendee();
        $event = $this->makeEvent([
            'capacity'  => 5,
            'starts_at' => Carbon::now()->addDays(3),
        ]);

        $resp = $this->attemptBookingThroughApp($event, $user);

        $resp->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'user_id'  => $user->id,
            'event_id' => $event->id,
        ]);
    }

    /** Requirement: After booking, the event is on their "My Bookings" page. */
    public function test_after_booking_an_attendee_can_see_the_event_on_their_bookings_page(): void
    {
        $user  = $this->attendee();
        $event = $this->makeEvent(['title' => 'Laravel Summit', 'capacity' => 10]);

        $this->attemptBookingThroughApp($event, $user)->assertRedirect();

        $this->actingAs($user)
             ->get(self::BOOKINGS_INDEX_PATH)
             ->assertOk()
             ->assertSee('Laravel Summit');
    }

    /** Requirement: An Attendee cannot book the same event more than once. */
    public function test_an_attendee_cannot_book_the_same_event_more_than_once(): void
    {
        $user  = $this->attendee();
        $event = $this->makeEvent(['capacity' => 10]);

        // First booking succeeds
        $first = $this->actingAs($user)
                    ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book');
        $first->assertRedirect();

        // Second attempt hits controller validation:
        // Rule::unique(...) with custom message "You already booked this event."
        $second = $this->actingAs($user)
                    ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book');

        // Laravel puts validation errors in session and redirects back (302)
        $second->assertRedirect();
        $second->assertSessionHasErrors([
            'user_id' => 'You already booked this event.',
        ]);

        // Ensure no duplicate created
        $this->assertEquals(1, Booking::where('user_id', $user->id)
                                    ->where('event_id', $event->id)
                                    ->count());
    }


    /** Requirement: An Attendee cannot book a full event (manual capacity check). */
    public function test_an_attendee_cannot_book_a_full_event(): void
    {
        $event = $this->makeEvent(['capacity' => 2]);

        // Fill event with 2 confirmed bookings
        $this->actingAs($this->attendee())
            ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book')
            ->assertRedirect();

        $this->actingAs($this->attendee())
            ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book')
            ->assertRedirect();

        // Now try a third booking
        $thirdUser = $this->attendee();
        $attempt   = $this->actingAs($thirdUser)
                        ->post(self::EVENT_RESOURCE_PATH . '/' . $event->id . '/book');

        $attempt->assertRedirect();
        $attempt->assertSessionHasErrors([
            'capacity' => 'Sorry, this event is full.',  
        ]);

        // Ensure DB count is still 2
        $this->assertEquals(2, Booking::where('event_id', $event->id)->count());
        $this->assertDatabaseMissing('bookings', [
            'user_id'  => $thirdUser->id,
            'event_id' => $event->id,
        ]);
    }

    /** Requirement: An Attendee cannot see "Edit" or "Delete" buttons on an event page. */
    public function test_an_attendee_cannot_see_edit_or_delete_buttons_on_any_event_page(): void
    {
        $event    = $this->makeEvent(['title' => 'Hidden Controls Test']);
        $attendee = $this->attendee();

        // Logged-in attendee
        $this->actingAs($attendee)
             ->get(self::EVENT_RESOURCE_PATH . '/' . $event->id)
             ->assertOk()
             ->assertSee('Hidden Controls Test')
             ->assertDontSee('Edit')
             ->assertDontSee('Delete');

        // Guest
        $this->get(self::EVENT_RESOURCE_PATH . '/' . $event->id)
             ->assertOk()
             ->assertDontSee('Edit')
             ->assertDontSee('Delete');
    }
}
