<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title . ' - ' . config('app.name') ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/regular.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/solid.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/brands.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/ayro-ui/starter.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/ayro-ui/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/responsive-sizing-bootstrap.min.css') }}">
</head>
<body>
    @include('components.layouts.public.navbar')
    {{ $slot }}
    @include('components.layouts.public.footer')
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>