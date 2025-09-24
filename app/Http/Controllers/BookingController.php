<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;
use App\Models\Waitlist;     
use App\Mail\WaitlistMail; 

class BookingController extends Controller
{
    /**
     * List the authenticated user's bookings with event context.
     *
     * @param  Request $request Current HTTP request (for auth user).
     * @return View    Bookings index view with pagination.
     */
    public function index(Request $request)
    {
        $bookings = Booking::with('event') ->where('user_id', $request->user()->id) ->paginate(8); 
        return view('bookings.index', ['bookings' => $bookings]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Create/confirm a booking for the given event by the current user.
     *
     * Enforces uniqueness per (event,user) for confirmed bookings, respects capacity,
     * and honours active waitlist holds (offers) before general availability.
     *
     * @param  Request $request HTTP request (auth user inferred).
     * @param  Event   $event   Route-model-bound target event.
     * @return RedirectResponse Redirects to bookings list on success/failure with flash.     *
     * @throws ValidationException On duplicate confirmed booking.
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        // Duplicate Check 
        // Prevent duplicate confirmed bookings for the same event.
        $request->merge(['user_id' => $user->id]);
        $request->validate([
            'user_id' => [
                Rule::unique('bookings','user_id')
                    ->where(fn($q) => $q->where('event_id',$event->id)->where('status','confirmed')),
            ],
        ], ['user_id.unique' => 'You already booked this event.']);


        // Waitlist check 
        // Respect active waitlist offers: only the offeree may book during the hold window.
        $offer = $event->activeOffer();  // return only first non-expred offer
        $userIsOfferee = $offer && $offer->user_id === $user->id;

        // Capacity Check 
        // Capacity gate for everyone except the current offeree.
        if ($event->isFull() && !$userIsOfferee) {
            return back()->withErrors(['capacity' => 'Sorry, this event is full.']);
        }

        // Hold check
        // Seat is temporarily held for someone else.
        if ($offer && !$userIsOfferee) {
            return back()->withErrors([
                'capacity' => 'This seat is currently held for a waitlisted attendee until '
                    . $offer->offer_expires_at->format('D, M j, Y g:ia') . '.'
            ]);
        }

        // Reuse the event ID/user ID (cancelled and rebooked case)
        $booking = Booking::firstOrNew([
            'event_id'    => $event->id,
            'user_id'     => $user->id,
        ]);

        $booking->status = 'confirmed';
        $booking->cancelled_at = null;
        $booking->ticket_code = $booking->ticket_code ?: Str::upper(Str::random(8));
        $booking->save();

        // Remove the position if this person is coming from the waitlist
        $entry = Waitlist::where('event_id', $event->id)->where('user_id', $user->id)->first();
        if ($entry) {
            $pos = $entry->position;
            $entry->delete();
        
            Waitlist::where('event_id', $event->id) ->where('position', '>', $pos)->decrement('position');
        }

        return redirect()->route('bookings.index')->with('success', 'Booking confirmed!');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Cancel a booking and notify the next person on the waitlist (if any).
     *
     * Uses a short transaction for the status flip, then promotes the head
     * of the waitlist by sending a time-bound offer email.
     *
     * @param  Booking $booking Route-model-bound booking to cancel.
     * @return RedirectResponse Redirects back with flash message.
     */
    public function destroy(Booking $booking)
    {
        DB::transaction(function () use ($booking) {
            if ($booking->status === 'confirmed') {
                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            }
        });

        // Promote waitlist only if no active offer currently exists (Email Notification)
        $event = $booking->event;
        if (!$event->activeOffer()) {
            $first = Waitlist::where('event_id', $event->id) ->orderBy('position')->first();

            if ($first) {
                $first->update([
                    'notified_at' => now(),
                    'offer_expires_at' => now()->addHours(2)
                ]);    

                Mail::to($first->user->email)->send(new WaitlistMail($first));
            }
        }

        return back()->with('success', 'Booking cancelled.');    
    }

}