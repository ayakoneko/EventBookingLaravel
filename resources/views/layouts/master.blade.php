<!DOCTYPE html>
<html>
  <head>
    <title>@yield('title')</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/wp.css') }}" type="text/css">
  </head>
  
  <body>
  <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
      <div class="container">
        <a class="navbar-brand fw-bold" href="{{url('/')}}">EventBooking</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
          {{-- Left side --}}
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            @auth
              @if(auth()->user()->type === 'organiser')
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('events.create') ? 'active' : '' }}" 
                     href="{{url('/')}}">Create Event</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                     href="{{url('/')}}">Event Dashboard</a>
                </li>
              @else 
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('bookings.index') ? 'active' : '' }}" 
                     href="{{url('/')}}">My Bookings</a>
                </li>
              @endif
            @endauth
          </ul>

          {{-- Right side --}}
          <ul class="navbar-nav ms-auto">
            @guest
              <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log in</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
            @endguest

            @auth
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form method="POST" action="{{url('/logout')}}">
                      @csrf
                      <button type="submit" class="dropdown-item">Log Out</button>
                    </form>
                  </li>
                </ul>
              </li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
