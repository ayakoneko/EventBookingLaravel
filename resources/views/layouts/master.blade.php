<!DOCTYPE html>
<html>
  <head>
    <title>@yield('title')</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/wp.css') }}" type="text/css">
  </head>
  <body>
    @auth     <!--- user is logged in --->
      {{Auth::user()->name}}
      <form method="POST" action= "{{url('/logout')}}">
        {{csrf_field()}}
        <input type="submit" value="Logout">
      </form>
    @else <!--- user is not logged in --->
      <a href="{{route('login')}}"> Log in</a>
      <a href="{{route('register')}}"> register</a>
    @endauth

    @yield('content')
  </body>
</html>
