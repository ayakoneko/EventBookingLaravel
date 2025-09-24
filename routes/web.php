<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsOrganiser;
use App\Http\Middleware\EnsureUserIsAttendee;

/**
 * Route Overview: 
 * - Public discovery: view events list & details and policies
 * - Authenticated user features: profile, bookings, waitlists.
 * - Role-gated features: organisers manage events and see dashboards; attendees book/join waitlists.
 */

// Public: event list (home) and detail
Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');

    // Legal pages surfaced during registration/consent. 
Route::view('/terms', 'auth.terms')->name('terms');
Route::view('/policy', 'auth.policy')->name('policy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * Authorized Users (Both Organizer and Attendee) - Profiles
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Authorized Organiser Only - event CRUD, organiser dashboard, waitlist admin
*/
Route::middleware(['auth', 'organiser'])->group(function () {
    // Event authoring lifecycle
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->whereNumber('event')->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->whereNumber('event')->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->whereNumber('event')->name('events.destroy');

    // Dashboard for owned events
    Route::get('/organiser/dashboard',[DashboardController::class, 'index'])->name('organiser.dashboard');

    // Waitlist management for the queue for a specific event you own
    Route::get('/events/{event}/waitlist/admin', [WaitlistController::class, 'admin'])->whereNumber('event')->name('waitlists.admin');
});

/**
 * Authorized Attendee Only — booking & personal waitlist
*/
Route::middleware(['auth', 'attendee'])->group(function () {
    // Book a seat 
    Route::post('/events/{event}/book', [BookingController::class, 'store'])->name('events.book');
    // Cancel booking (may trigger next waitlist offer)
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    // My bookings (with pagination)
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');

    // Join/leave waitlist
    Route::post('/events/{event}/waitlist', [WaitlistController::class, 'store'])->whereNumber('event')->name('waitlists.join');
    Route::get('/my-waitlist', [WaitlistController::class, 'index'])->name('waitlists.index');
    Route::delete('/events/{event}/waitlist', [WaitlistController::class, 'destroy'])->whereNumber('event')->name('waitlists.destroy');
});

require __DIR__.'/auth.php';
