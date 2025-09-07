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

  @forelse ($bookings as $booking)
    <ul class="list-unstyled mb-0">
      <li>
        @if ($booking->event)
          <a href="{{ route('events.show', $booking->event) }}">
            {{ $booking->event->title }}
          </a>
        @else
          <span class="text-muted">[Event no longer available]</span>
        @endif
      </li>
    </ul>
  @empty
    <p>You have not booked any events yet.</p>
  @endforelse

  @if (method_exists($bookings, 'links'))
    <div class="mt-3">{{ $bookings->links() }}</div>
  @endif
</div>
@endsection