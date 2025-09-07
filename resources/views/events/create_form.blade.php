@extends('.layouts.master')

@section('title')
    Create Events
@endsection

@section('content')
 <form method="POST" action='{{url("event")}}'>
    {{csrf_field()}}
    @include('events._form', ['event' => $event])
    <input type="submit" value="Create">
</form>
@endsection