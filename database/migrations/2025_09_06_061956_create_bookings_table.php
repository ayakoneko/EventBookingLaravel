<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the bookings table to record attendee registrations for events.
 *
 * Enforces one booking per (event,user) pair, captures cancellation lifecycle,
 * and stores a unique ticket code for verification/entry flows.
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();  // (PK)BookingNum

            // Relationships: restrict deleting an event with active bookings; user cascade cleans up orphan records.
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Prevent duplicate bookings by the same user for the same event.
            $table->unique(['event_id', 'user_id']);

            // Status lifecycle: 'confirmed' or 'cancelled'
            $table->string('status', 20)->default('confirmed'); // confirmed|cancelled
            $table->dateTime('cancelled_at')->nullable();

            // Ticketing: unique code used for check-in and receipt references.
            $table->string('ticket_code')->unique()->nullable();

            // Free-form notes (e.g., dietary/accessibility requirements).
            $table->text('notes')->nullable(); 
            
            $table->timestamps();

            // handy filters
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
