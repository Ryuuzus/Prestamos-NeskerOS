{{--
    Navegación de NeskerOS por rol.
    Los enlaces visibles NO sustituyen a las autorizaciones de routes/web.php.
--}}
@php
    $currentUser = auth()->user();
    $isUser = $currentUser?->hasRole(\App\Models\User::ROLE_USER) ?? false;
    $isModerator = $currentUser?->hasRole(\App\Models\User::ROLE_MODERATOR) ?? false;
    $canModerate = $currentUser?->can('review-reservations') ?? false;
    $isAdmin = $currentUser?->hasRole(\App\Models\User::ROLE_ADMIN) ?? false;

    $homeUrl = $canModerate ? route('admin.reservations.index') : route('reservations.index');
    $dashboardActive = $isUser && request()->routeIs('reservations.index', 'reservations.create', 'reservations.edit');
    $receiptsActive = request()->routeIs('receipts.*', 'pdf.ver', 'pdf.exportar');
@endphp

<div class="app-sidebar-inner">

    <a href="{{ $homeUrl }}" class="app-sidebar-brand mb-3">
        <img
            src="{{ asset('images/Genji.png') }}"
            alt="Logo NeskerOS"
            class="app-sidebar-logo"
        >
        <span class="fs-5">NeskerOS</span>
    </a>

    <hr>

    <ul class="nav nav-pills flex-column mb-auto">
        {{-- Agenda compartida: todos los roles pueden consultar disponibilidad ocupada. --}}
        <li class="mt-1 mb-1 px-3">
            <span class="app-sidebar-section text-uppercase small fw-bold">General</span>
        </li>
        <li>
            <a href="{{ route('agenda.index') }}" class="nav-link {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                <i class="fa fa-calendar-alt fa-fw me-2"></i> Agenda de préstamos
            </a>
        </li>

        {{-- Menú personal: SOLO el rol user puede crear y administrar sus reservas. --}}
        @if($isUser)
        <li class="mt-1 mb-1 px-3">
            <span class="app-sidebar-section text-uppercase small fw-bold">Usuario</span>
        </li>
        <li>
            <a href="{{ route('reservations.index') }}" class="nav-link {{ $dashboardActive ? 'active' : '' }}">
                <i class="fa fa-chart-line fa-fw me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('receipts.index') }}" class="nav-link {{ $receiptsActive ? 'active' : '' }}">
                <i class="fa fa-file-invoice fa-fw me-2"></i> Mis recibos
            </a>
        </li>
        @endif

        {{-- Trabajo de solicitudes: moderadores y administradores --}}
        @if($canModerate)
            <li class="mt-3 mb-1 px-3">
                <span class="text-uppercase text-secondary small fw-bold" style="letter-spacing: .05em;">Moderación</span>
            </li>
            <li>
                <a href="{{ route('admin.reservations.index') }}" class="nav-link {{ request()->routeIs('admin.reservations.index') ? 'active' : '' }}">
                    <i class="fa fa-clipboard-check fa-fw me-2"></i> Gestionar solicitudes
                </a>
            </li>
            @can('view-history')
                <li>
                    <a href="{{ route('reservations.history') }}" class="nav-link {{ request()->routeIs('reservations.history') ? 'active' : '' }}">
                        <i class="fa fa-history fa-fw me-2"></i> Historial
                    </a>
                </li>
            @endcan

            {{-- El moderador puede consultar los catálogos, sin modificarlos. --}}
            @if($isModerator)
                <li>
                    <a href="{{ route('buildings.index') }}" class="nav-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}">
                        <i class="fa fa-building fa-fw me-2"></i> Ver edificios
                    </a>
                </li>
                <li>
                    <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
                        <i class="fa fa-door-open fa-fw me-2"></i> Ver aulas
                    </a>
                </li>
                <li>
                    <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.index', 'devices.show') ? 'active' : '' }}">
                        <i class="fa fa-microchip fa-fw me-2"></i> Ver dispositivos
                    </a>
                </li>
            @endif
        @endif

        {{-- Configuración y manejo de recursos: únicamente administrador --}}
        @if($isAdmin)
            <li class="mt-3 mb-1 px-3">
                <span class="app-sidebar-section text-uppercase small fw-bold">Administración</span>
            </li>
            <li>
                <a href="{{ route('buildings.index') }}" class="nav-link {{ request()->routeIs('buildings.*') ? 'active' : '' }}">
                    <i class="fa fa-building fa-fw me-2"></i> Edificios
                </a>
            </li>
            <li>
                <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">
                    <i class="fa fa-door-open fa-fw me-2"></i> Aulas
                </a>
            </li>
            <li>
                <a href="{{ route('devices.index') }}" class="nav-link {{ request()->routeIs('devices.index', 'devices.show') ? 'active' : '' }}">
                    <i class="fa fa-microchip fa-fw me-2"></i> Dispositivos
                </a>
            </li>
            @can('export-data')
                <li>
                    <a href="{{ route('reports.excel') }}" class="nav-link {{ request()->routeIs('reports.excel', 'devices.excel', 'buildings.excel', 'classrooms.excel') ? 'active' : '' }}">
                        <i class="fa fa-file-excel fa-fw me-2"></i> Excel
                    </a>
                </li>
            @endcan
            @can('manage-users')
                <li>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fa fa-users-cog fa-fw me-2"></i> Usuarios y roles
                    </a>
                </li>
            @endcan
        @endif
    </ul>
    <hr>
</div>
