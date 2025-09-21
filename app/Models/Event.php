<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'image_path',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_online' => 'boolean'
    ];
    
    public function organiser() { return $this->belongsTo(User::class, 'organiser_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function waitlists() { return $this->hasMany(Waitlist::class)->orderBy('position'); }

    //Helper for Confirmed Booking
    //1. if booking is confirmed
    //2. if the event is full of booking
    //3. user already have a confirmed booking for the event
    //4. user’s waitlist row/model (can query later)

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
}
