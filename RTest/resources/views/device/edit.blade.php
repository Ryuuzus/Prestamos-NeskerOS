{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la edición de registros de Dispositivos (Edit Device Modal).
    Se renderiza una vez por dispositivo (ID único) FUERA de la tabla. Envía PATCH a 'devices.update'
    e incluye el formulario parcial ('device.form').
--================================================================================================== --}}

<div class="modal fade" id="editModal{{ $device->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $device->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">

            {{-- Encabezado --}}
            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $device->id }}">{{ __('Update Device') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('devices.update', $device->id) }}" novalidate>
                @csrf
                @method('PATCH')

                {{-- Identificador para reabrir ESTE modal si falla la validación --}}
                <input type="hidden" name="editing_id" value="{{ $device->id }}">

                {{-- Cuerpo: resumen de errores + campos --}}
                <div class="modal-body building-modal-body">
                    @include('device.form')
                </div>

                {{-- Pie --}}
                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Update') }}</button>
                </div>
            </form>

        </div>
    </div>
</div>