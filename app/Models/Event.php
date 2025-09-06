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

    protected $casts = ['starts_at' => 'datetime','ends_at' => 'datetime'];
    
    public function organiser() { return $this->belongsTo(User::class, 'organiser_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
      
}
