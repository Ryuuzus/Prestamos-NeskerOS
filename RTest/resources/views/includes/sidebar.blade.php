{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Barra lateral de navegación principal (Sidebar Component).
    Incluye la marca del sistema (NeskerOS) y la navegación condicional que despliega las opciones
    del Dashboard y los módulos de administración (Edificios, Aulas, Dispositivos y Excel) únicamente
    para usuarios con rol de administrador (`is_admin == 1`).
--================================================================================================== --}}

{{-- Contenedor principal de la barra lateral --}}
<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px; min-height: 100vh;">
    
    {{-- Logotipo y título de la aplicación --}}
    <h1 href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fa fa-boxes fs-4 me-2"></i>
        <span class="fs-5">NeskerOS</span>
    </h1>
    
    <hr>
    
    {{-- Menú de navegación principal --}}
    <ul class="nav nav-pills flex-column mb-auto">
        
        {{-- Enlace general: Dashboard / Solicitudes --}}
        <li>
            <a href="{{ route('reservations.index') }}" class="nav-link {{ request()->routeIs('reservations.*') ? 'active' : 'text-white' }}">
                <i class="fa fa-laptop fa-fw me-2"></i> Dashboard
            </a>
        </li>

        {{-- Menú exclusivo para Administradores --}}
        @if(auth()->check() && auth()->user()->is_admin == 1)
            
            <li class="mt-3 mb-1 px-3">
                <span class="text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 0.05em; font-weight: 600;">
                    Administración
                </span>
            </li>

            {{-- Módulo: Edificios --}}
            <li>
                <a href="{{ route('buildings.index') }}" class="nav-link {{ request()->routeIs('buildings.*') ? 'active' : 'text-white' }}">
                    <i class="fa fa-laptop fa-fw me-2"></i> Edificios
                </a>
            </li>

            {{-- Módulo: Aulas --}}
            <li>
                <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : 'text-white' }}">
                    <i class="fa fa-laptop fa-fw me-2"></i> Aulas
                </a>
            </li>

            {{-- Módulo: Dispositivos --}}
            <li>
                <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.index*') ? 'active' : 'text-white' }}">
                    <i class="fa fa-laptop fa-fw me-2"></i> Dispositivos
                </a>
            </li>

            {{-- Módulo: Exportación/Importación Excel --}}
            <li>
                <a href="{{ route('devices.excel') }}" class="nav-link {{ request()->routeIs('devices.excel') ? 'active' : 'text-white' }}">
                    <i class="fa fa-laptop fa-fw me-2"></i> Excel
                </a>
            </li>

        @endif

    </ul>
    
    <hr>
</div>