<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the waitlists table to queue interested attendees when events are full.
 *
 * Tracks a stable queue position, notification/offer windows, 
 * and the attendee’s response to an offer to purchase a vacated spot.
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();  // (PK)WaitlisttNum

            // Relationships: cascade to keep waitlist consistent with event/user lifecycles.
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // One waitlist entry per (event,user) ensures predictable queueing.
            $table->unique(['event_id', 'user_id']);

            // Queue mechanics
            $table->unsignedInteger('position');  
            
            // Offer workflow: record when notified, when the offer expires, and whether they accepted.
            $table->dateTime('notified_at')->nullable();
            $table->dateTime('offer_expires_at')->nullable();
            $table->boolean('offer_accepted')->default(false);
            $table->timestamps();

            // handy filters
            $table->index(['event_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
