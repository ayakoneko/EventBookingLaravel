@extends('layouts.master')

@section('title')
  My Waitlists
@endsection

@section('content')
<div class="container py-4">
  <h2 class="mb-3">My Waitlists</h2>
  
  {{-- Surface the first validation/flow error if present --}}
  @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  @if ($waitlists->count() === 0)
    <p>You have no waiting events yet.</p>
  @else
    <ul class="list-group">
      @foreach ($waitlists as $waitlist)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <!-- Left: Event Details -->
          <div>
            <a href="{{ route('events.show', $waitlist->event) }}"> {{ $waitlist->event->title }} </a>
            <div class="text-muted">
              {{ $waitlist->event?->starts_at?->format('D, M j, Y g:ia') }}
              @if ($waitlist->event?->ends_at)
                - {{ $waitlist->event->ends_at->format('D, M j, Y g:ia') }}
              @endif
            </div>
            <div>
              <small>Location: {{ $waitlist->event->location }}</small>
            </div>            
          </div>
          <!-- Waitlist Details (Expiry and Position) -->
          <div>
            @if($waitlist->notified_at)
              Offer expires: {{ $waitlist->offer_expires_at->format('D, M j, Y g:ia')}}
            @else
              Position: {{ $waitlist->position }}
            @endif  
          </div>  
        </li>
      @endforeach
    </ul>
    
    <!-- for pagination -->
    <div class="mt-4">{{ $waitlists->links() }}</div> 
  @endif
</div>
@endsection
