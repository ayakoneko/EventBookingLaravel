{{-- resources/views/layouts/partials/bootstrap-nav.blade.php --}}
<nav class="navbar navbar-expand-lg bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="{{ route('events.index') }}">EventBooking</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        @guest {{-- will redirect to login if guest --}}
          <li class="nav-item">
            <a class="nav-link" href="{{ route('events.create') }}">Create Event</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('bookings.index') }}">Find my tickets</a> {{--tickets.find--}}
          </li>
        @endguest

        @auth
          @if(auth()->user()->type === 'organiser')
            <li class="nav-item">
              <a class="nav-link" href="{{ route('events.create') }}">Create Event</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('organiser.dashboard') }}">Event Dashboard</a>
            </li>
          @else
            <li class="nav-item">
              <a class="nav-link" href="{{ route('bookings.index') }}">My Bookings</a>
            </li>
          @endif
        @endauth
      </ul>

      <ul class="navbar-nav ms-auto">
        @guest
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log in</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
        @endguest

        @auth
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button">
              {{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item" type="submit">Log Out</button>
                </form>
              </li>
            </ul>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>