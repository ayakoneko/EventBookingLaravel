<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $user = $request->user();

        //Duplicate Check 
        $already = Booking::where('event_id', $event->id)->where('user_id', $user->id)->exists();
        if ($already) {
            return back()->withErrors(['booking' => 'You already booked this event.']);
        }

        //Capacity Check 
        $confirmedCount = Booking::where('event_id', $event->id)->where('status', 'confirmed')->count();
        if ($confirmedCount >= $event->capacity) {
            return back()->withErrors(['capacity' => 'Sorry, this event is full.']);
        }

        Booking::create([
            'event_id'    => $event->id,
            'user_id'     => $user->id,
            'status'      => 'confirmed',
            'ticket_code' => Str::upper(Str::random(8)),
        ]);

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
