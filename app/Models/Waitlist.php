<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id','user_id',
        'position',
        'notified_at','offer_expires_at',
        'offer_accepted'
    ];

    protected $casts = [
        'notified_at'      => 'datetime',
        'offer_expires_at' => 'datetime',
        'offer_accepted'   => 'boolean',
    ];
    
    public function event() { return $this->belongsTo(Event::class); }
    public function user() { return $this->belongsTo(User::class); }

}
