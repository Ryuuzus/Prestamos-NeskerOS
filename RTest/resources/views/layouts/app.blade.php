<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 

    <!-- Bootstrap -->
    @vite(['resources/css/crud/crud.css'])
    @vite(['resources/css/layouts/header.css'])
    @vite(['resources/css/layouts/sidebar.css'])
    @vite(['resources/css/excel/excel.css'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head> 
<body style="background-color: #f4f7f6; font-family: 'Nunito', sans-serif;"> 
        <div class="d-flex">
                
                <aside class="main-sidebar">
                    @include('includes.sidebar')
                </aside>


                <!-- Área principal de contenido -->
                <div class="flex-grow-1 bg-light" style="min-height: 100vh; overflow-y: auto;">
                    
                    <!-- barra superior de navegación / Navbar -->
                    @include('includes.header')
                    <main>
                        @yield('content')
                    </main>

                </div>
            </div>

        <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body> 
<footer>
    @include('includes.footer')
</footer>
</html>