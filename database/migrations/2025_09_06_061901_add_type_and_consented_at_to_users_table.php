<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add role/consent metadata to the existing users table.
 *
 * Introduces a simple role flag (attendee/organiser) to drive authorization
 * and a timestamp recording when a user accepted the Terms/Privacy policy.
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role used for authorization checks: attendee (default) or organiser
            $table->string('type')->default('attendee')->after('password'); 

            // Records when the user explicitly accepted the terms and privacy policy (auditing & compliance)
            $table->dateTime('consented_at')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type', 'consented_at']);
        });
    }
};
