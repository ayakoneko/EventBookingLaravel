@extends('layouts.master')

@section('title')
  My Bookings
@endsection

@section('content')
<div class="container py-4">
  <h2 class="mb-3">My Bookings</h2>
  
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  @if ($bookings->isEmpty())
    <p>You have not booked any events yet.</p>
  @else
    <ul class="list-group">
      @foreach ($bookings as $booking)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <a href="{{ route('events.show', $booking->event) }}"> {{ $booking->event->title }} </a>
            <div class="text-muted">
              {{ $booking->event->starts_at->format('D, d M Y H:i') }}
            </div>
            @if($booking->ticket_code)
              <small>Ticket: {{ $booking->ticket_code }}</small>
            @endif
          </div>
          <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}"> {{ ucfirst($booking->status) }} </span>
        </li>
      @endforeach
    </ul>
    <div class="mt-3">{{ $bookings->links() }}</div>
  @endif
@endsection
