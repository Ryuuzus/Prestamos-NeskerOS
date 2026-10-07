{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Encabezado principal de la aplicación (Header Component).
    Proporciona navegación superior, desplegable de perfil de usuario autenticado con avatar
    dinámico generado por la inicial del nombre, datos del usuario y formulario de cierre de sesión.
--================================================================================================== --}}

{{-- Barra de navegación superior --}}
<header class="bg-white shadow-sm laboc-header">
    <div class="container-fluid px-4 py-3 d-flex justify-content-end align-items-center">

        {{-- Menú desplegable con información del usuario autenticado --}}
        <div class="user-profile dropdown">
            
            {{-- Botón activador del menú de perfil --}}
            <button type="button" class="btn btn-light d-flex align-items-center gap-2 btn-profile-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                {{-- Inicial del usuario en tamaño pequeño --}}
                <div class="avatar-sm">
                    {{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}
                </div>
                
                <span class="fw-semibold text-dark">{{ Auth::user()->name }}</span>
                <i class="fa fa-chevron-down text-muted ms-1 chevron-icon"></i>
            </button>

            {{-- Contenido del menú desplegable del usuario --}}
            <div class="dropdown-menu dropdown-menu-end profile-dropdown-menu p-4 text-center">
                
                {{-- Avatar ampliado e información del usuario --}}
                <div class="avatar-lg mx-auto mb-3">
                    {{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}
                </div>
                <h5 class="fw-bold mb-1 profile-name">{{ Auth::user()->name }}</h5>
                <p class="text-muted small mb-4">{{ Auth::user()->email }}</p>
                
                {{-- Botón para cerrar sesión mediante JavaScript --}}
                <div class="d-grid gap-3">
                    <a href="{{ route('logout') }}" class="btn btn-logout fw-semibold d-flex align-items-center justify-content-center gap-2"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
                
                {{-- Formulario oculto POST para la ruta de logout --}}
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

            </div>
        </div>

    </div>
</header>