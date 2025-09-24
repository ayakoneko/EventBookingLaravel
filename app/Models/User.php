<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * The User model represents both organisers and attendees.
 *
 * Authorisation is role-driven via the 'type' attribute; relationships expose
 * owned events and participation in bookings/waitlists.
 */

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Relationship:
     * One-to-many of events the user owns (Organizer ID)
     * One-to-many of the user's bookings
     * One-to-many of the user's waitlists
     */
    public function events() { return $this->hasMany(Event::class, 'organiser_id');}
    public function bookings() { return $this->hasMany(Booking::class); }
    public function waitlists() { return $this->hasMany(Waitlist::class); }


    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
