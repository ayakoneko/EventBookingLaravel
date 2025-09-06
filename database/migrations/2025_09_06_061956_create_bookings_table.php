<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();  // (PK)TicketNum
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['event_id', 'user_id']);

            $table->string('status', 20)->default('confirmed'); // confirmed|cancelled|waitlisted
            $table->dateTime('cancelled_at')->nullable();
            $table->string('ticket_code')->unique()->nullable();
            $table->text('notes')->nullable(); // dietary preference, etc.
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
