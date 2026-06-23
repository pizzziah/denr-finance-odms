<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'DENR Finance OBMS')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@500;600;700;800&display=swap"
          rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/DENR-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet"
        href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="app-wrapper d-flex min-vh-100 p-0 m-0">
        @include('layouts.sidebar')

        <div class="main-wrapper flex-grow-1 d-flex flex-column">
            @include('layouts.header')

            <main class="main-content flex-grow-1 p-5 pt-0">
                @yield('content')
            </main>

            @include('layouts.footer')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>