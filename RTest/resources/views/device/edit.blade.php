{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la edición de registros de Dispositivos (Edit Device Modal).
    Se vincula dinámicamente utilizando el ID único del dispositivo ($device->id).
    Envía las actualizaciones mediante el método PATCH a la ruta 'devices.update' e incluye
    la plantilla parcial del formulario ('device.form').
--================================================================================================== --}}

{{-- Estructura principal del Modal de Edición vinculada al ID del dispositivo --}}
<div class="modal fade" id="editModal{{ $device->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $device->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            {{-- Encabezado del Modal con título y botón de cierre --}}
            <div class="modal-header" style="border-bottom: 1px solid #f0f2f5; padding: 20px 30px;">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $device->id }}" style="color: #2b3445;">{{ __('Update Device') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Formulario de actualización con método PATCH --}}
            <form method="POST" action="{{ route('devices.update', $device->id) }}">
                @csrf
                @method('PATCH')
                
                {{-- Identificador de edición para reapertura automática en caso de error de validación --}}
                <input type="hidden" name="editing_id" value="{{ $device->id }}">
                
                {{-- Cuerpo del Modal que incluye los campos parciales del formulario --}}
                <div class="modal-body" style="padding: 30px;">
                    @include('device.form')
                </div>
                
                {{-- Pie del Modal con botones de acción --}}
                <div class="modal-footer" style="border-top: 1px solid #f0f2f5; padding: 15px 30px;">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Update') }}</button>
                </div>
            </form>

        </div>
    </div>
</div>
