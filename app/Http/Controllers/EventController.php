<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        return view('events.show')->with('event', $event);
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
