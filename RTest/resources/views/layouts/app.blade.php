<<<<<<< HEAD
=======
{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Plantilla base o maquetación principal del sistema (Main Application Layout).
    Define la estructura HTML5 global, metadatos, carga de activos CSS/JS mediante Vite y CDN de Bootstrap,
    e integra modularmente la barra lateral (`includes.sidebar`), encabezado (`includes.header`),
    área dinámica `@yield('content')` y pie de página (`includes.footer`).
--================================================================================================== --}}

>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

<<<<<<< HEAD
    <!-- CSRF Token -->
=======
    {{-- Token CSRF de seguridad para peticiones AJAX / POST --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

<<<<<<< HEAD
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 

    <!-- Bootstrap -->
=======
    {{-- Fuentes tipográficas externas --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 

    {{-- Bootstrap CSS CDN (Cargar primero para establecer la base) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Hojas de estilo personalizadas compiladas vía Vite (Tienen prioridad sobre Bootstrap) --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    @vite(['resources/css/crud/crud.css'])
    @vite(['resources/css/layouts/header.css'])
    @vite(['resources/css/layouts/sidebar.css'])
    @vite(['resources/css/excel/excel.css'])

<<<<<<< HEAD
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
=======
</head> 
<body style="background-color: #f4f7f6; font-family: 'Nunito', sans-serif;"> 
    
    {{-- Contenedor principal de la interfaz flexible --}}
    <div class="d-flex">
            
        {{-- Menú de navegación lateral / Sidebar --}}
        <aside class="main-sidebar">
            @include('includes.sidebar')
        </aside>

        {{-- Área principal de contenido general --}}
        <div class="flex-grow-1 bg-light d-flex flex-column" style="min-height: 100vh;">
            
            {{-- Barra superior de navegación / Header --}}
            @include('includes.header')
            
            {{-- Inyección de vistas hijas dinámicas con padding y margen interno --}}
            <main class="flex-grow-1 container-fluid p-4">
                @yield('content')
            </main>



        </div>
    </div>

    {{-- Librerías de JavaScript de Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body> 
{{-- Pie de página global  --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
<footer>
    @include('includes.footer')
</footer>
</html>