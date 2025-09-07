  @extends('.layouts.master')

  @section('title')
      Edit Event
  @endsection

  @section('content')
  <div class="container d-flex justify-content-center">
    <div class="card w-50 shadow-sm">
      <div class="card-body">
        <h2 class="card-title mb-4"> Edit Event</h2>

        <form method="POST" action="{{ route('events.update', $event) }}">
          {{csrf_field()}}
          {{ method_field('PUT') }}

          @include('events._form', ['event' => $event])

          <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary">Save Event</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endsection


