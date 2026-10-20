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

    {{-- Bootstrap CSS CDN (Cargar primero para establecer la base) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Hojas de estilo personalizadas compiladas vía Vite (Tienen prioridad sobre Bootstrap) --}}

    @vite(['resources/css/global.css'])
    @vite(['resources/css/crud/crud.css'])
    @vite(['resources/css/layouts/header.css'])
    @vite(['resources/css/layouts/sidebar.css'])
    @vite(['resources/css/excel/excel.css'])
    @stack('styles')

</head>
<body>
    
    {{-- Contenedor principal de la interfaz flexible --}}
    <div class="app-shell">
            
        {{-- Menú de navegación lateral / Sidebar --}}
        <aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarTitle">
            <div class="offcanvas-header d-lg-none border-bottom border-secondary-subtle">
                <span id="appSidebarTitle" class="text-white fw-bold">NeskerOS</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
            </div>
            <div class="offcanvas-body">
                @include('components.sidebar')
            </div>
        </aside>

        {{-- Área principal de contenido general --}}
        <div class="app-main">
            
            {{-- Barra superior de navegación / Header --}}
            @include('includes.header')
            
            {{-- Inyección de vistas hijas dinámicas con padding y margen interno --}}
            <main class="flex-grow-1 container-fluid p-4">
                @yield('content')
            </main>

            @include('includes.footer')

        </div>
    </div>

    {{-- Librerías de JavaScript de Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>