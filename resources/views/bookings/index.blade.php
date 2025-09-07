@extends('layouts.master')

@section('title')
  My Bookings
@endsection

@section('content')
  <h2>My Bookings</h2>
  @if ($bookings->isEmpty())
    <p>You have not booked any events yet.</p>
  @endif
@endsection