<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $events = Event::all();
        $events=Event::paginate(8);
        return view('events.index')->with('events', $events);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $event = (object)[
            'title'       => null,
            'description' => null,
            'starts_at'   => null,
            'ends_at'     => null,
            'location'    => null,
            'is_online'   => 0,
            'online_url'  => null,
            'capacity'    => null,
            'price_cents' => 0,
            'image_path'  => null,
        ];
        
        return view('events.create_form')->with('event', $event);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'starts_at'    => ['required', 'date', 'after:now'],
            'ends_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location'     => ['required', 'string', 'max:255'],
            'is_online'    => ['required', 'in:0,1'],
            'online_url'   => ['nullable', 'string', 'max:255'],
            'capacity'     => ['required', 'integer', 'between:1,1000'],
            'price_cents'  => ['required', 'integer', 'min:0'],
            'image_path'   => ['nullable', 'string', 'max:255'],
        ]);
        $validated['organiser_id'] = Auth::id(); // owner = current user

        $event = Event::create($validated);
        return redirect()->route('events.show', $event) ->with('success', 'Event created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('events.edit_form')->with('event', $event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'starts_at'    => ['required', 'date', 'after:now'],
            'ends_at'      => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location'     => ['required', 'string', 'max:255'],
            'is_online'    => ['required', 'in:0,1'],
            'online_url'   => ['nullable', 'string', 'max:255'],
            'capacity'     => ['required', 'integer', 'between:1,1000'],
            'price_cents'  => ['required', 'integer', 'min:0'],
            'image_path'   => ['nullable', 'string', 'max:255'],
        ]);
        
        $event->update($validated);
        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }
}
