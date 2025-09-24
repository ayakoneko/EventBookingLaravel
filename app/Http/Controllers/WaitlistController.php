<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaitlistController extends Controller
{
    /**
     * List the authenticated user's waitlist entries with event context.
     *
     * @param  Request $request Current HTTP request (auth user inferred).
     * @return View    Waitlists index view with pagination.
     */
    public function index(Request $request)
    {
        $waitlists = Waitlist::with('event') ->where('user_id', $request->user()->id) ->paginate(8); 
        return view('waitlists.index', ['waitlists' => $waitlists]);
    }

    /**
     * Organiser-only: view the full waitlist for one of their events.
     *
     * Authorisation: organiser must own the event.
     *
     * @param  Request $request Current HTTP request (auth user inferred).
     * @param  Event   $event   Event whose waitlist to inspect.
     * @return View    Admin waitlist view with counts and queue.
     */
    public function admin(Request $request, Event $event)
    {
        //creator-only
        abort_unless($event->organiser_id === Auth::id(), 403);

        $event->loadCount(['confirmedBookings', 'waitlists']);
        $entries = $event->waitlists()->with('user') ->orderBy('position') ->paginate(8); 

        return view('waitlists.admin', ['event' => $event, 'entries' => $entries]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Join the waitlist for an event when seats are unavailable or held.
     *
     * Prevents join when seats are available (encourages immediate booking),
     * blocks duplicates and conflicts with existing confirmed bookings,
     * and assigns the next queue position.
     *
     * @param  Request $request Current HTTP request (auth user inferred).
     * @param  Event   $event   Event to join the waitlist for.
     * @return RedirectResponse Redirects back with outcome message.
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        //Full Booking Check (inc case seat is held for someone else)
        $offer = $event->activeOffer();
        $userIsOfferee = $offer && $offer->user_id === $user->id;
        $remaining = max(0, ($event->capacity ?? 0) - $event->confirmedBookings()->count());

        if (!($remaining === 0 || ($offer && !$userIsOfferee))) {
            return back()->withErrors(['waitlist' => 'Seats are currently available — you can book now.']);
        }

        //Confirmed Booking Exist Check
        if ($event->userHasConfirmedBooking($user->id)) {
            return back()->withErrors(['waitlist' => 'You already have a confirmed booking for this event.']);
        }

        //Current Waitlist Check 
        if ($event->waitlists()->where('user_id', $user->id)->exists()) {
            return back()->with('success', 'You are already on the waitlist.');
        }

        // Waitlist Position index 
        $nextPos = (int) $event->waitlists()->max('position') + 1;

        Waitlist::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'position' => $nextPos,
        ]);

        return back()->with('success', 'Joined the waitlist.');
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
     * Leave the waitlist for a given event and compact queue positions.
     *
     * Removes the current user's waitlist row and shifts all subsequent
     * entries up by one to keep the queue contiguous.
     *
     * @param  Request $request Current HTTP request (auth user inferred).
     * @param  Event   $event   Event to leave the waitlist for.
     * @return RedirectResponse Redirects back with success message.
     */
    public function destroy(Request $request, Event $event)
    {
        
        $userId = $request->user()->id;

        // Current user's waitlist entry and current position 
        $entry = Waitlist::where('event_id', $event->id)->where('user_id', $userId)->first();
        $pos = $entry->position;

        // 1. remove the row
        $entry->delete();

        // 2. compact positions: shift everyone behind up by 1
        Waitlist::where('event_id', $event->id)->where('position', '>', $pos)->decrement('position');

        return back()->with('success', 'Left waitlist.');

    }
}
