<?php

namespace Tests\Feature;

use App\Mail\WaitlistMail;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    /* =========================== Route helpers =========================== */

    private function routeEventShow(Event $event): string
    {
        return route('events.show', $event);
    }
    private function routeWaitlistsIndex(): string
    {
        return route('waitlists.index');
    }
    private function routeWaitlistsAdmin(Event $event): string
    {
        return route('waitlists.admin', $event);
    }
    private function routeWaitlistsJoin(Event $event): string
    {
        return route('waitlists.join', $event);
    }
    private function routeWaitlistDestroy(Event $event): string
    {
        // note: singular in blade/routes
        return route('waitlist.destroy', $event);
    }
    private function routeBook(Event $event): string
    {
        return route('events.book', $event);
    }
    private function routeCancel(Booking $booking): string
    {
        return route('bookings.destroy', $booking);
    }

    /* =========================== Helpers ========================== */

    private function organiser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'name'     => 'Org ' . Str::random(6),
            'email'    => strtolower('org' . Str::random(10) . '@example.test'),
            'type'     => 'organiser',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    private function attendee(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'name'     => 'Aten' . Str::random(6),
            'email'    => strtolower('aten' . Str::random(10) . '@example.test'),
            'type'     => 'attendee',
            'password' => Hash::make('password'),
        ], $attrs));
    }

    private function makeEvent(array $overrides = []): Event
    {
        $organiser = $overrides['organiser_id'] ?? $this->organiser()->id;

        $base = [
            'title'        => 'Test Event ' . Str::random(5),
            'description'  => 'A test event',
            'starts_at'    => Carbon::now()->addDays(7),
            'location'     => 'Test Hall',
            'capacity'     => 1,
            'is_online'    => false,
            'organiser_id' => $organiser,
        ];

        return Model::unguarded(function () use ($base, $overrides) {
            return Event::query()->create(array_merge($base, $overrides));
        });
    }

    private function book(Event $event, User $user)
    {
        return $this->actingAs($user)->post($this->routeBook($event));
    }

    private function cancel(Booking $booking, User $user)
    {
        return $this->actingAs($user)->delete($this->routeCancel($booking));
    }

    private function joinWaitlist(Event $event, User $user)
    {
        return $this->actingAs($user)->post($this->routeWaitlistsJoin($event));
    }

    private function leaveWaitlist(Event $event, User $user)
    {
        return $this->actingAs($user)->delete($this->routeWaitlistDestroy($event));
    }

    /* =========================== Core MUSTs ======================= */

    public function test_join_waitlist_button_shows_only_when_event_is_full(): void
    {
        $event = $this->makeEvent(['capacity' => 1, 'title' => 'Full House']);
        $u1 = $this->attendee(); // fills it
        $u2 = $this->attendee(); // sees waitlist

        $this->book($event, $u1)->assertRedirect();

        $this->actingAs($u2)
            ->get($this->routeEventShow($event))
            ->assertOk()
            ->assertSee('Join waitlist');
    }

    public function test_attendee_can_join_waitlist_on_full_event(): void
    {
        $event = $this->makeEvent(['capacity' => 1]);
        $booked = $this->attendee();
        $waiter = $this->attendee();

        $this->book($event, $booked)->assertRedirect();

        $resp = $this->joinWaitlist($event, $waiter);
        $resp->assertRedirect();
        $resp->assertSessionHas('success', 'Joined the waitlist.');

        $this->assertDatabaseHas('waitlists', [
            'event_id' => $event->id,
            'user_id'  => $waiter->id,
            'position' => 1,
        ]);
    }

    public function test_attendee_can_view_and_leave_a_waitlist(): void
    {
        $event = $this->makeEvent(['capacity' => 0]); // full by definition
        $u = $this->attendee();

        $this->joinWaitlist($event, $u)->assertRedirect();

        $this->actingAs($u)
            ->get($this->routeWaitlistsIndex())
            ->assertOk()
            ->assertSee($event->title);

        $this->leaveWaitlist($event, $u)->assertRedirect();
        $this->assertDatabaseMissing('waitlists', [
            'event_id' => $event->id,
            'user_id'  => $u->id,
        ]);
    }

    public function test_organiser_can_view_waitlist_for_their_event(): void
    {
        $org   = $this->organiser();
        $event = $this->makeEvent(['organiser_id' => $org->id, 'capacity' => 1]);

        $booked = $this->attendee();
        $a = $this->attendee();
        $b = $this->attendee();

        $this->book($event, $booked)->assertRedirect();
        $this->joinWaitlist($event, $a)->assertRedirect();
        $this->joinWaitlist($event, $b)->assertRedirect();

        $this->actingAs($org)
            ->get($this->routeWaitlistsAdmin($event))
            ->assertOk()
            ->assertSee($a->name)
            ->assertSee($b->name);
    }

    public function test_non_organiser_cannot_view_waitlist_admin(): void
    {
        $org   = $this->organiser();
        $other = $this->organiser(); // not the organiser of this event
        $event = $this->makeEvent(['organiser_id' => $org->id, 'capacity' => 0]);

        $this->actingAs($other)
            ->get($this->routeWaitlistsAdmin($event))
            ->assertStatus(403);
    }

    public function test_cannot_join_waitlist_when_seats_available_and_no_offer(): void
    {
        $event = $this->makeEvent(['capacity' => 2]);
        $u = $this->attendee();

        $this->actingAs($u)
            ->post($this->routeWaitlistsJoin($event))
            ->assertRedirect()
            ->assertSessionHasErrors(['waitlist' => 'Seats are currently available — you can book now.']);

        $this->assertDatabaseMissing('waitlists', [
            'event_id' => $event->id,
            'user_id'  => $u->id,
        ]);
    }

    public function test_cannot_join_waitlist_if_already_booked(): void
    {
        // Make the event FULL so the controller hits the "already booked" branch
        $event = $this->makeEvent(['capacity' => 1]);
        $u = $this->attendee();

        // user books the only seat
        $this->book($event, $u)->assertRedirect();

        // now trying to join waitlist must yield "already booked" (your controller check)
        $this->actingAs($u)
            ->post($this->routeWaitlistsJoin($event))
            ->assertRedirect()
            ->assertSessionHasErrors(['waitlist' => 'You already have a confirmed booking for this event.']);
    }

    public function test_joining_the_same_waitlist_twice_is_idempotent(): void
    {
        $event = $this->makeEvent(['capacity' => 0]);
        $u = $this->attendee();

        $this->joinWaitlist($event, $u)->assertRedirect();
        $this->actingAs($u)
            ->post($this->routeWaitlistsJoin($event))
            ->assertRedirect()
            ->assertSessionHas('success', 'You are already on the waitlist.');

        $this->assertEquals(1, Waitlist::where('event_id', $event->id)
            ->where('user_id', $u->id)->count());
    }

    public function test_leaving_waitlist_compacts_positions(): void
    {
        $event = $this->makeEvent(['capacity' => 0]);
        [$u1, $u2, $u3] = [$this->attendee(), $this->attendee(), $this->attendee()];

        $this->joinWaitlist($event, $u1); // pos 1
        $this->joinWaitlist($event, $u2); // pos 2
        $this->joinWaitlist($event, $u3); // pos 3

        $this->leaveWaitlist($event, $u2)->assertRedirect();

        $this->assertDatabaseHas('waitlists', ['event_id' => $event->id, 'user_id' => $u1->id, 'position' => 1]);
        $this->assertDatabaseHas('waitlists', ['event_id' => $event->id, 'user_id' => $u3->id, 'position' => 2]);
        $this->assertDatabaseMissing('waitlists', ['event_id' => $event->id, 'user_id' => $u2->id]);
    }

    /* ======================= Mandatory Excellence: Mail & Offer Window ======================= */

    public function test_cancelling_confirmed_booking_notifies_first_waitlisted_and_sets_offer_window(): void
    {
        Mail::fake();

        $event = $this->makeEvent(['capacity' => 1]);
        $bookedUser = $this->attendee();
        $first = $this->attendee();
        $second = $this->attendee();

        $this->book($event, $bookedUser)->assertRedirect();
        $this->joinWaitlist($event, $first)->assertRedirect();   // pos 1
        $this->joinWaitlist($event, $second)->assertRedirect();  // pos 2

        $booking = Booking::where('event_id', $event->id)
            ->where('user_id', $bookedUser->id)->firstOrFail();

        $this->cancel($booking, $bookedUser)->assertRedirect();

        $firstEntry = Waitlist::where('event_id', $event->id)
            ->where('user_id', $first->id)->firstOrFail();

        $this->assertNotNull($firstEntry->notified_at);
        $this->assertNotNull($firstEntry->offer_expires_at);
        $this->assertTrue($firstEntry->offer_expires_at->isAfter(now()));

        Mail::assertSent(WaitlistMail::class, function (WaitlistMail $m) use ($first, $event) {
            $to = collect($m->to ?? [])->pluck('address')->all();
            $subject = method_exists($m, 'envelope') ? $m->envelope()->subject : '';
            return in_array($first->email, $to, true)
                && str_contains($subject, $event->title);
        });

        Mail::assertNotSent(WaitlistMail::class, function (WaitlistMail $m) use ($second) {
            $to = collect($m->to ?? [])->pluck('address')->all();
            return in_array($second->email, $to, true);
        });
    }

    /* ======================= Offer Gating: only offeree can book while active ======================= */

    public function test_only_offeree_can_book_while_offer_active_everyone_else_blocked(): void
    {
        $event  = $this->makeEvent(['capacity' => 1]);
        $booked = $this->attendee();
        $first  = $this->attendee(); // offeree
        $other  = $this->attendee();

        // Setup: full + waitlist
        $this->book($event, $booked)->assertRedirect();
        $this->joinWaitlist($event, $first)->assertRedirect();

        // Cancel -> creates 2h offer for $first
        $booking = Booking::where('event_id', $event->id)
            ->where('user_id', $booked->id)->firstOrFail();
        $this->cancel($booking, $booked)->assertRedirect();

        // Non-offeree is blocked with "seat held" message
        $resp = $this->actingAs($other)->post($this->routeBook($event))->assertRedirect();
        $resp->assertSessionHasErrors('capacity');
        $this->assertStringContainsString(
            'held for a waitlisted attendee',
            session('errors')->first('capacity')
        );

        // Offeree can book
        $this->book($event, $first)->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'user_id'  => $first->id,
            'status'   => 'confirmed',
        ]);

        // Offeree removed from waitlist
        $this->assertDatabaseMissing('waitlists', [
            'event_id' => $event->id,
            'user_id'  => $first->id,
        ]);
    }

    public function test_after_offer_expiry_others_can_book_normally(): void
    {
        $event  = $this->makeEvent(['capacity' => 1]);
        $booked = $this->attendee();
        $first  = $this->attendee(); // will let it expire
        $other  = $this->attendee();

        $this->book($event, $booked)->assertRedirect();
        $this->joinWaitlist($event, $first)->assertRedirect();

        $booking = Booking::where('event_id', $event->id)
            ->where('user_id', $booked->id)->firstOrFail();
        $this->cancel($booking, $booked)->assertRedirect();

        $entry = Waitlist::where('event_id', $event->id)
            ->where('user_id', $first->id)->firstOrFail();
        $this->assertNotNull($entry->offer_expires_at);

        // Jump past expiry (+2h set in controller)
        Carbon::setTestNow($entry->offer_expires_at->copy()->addMinute());

        // With no active offer, anyone can book
        $this->book($event, $other)->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'event_id' => $event->id,
            'user_id'  => $other->id,
            'status'   => 'confirmed',
        ]);

        Carbon::setTestNow(); // reset
    }

    public function test_user_cannot_double_book_same_event(): void
    {
        $event = $this->makeEvent(['capacity' => 2]);
        $u = $this->attendee();

        $this->book($event, $u)->assertRedirect();

        $this->actingAs($u)
            ->post($this->routeBook($event))
            ->assertRedirect()
            ->assertSessionHasErrors(['user_id' => 'You already booked this event.']);
    }
}
