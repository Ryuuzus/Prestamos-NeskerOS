{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Plantilla base o maquetación principal del sistema (Main Application Layout).
    Define la estructura HTML5 global, metadatos, carga de activos CSS/JS mediante Vite y CDN de Bootstrap,
    e integra modularmente la barra lateral (`includes.sidebar`), encabezado (`includes.header`),
    área dinámica `@yield('content')` y pie de página (`includes.footer`).
--================================================================================================== --}}

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Token CSRF de seguridad para peticiones AJAX / POST --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fuentes tipográficas externas --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 

    {{-- 1. Bootstrap CSS CDN (Cargar primero para establecer la base) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- 2. Hojas de estilo personalizadas compiladas vía Vite (Tienen prioridad sobre Bootstrap) --}}
    @vite(['resources/css/crud/crud.css'])
    @vite(['resources/css/layouts/header.css'])
    @vite(['resources/css/layouts/sidebar.css'])
    @vite(['resources/css/excel/excel.css'])

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

            {{-- Pie de página global integrado dentro del contenedor dinámico --}}
            <footer>
                @include('includes.footer')
            </footer>

        </div>
    </div>

    {{-- Librerías de JavaScript de Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>