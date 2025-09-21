@extends('layouts.master')

@section('title')
  Waitlist — {{ $event->title }}
@endsection

@section('content')
<div class="container py-4">
  <h2 class="mb-3">Waitlist — {{ $event->title }}</h2>
  
  <div class="mb-3 text-muted">
    Capacity: {{ $event->capacity }} · Confirmed: {{ $event->confirmed_bookings_count }} · On waitlist: {{ $event->waitlists_count }}
  </div>

  @if($entries->count() === 0)
    <p>No one is on the waitlist yet.</p>
  @else
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Joined</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($entries as $row)
          <tr>
            <td>{{ $row->position }}</td>
            <td>{{ $row->user->name }}</td>
            <td>{{ $row->user->email }}</td>
            <td>{{ $row->created_at->format('D, M j, Y g:ia') }}</td>
            <td>
              @if($row->notified_at)
                Offered until {{ $row->offer_expires_at->format('D, M j, Y g:ia') }}
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>    
    <div class="mt-3">{{ $entries->links() }}</div> 
  @endif
</div>
@endsection
