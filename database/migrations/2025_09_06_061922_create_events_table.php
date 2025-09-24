<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the events table to store publishable event listings.
 *
 * Captures event details, scheduling, location/online metadata, capacity, pricing, image path and
 * the organiser relationship for access control and filtering.
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Event details
            $table->string('title', 100);
            $table->text('description')->nullable();

            // Ownership: allows organiser-only CRUD and scoped dashboards.
            $table->foreignId('organiser_id')->constrained('users')->cascadeOnDelete();

            // Scheduling
            $table->dateTime('starts_at'); // future date only
            $table->dateTime('ends_at')->nullable(); //must be equal or after starts_at

            // Venue: supports in-person, online, or hybrid patterns
            $table->boolean('is_online')->default(0);
            $table->string('location', 255);
            $table->string('online_url')->nullable();

            // Capacity & pricing: 
            $table->unsignedSmallInteger('capacity');    //min 1, max 1000       
            $table->unsignedInteger('price_cents')->default(0); //stored in cents to avoid float issues
            $table->char('currency', 3)->default('AUD');
            
            // Image: relative path (for seeded events) or URL string (for event created via app form); rendering layer decides how to resolve.
            $table->string('image_path', 255)->nullable(); 
            $table->timestamps();

            // handy filters
            $table->index('starts_at');
            $table->index(['organiser_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
