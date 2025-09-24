<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A Booking represents a user's registration for an event, with a simple
 * lifecycle ('confirmed' or 'cancelled') and optional ticket code/notes.
 */

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id','user_id',
        'status','cancelled_at',
        'ticket_code',
        'notes'
    ];
    
    protected $casts = ['cancelled_at' => 'datetime'];
    
    /**
     * Relationship: 
     * Event this booking belongs to.
     * User who owns this booking.
     */
    public function event() { return $this->belongsTo(Event::class); }
    public function user() { return $this->belongsTo(User::class); }

}
