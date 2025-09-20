@extends('layouts.master')

@section('title')
  Dashboard - {{ $user->name }}
@endsection

@section('content')
<div class="container py-4">
  <h2 class="mb-3">Organizer Dashboard - ({{ count($report) }} events)</h2>
  
  <table class="table table-striped mt-3">
    <thead>
      <tr>
        <th>Event Title</th>
        <th>Date (Start at) </th>
        <th>Capacity</th>
        <th>Bookings</th>
        <th>Remaining</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($report as $row)
        <tr>
          <td><a href="{{ route('events.show', $row->id) }}">{{ $row->title }}</a></td>
          <td>{{ $row->starts_at}}</td>
          <td>{{ $row->capacity }}</td>
          <td>{{ $row->booking }}</td>
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
