{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la creación de un nuevo registro de Dispositivo (Create Device Modal).
    Proporciona el contenedor para registrar la información básica (Nombre, Número Serial y Estado)
    enviando los datos mediante el método POST a la ruta 'devices.store' e incluyendo el formulario parcial.
--================================================================================================== --}}

{{-- Estructura principal del Modal de Creación --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            {{-- Encabezado del Modal con título y botón de cierre --}}
            <div class="modal-header" style="border-bottom: 1px solid #f0f2f5; padding: 20px 30px;">
                <h5 class="modal-title fw-bold" id="createModalLabel" style="color: #2b3445;">{{ __('Create Device') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Formulario para almacenar el nuevo dispositivo --}}
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                
                {{-- Cuerpo del Modal que incluye los campos parciales del formulario --}}
                <div class="modal-body" style="padding: 30px;">
                    @include('device.form')
                </div>
                
                {{-- Pie del Modal con botones de acción --}}
                <div class="modal-footer" style="border-top: 1px solid #f0f2f5; padding: 15px 30px;">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Save') }}</button>
                </div>
            </form>

        </div>
    </div>
</div>