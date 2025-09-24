<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * The Event model captures schedule, venue, capacity and pricing,
 * and ties each event to an organiser for ownership and access control.
 */

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organiser_id',
        'title', 'description',
        'starts_at','ends_at',
        'location', 'is_online','online_url',
        'capacity',
        'price_cents','currency',
        'image_path','organiser_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_online' => 'boolean'
    ];
    
    /**
     * Relationship:
     * Organiser who owns/manages this event 
     * One-to-many of event bookings
     * One-to-many ordered waitlist
     */
    public function organiser() { return $this->belongsTo(User::class, 'organiser_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function waitlists() { return $this->hasMany(Waitlist::class)->orderBy('position'); }


    /**
     *Helper for Confirmed Booking
    * 1. Query filtered to 'confirmed'.
    * 2. Bool True when confirmed bookings >= capacity.
    * 3. Bool True if the user has a confirmed booking for this event.
    * 4. The user's waitlist row or null if absent.
    * 5. The top active offer or null if none.
    */

    public function confirmedBookings() {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function isFull(): bool {
        return $this->confirmedBookings()->count() >= (int)$this->capacity;
    }

    public function userHasConfirmedBooking(?int $userId): bool {
        if (!$userId) return false;
        return $this->confirmedBookings()->where('user_id', $userId)->exists();
    }

    public function userWaitlistEntry(?int $userId): ?Waitlist {
        if (!$userId) return null;
        return $this->waitlists()->where('user_id', $userId)->first();
    }

    public function activeOffer(): ?Waitlist{
        return $this->waitlists() ->whereNotNull('notified_at') ->where('offer_expires_at', '>', now()) ->orderBy('position') ->first();
    }

}
