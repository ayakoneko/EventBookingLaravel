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
    //3. user’s waitlist position(row)  
    //4. user already have a confirmed booking for the event

    public function confirmedBookings() {
        return $this->bookings()->where('status', 'confirmed');
    }

    public function isFull(): bool {
        return $this->confirmedBookings()->count() >= (int)$this->capacity;
    }
}
