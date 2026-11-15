<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0F5132">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
