<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Event;
use App\Models\Booking;



class EventController extends Controller
{
    /**
     * List upcoming events in ascending start order with pagination.
     * @return View Rendered events index view.
     */
    public function index()
    {
        // $events = Event::all();
        $events = Event::orderBy('starts_at', 'asc')->paginate(8);
        return view('events.index')->with('events', $events);
    }

    /**
     * Show the event creation form.
     * @return View Rendered event creation view.
     */
    public function create()
    {
        $event = new Event();
        
        return view('events.create_form')->with('event', $event);
    }

    /**
     * Store a newly created event owned by the current organiser.
     *
     * Validates time window, format-specific fields (location/URL), and capacity,
     * then assigns organiser ownership to the authenticated user.
     *
     * @param  Request $request Incoming request with form data.
     * @return RedirectResponse Redirects to the event details on success.     *
     * @throws ValidationException On invalid input.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'starts_at'    => ['required', 'date', 'date_format:Y-m-d\TH:i', 'after:now'],
            'ends_at'      => ['nullable', 'date', 'date_format:Y-m-d\TH:i', 'after_or_equal:starts_at'],
            'is_online'    => ['required', 'in:0,1'],
            'location'     => ['required_if:is_online,0', 'nullable', 'string', 'max:255'],
            'online_url'   => ['required_if:is_online,1','nullable', 'url', 'max:255'],
            'capacity'     => ['required', 'integer', 'between:1,1000'],
            'price_cents'  => ['required', 'integer', 'min:0'],
            'image_path'   => ['nullable', 'string', 'max:2048'],
        ],
        [
            'starts_at.after'   => 'The start time must be in the future.',
            'ends_at.after_or_equal' => 'The end time must be after the start time.',
            'location.required_if'   => 'Location is required for in-person events.',
            'online_url.required_if' => 'Online URL is required when the event is online.',
        ]);
        
        // If online, normalise the location for consistent display/filtering.
        if ($request->boolean('is_online')) {
            $validated['location'] = 'Online';
        }

        // Ownership: created by the currently authenticated organiser.
        $validated['organiser_id'] = Auth::id(); 

        $event = Event::create($validated);
        return redirect()->route('events.show', $event) ->with('success', 'Event created.');
    }

    /**
     * Display the event details page.     *
     * @param  Event $event Route-model-bound event instance.
     * @return View  Rendered event details view.
     */
    public function show(Event $event)
    {
        return view('events.show')->with('event', $event);
    }

    /**
     * Show the edit form for an event the current user owns.
     *
     * Authorisation: organiser-only and owner-only.
     *
     * @param  Event $event Event to edit.
     * @return View  Rendered edit form view.
     */
    public function edit(Event $event)
    {
        //creator-only
        abort_unless($event->organiser_id === Auth::id(), 403);

        return view('events.edit_form')->with('event', $event);
    }

    /**
     * Update an existing event owned by the current organiser.
     * 
     * Authorisation: organiser-only and owner-only.
     * Enforces the same validation rules as creation; keeps ownership unchanged.
     *
     * @param  Request $request Incoming request with updated data.
     * @param  Event   $event   Event to update.
     * @return RedirectResponse Redirects to the event details on success.     *
     * @throws ValidationException On invalid input.
     */
    public function update(Request $request, Event $event)
    {
        //creator-only
        abort_unless($event->organiser_id === Auth::id(), 403);
        
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'starts_at'    => ['required', 'date', 'date_format:Y-m-d\TH:i', 'after:now'],
            'ends_at'      => ['nullable', 'date', 'date_format:Y-m-d\TH:i', 'after_or_equal:starts_at'],
            'is_online'    => ['required', 'in:0,1'],
            'location'     => ['required_if:is_online,0', 'nullable', 'string', 'max:255'],
            'online_url'   => ['required_if:is_online,1', 'nullable', 'url', 'max:255'],
            'capacity'     => ['required', 'integer', 'between:1,1000'],
            'price_cents'  => ['required', 'integer', 'min:0'],
            'image_path'   => ['nullable', 'string', 'max:2048'],
        ],
        [
            'starts_at.after'   => 'The start time must be in the future.',
            'ends_at.after'     => 'The end time must be after the start time.',
            'location.required_if'   => 'Location is required for in-person events.',
            'online_url.required_if' => 'Online URL is required when the event is online.',
        ]);

        if ($request->boolean('is_online')) {
            $validated['location'] = 'Online';
        }

        $event->update($validated);
        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    /**
     * Delete an event if it has no bookings and belongs to the current organiser.
     *
     * Authorisation: organiser-only and owner-only.
     * Business rule: events with any bookings are immutable to preserve audit history.
     *
     * @param  Event $event Event to delete.
     * @return RedirectResponse Redirects to events index with flash status.
     */
    public function destroy(Event $event)
    {
        //creator only
        abort_unless($event->organiser_id === Auth::id(), 403);
        
        // Block deletion when any bookings exist
        if ($event->bookings()->exists()) {
            return back()->with('error', 'This event has bookings and cannot be deleted.');
        }
        
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }
}
