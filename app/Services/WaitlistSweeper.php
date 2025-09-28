<?php

namespace App\Services;

use App\Mail\WaitlistMail;
use App\Models\Event;
use App\Models\Waitlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WaitlistSweeper
{
    /**
     * Sweep one event:
     * - Expire the headwaitlist offer if it’s overdue
     * - Promote the next user if there is capacity
     */
    public function sweepEvent(int $eventId): void
    {
        $event = Event::find($eventId);
        if (!$event) {
            return;
        }

        // Find expired offer. 
        // If exist update to null. And move to last waitlist poition and decrement other position
        $expired = Waitlist::where('event_id', $event->id)
            ->whereNotNull('notified_at') ->whereNotNull('offer_expires_at') ->where('offer_expires_at', '<=', now()) ->orderBy('position') ->first();
        if (!$expired) return;

        $oldPos = $expired->position;
        $maxPos = (int) Waitlist::where('event_id', $event->id)->max('position');
        
        $expired->update([
            'position'       => $maxPos + 1,
            'notified_at'      => null,
            'offer_expires_at' => null,         
        ]);
        
        Waitlist::where('event_id', $event->id)->where('position', '>', $oldPos)->decrement('position');   

        // Check for capacity 
        if ($event->isFull()) {
            return;
        }

        // Promote next person with email notification
        $next = Waitlist::where('event_id', $event->id) ->whereNull('notified_at') ->orderBy('position') ->first();

        if ($next) {
            $next->update([
                'notified_at'      => now(),
                'offer_expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($next->user->email)->send(new WaitlistMail($next));
        }
    }

    /**
     * Sweep all events that currently have overdue offers.
     */
    public function sweepAllOverdue(): void
    {
        $eventIds = Waitlist::query()
            ->whereNotNull('notified_at') ->whereNotNull('offer_expires_at') ->where('offer_expires_at', '<=', now()) ->distinct() ->pluck('event_id');

        foreach ($eventIds as $id) {
            $this->sweepEvent((int)$id);
        }
    }

}