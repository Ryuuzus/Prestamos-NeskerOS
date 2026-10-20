{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Componente de pie de página global de la aplicación (Footer Component).
    Muestra los derechos de autor con el año dinámico actual, la marca NeskerOS y el
    reconocimiento del proyecto universitario de la Universidad de Guadalajara.
--================================================================================================== --}}

{{-- Pie de página global --}}
<footer class="app-footer mt-auto">
    <div class="app-footer-inner">
        
        {{-- Derechos de autor y nombre del sistema con año dinámico --}}
        <span>
            &copy; {{ date('Y') }} <strong>NeskerOS</strong>. Todos los derechos reservados.
        </span>
        
        {{-- Identificación institucional del proyecto --}}
        <span>
            Proyecto de CTA &bull; Universidad de Guadalajara
        </span>


    </div>
</footer>