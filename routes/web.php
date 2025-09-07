<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsOrganiser;
use App\Http\Middleware\EnsureUserIsAttendee;

// Public: event list (home) and detail
Route::get('/', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Authorized Organiser Only
Route::middleware(['auth', 'organiser'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/organiser/dashboard', fn() => view('organiser.dashboard'))->name('organiser.dashboard');
});

// Authorized Attendee Only
Route::middleware(['auth', 'attendee'])->group(function () {
    // Route::get('/my-bookings', [BookingController::class, 'index'])->name('bookings.index');
});

require __DIR__.'/auth.php';
