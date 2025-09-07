  @extends('.layouts.master')

  @section('title')
      Edit Event
  @endsection

  @section('content')
  <form method="POST" action="{{ route('events.update', $event) }}">
    {{csrf_field()}}
    {{ method_field('PUT') }}
    @include('events._form', ['event' => $event])
    
    <input type="submit" value="Update">
  </form>
  @endsection