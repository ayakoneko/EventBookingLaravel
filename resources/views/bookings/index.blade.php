@extends('layouts.master')

@section('title')
  My Bookings
@endsection

@section('content')
<div class="container py-4">
  <a href="{{ url('/') }}" class="btn btn-link px-0 mb-3">&larr; Back to Events List</a>
  <h2 class="mb-3">My Bookings</h2>
  
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  @if ($bookings->count() === 0)
    <p>You have not booked any events yet.</p>
  @else
    <ul class="list-group">
      @foreach ($bookings as $booking)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <!-- Lef: Event details -->
          <div>
            <a href="{{ route('events.show', $booking->event) }}"> {{ $booking->event->title }} </a>
            <div class="text-muted">
              {{ $booking->event?->starts_at?->format('D, M j, Y g:ia') }}
              @if ($booking->event?->ends_at)
                - {{ $booking->event->ends_at->format('D, M j, Y g:ia') }}
              @endif
            </div>
            <div>
              <small>Location: {{ $booking->event->location }}</small>
            </div>            
          </div>
          <!-- Right: Ticket Code and Booking Status -->
          <div>
          @if($booking->ticket_code)
            <small>Ticket: {{ $booking->ticket_code }}</small>
          @endif
          <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}"> {{ ucfirst($booking->status) }} </span>
          </div>  
        </li>
      @endforeach
    </ul>
    
    <!-- for pagination -->
    <div class="mt-4">{{ $bookings->links() }}</div> 
  @endif
</div>
@endsection
