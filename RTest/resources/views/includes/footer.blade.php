{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Componente de pie de página global de la aplicación (Footer Component).
    Muestra los derechos de autor con el año dinámico actual, la marca NeskerOS y el
    reconocimiento del proyecto universitario de la Universidad de Guadalajara.
--================================================================================================== --}}

{{-- Pie de página global --}}
<footer class="bg-white border-top py-3 mt-auto" style="box-shadow: 0 -2px 10px rgba(0,0,0,0.02);">
    <div class="container-fluid d-flex flex-column flex-md-row justify-content-between align-items-center px-4">
        
        {{-- Derechos de autor y nombre del sistema con año dinámico --}}
        <span class="text-muted" style="font-size: 0.9rem;">
            &copy; {{ date('Y') }} <strong>NeskerOS</strong>. Todos los derechos reservados.
        </span>
        
        {{-- Identificación institucional del proyecto --}}
        <span class="text-muted" style="font-size: 0.85rem; font-weight: 500;">
            Proyecto de CTA &bull; Universidad de Guadalajara
        </span>

    </div>
</footer>