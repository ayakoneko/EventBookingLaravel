@extends('.layouts.master')

@section('title')
    Create Event
@endsection

@section('content')
 <form method="POST" action="{{ route('events.store') }}">
    {{csrf_field()}}
    @include('events._form', ['event' => $event])
    <input type="submit" value="Create">
</form>
@endsection