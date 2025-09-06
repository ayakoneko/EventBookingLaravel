@extends('layouts.master')

@section('title')
  Find Events
@endsection

@section('content')
<div class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Upcoming Events</h1>
  </div>

  <div class="row g-3">
    @foreach ($events as $event)
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="card h-100">
          <img src="{{ asset($event->image_path) }}"{{-- expects 'images/Filename.png' --}} class="event-list-img" alt="{{ $event->title }}">
            
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-2 text-truncate" title="{{ $event->title }}">{{ $event->title }}</h5>
            <div class="small text-muted">
              <div>{{ $event->starts_at->format('D, M j, Y g:ia') }}</div>
              <div class="text-truncate"> {{$event->location}}</div>
            </div>

            <a href="{{ url('event/'.$event->id) }}" class="stretched-link mt-auto">Details</a>

          </div>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-4">
    {{ $events->links() }}
</div>
</div>
@endsection