<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $pageTitle }}</title>
    @vite('resources/saas/app.scss')
</head>
<body>
    @include('layout.nav')
    @yield('content')
    @vite('resources/js/app.scss')
</body>
</html>
