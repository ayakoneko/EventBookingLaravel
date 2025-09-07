@extends('layouts.master')

@section('title')
  Dashboard - {{$userID}}
@endsection

@section('content')
<div class="container py-4">
  <h2 class="mb-3">Organizer Dashboard</h2>
  
  <table class="table table-striped mt-3">
    <thead>
      <tr>
        <th>Event Title</th>
        <th>Date</th>
        <th>Capacity</th>
        <th>Bookings</th>
        <th>Remaining</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($report as $row)
        <tr>
          <td>{{ $row->title }}</td>
          <td>{{ \Carbon\Carbon::parse($row->starts_at)->format('M d, Y g:ia') }}</td>
          <td>{{ $row->capacity }}</td>
          <td>{{ $row->booking_count }}</td>
          <td>{{ $row->remaining }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="5">No events found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  
@endsection
