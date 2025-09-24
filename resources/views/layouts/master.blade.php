<!DOCTYPE html>
<html>
  <head>
    <title>@yield('title')</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/wp.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  </head>
  
  <body>    
    @include('layouts.navigation')

    {{-- Flash messages (success/error feedback from controllers) --}}
    <div class="container mt-3">
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>

    {{-- main content --}}
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
