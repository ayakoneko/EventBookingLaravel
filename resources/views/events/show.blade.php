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
        $user        = auth()->user();
        $userId      = auth()->id();  

        // seats
        $confirmed = $event->confirmedBookings()->count();
        $remaining = max(0, ($event->capacity ?? 0) - $confirmed);

        // current user's booking for this event (if any)
        $userBooking  = $user ? $event->bookings()->where('user_id', $user->id)->latest()->first() : null;
        $isConfirmed  = $userBooking && $userBooking->status === 'confirmed';
        $isCancelled  = $userBooking && $userBooking->status === 'cancelled';

        //waitlist and offer
        $offer         = $event->activeOffer(); 
        $userIsOfferee = $offer && $offer->user_id === $userId;
        $myWaitlist    = $event->userWaitlistEntry($userId);
      @endphp

      <div class="row g-3">
        <!-- Event Details (Time, Location, Capacity, Organizer) -->
        <div class="col-md-6">
          <div class="mb-2">
            <strong>When:</strong>
              {{ $event->starts_at->format('D, M j, Y g:ia') }} 
              @if ($event->ends_at)
                - {{ $event->ends_at->format('D, M j, Y g:ia') }}
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
            <strong>Capacity:</strong> 
              {{$event->capacity}}
          </div>

          <div class="mb-2">
            <strong>Organizer:</strong> 
              {{($event->organiser)->name}}
          </div>
        </div>

        <!-- Badges and Buttons -->
        <div class="col-md-6 text-end">
          <div class="d-inline-block">
            <!-- Price Badge -->
            <span class="badge bg-light text-dark fs-5">
              @if($event->price_cents == 0)
                Free
              @else
                {{$event->currency}} {{number_format(($event->price_cents)/100, 2)}}
              @endif
            </span>

            <!-- Remaining Seat Badge with text effects -->
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

          @auth
          <!-- Event's Organizer Only (3 actions buttons) -->
            @if(auth()->user()->type === 'organiser' && auth()->id() === optional($event->organiser)->id)
              <div class="d-flex gap-2 justify-content-end mb-3">
                <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-primary"> Edit </a>

                <form method="POST" action="{{ route('events.destroy', $event) }}"
                      onsubmit="return confirm('Delete this event? This cannot be undone.');">
                    {{csrf_field()}}
                    {{ method_field('DELETE') }}
                  <button type="submit" class="btn btn-sm btn-outline-danger"> Delete </button>
                </form>
              </div>

              <div>
                <a href="{{ route('waitlists.admin', $event) }}" class="btn btn-outline-secondary btn-sm">View Waitlist</a>
              </div>
         
          <!-- Attendee Only (Book/Cancel/Waitlist buttons) -->
            @elseif (auth()->user()->type === 'attendee')    
              <!-- 1. User already booked -->
              @if ($isConfirmed)
                <button class="btn btn-secondary disabled" disabled>Already Booked</button>
                <form method="POST" action="{{ route('bookings.destroy', $userBooking) }}"
                      onsubmit="return confirm('Cancel your booking?\nIf the event has a waitlist, your seat will be offered to the next person and you may not be able to rebook.');">
                    {{csrf_field()}}
                    {{ method_field('DELETE') }}
                  <button class="btn btn-outline-danger btn-sm">Cancel</button>
                </form>
                        
              <!-- 2. Active offer for user >> Clain seat-->
              @elseif ($offer && $userIsOfferee)                
                <form method="POST" action="{{ route('events.book', $event) }}">
                  {{csrf_field()}}      
                  <button type="submit" class="btn btn-primary"> Claim your seat (held until {{ $offer->offer_expires_at->format('D, M j, Y g:ia') }}) </button>
                </form>  
              
              <!-- 3. No active offer OR offer is for someone else-->
              @else
                <!-- 3a. Seats available and NO active offer >> normal booking-->
                @if ($remaining > 0 && !$offer)                  
                  @if ($isCancelled)
                    <button class="btn btn-secondary disabled" disabled>Cancelled</button>
                  @endif
                  <form method="POST" action="{{ route('events.book', $event) }}">
                    {{csrf_field()}}      
                    <button type="submit" class="btn btn-primary">Book Now</button>
                  </form>

                <!-- 3b. Seats visible but offer is for someone else >> hold + waitlist-->
                @elseif ($remaining > 0 && $offer && !$userIsOfferee)
                  <div class="mb-2">
                    <button class="btn btn-secondary" disabled>
                      Seat held for waitlist until {{ $offer->offer_expires_at->format('D, M j, Y g:ia') }}
                    </button>
                  </div>
                  @if(!$myWaitlist)
                    <form method="POST" action="{{ route('waitlists.join', $event) }}">
                      @csrf
                      <button class="btn btn-outline-primary">Join waitlist</button>
                    </form>
                  @else
                    <button class="btn btn-secondary" disabled>
                      You're on the waitlist (pos #{{ $myWaitlist->position }})
                    </button>
                    <form method="POST" action="{{ route('waitlists.destroy', $event) }}"
                        onsubmit="return confirm('Leave the waitlist? Your wait position will be reset.');">
                      {{csrf_field()}}
                      {{ method_field('DELETE') }}
                    <button type="submit" class="btn btn-sm btn-outline-danger"> Leave Waitlist </button>
                  </form>
                  @endif
                  
                <!-- 3c. No seats (full) >> normal waitlist-->
                @else
                  @if(!$myWaitlist)
                    <form method="POST" action="{{ route('waitlists.join', $event) }}">
                      @csrf
                      <button class="btn btn-outline-primary">Join waitlist</button>
                    </form>
                  @else
                    <button class="btn btn-secondary" disabled>
                      You're on the waitlist (pos #{{ $myWaitlist->position }})
                    </button>
                    <form method="POST" action="{{ route('waitlists.destroy', $event) }}"
                      onsubmit="return confirm('Leave the waitlist? Your wait position will be reset.');">
                      {{csrf_field()}}
                      {{ method_field('DELETE') }}
                      <button type="submit" class="btn btn-sm btn-danger"> Leave Waitlist </button>
                    </form>
                  @endif    
                @endif     
              @endif
            @endif
          @endauth
        </div>
      </div>
    </div>

    @if(!empty($event->description))
      <hr>
      <div class="mt-2 px-4">
        <strong>Event Description:</strong><br>
        <p class="ms-4">{{ $event->description }}</p>
      </div>
     @endif
  </div>
</div>
@endsection