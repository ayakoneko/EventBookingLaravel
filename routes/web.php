<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsOrganiser;
use App\Http\Middleware\EnsureUserIsAttendee;

// Public: event list (home) and detail
Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->whereNumber('event')->name('events.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('/policy', 'auth.policy')->name('policy');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Authorized Organiser Only
Route::middleware(['auth', 'organiser'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->whereNumber('event')->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->whereNumber('event')->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->whereNumber('event')->name('events.destroy');
    Route::get('/organiser/dashboard',[DashboardController::class, 'index'])->name('organiser.dashboard');
});

// Authorized Attendee Only
Route::middleware(['auth', 'attendee'])->group(function () {
    Route::post('/events/{event}/book', [BookingController::class, 'store'])->name('events.book');
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/events/{event}/waitlist', [WaitlistController::class, 'store'])->whereNumber('event')->name('waitlists.join');
    Route::get('/my-waitlist', [WaitlistController::class, 'index'])->name('waitlists.index');
    Route::delete('/events/{event}/waitlist', [WaitlistController::class, 'destroy'])->whereNumber('event')->name('waitlist.destroy');
});

require __DIR__.'/auth.php';
