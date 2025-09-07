@extends('layouts.master')

@section('title')
  {{ $event->title }}
@endsection

@section('content')
<div class="container py-4">
  <a href="{{ url('/') }}" class="btn btn-link px-0 mb-3">&larr; Back to Events List</a>

  <div class="card">
    <img src="{{ asset($event->image_path) }}"{{-- expects 'images/Filename.png' --}} class="event-detail-img" alt="{{ $event->title }}">
    
    <div class="card-body">
      <h3>{{ $event->title }}</h3>

      @php
        $confirmed = $event->bookings()->where('status', 'confirmed')->count();
        $remaining = max(0, ($event->capacity ?? 0) - $confirmed);
      @endphp

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <div class="mb-2">
            <strong>When:</strong>
              {{ $event->starts_at->format('D, M j, Y g:ia') }} 
              @if ($event->ends_at)
                – {{ $event->ends_at->format('D, M j, Y g:ia') }}
              @endif
          </div>

          <div class="mb-2">
            <strong>Where:</strong>
            {{$event->location}}
              @if(!empty($event->online_url))
                (<a href="{{ $event->online_url }}" target="_blank" rel="noopener">Join Link</a>)
              @endif
          </div>

          <div class="mb-2">
            <strong>Organizer:</strong> 
              {{($event->organiser)->name}}
          </div>
        </div>

        <div class="col-md-6 text-end">
          <div class="d-inline-block">
            <span class="badge bg-light text-dark fs-5">
              @if($event->price_cents == 0)
                Free
              @else
                {{$event->currency}} {{number_format(($event->price_cents)/100, 2)}}
              @endif
            </span>

            <span class="fs-6 {{ $remaining === 0 ? 'text-danger' : ($remaining <= 5 ? 'text-warning' : 'text-muted') }}">
              @if($remaining === 0)
                Full
              @elseif($remaining <= 5)
                Almost full ({{ $remaining }} left)
              @else
                {{ $remaining }} seats left
              @endif
            </span>
          </div>
        </div>
      </div>
    </div>

    @if(!empty($event->description))
      <hr>
      <div class="mt-2">
      <strong>Event Description:</strong></br>
          {{$event->description}}
      </div>
     @endif
  </div>
</div>
@endsection