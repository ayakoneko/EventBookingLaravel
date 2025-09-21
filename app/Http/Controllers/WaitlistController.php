<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaitlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $waitlists = Waitlist::with('event') ->where('user_id', $request->user()->id) ->paginate(8); 
        return view('waitlists.index', ['waitlists' => $waitlists]);
    }

    // organizer only - view all the attendees on the waitlsit for any of their specific events
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        //Full Booking Check 
        if (!$event->isFull()) {
            return back()->withErrors(['waitlist' => 'Event is not full — you can book now.']);
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
     * Remove the specified resource from storage.
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
