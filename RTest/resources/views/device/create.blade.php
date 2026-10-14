{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la creación de un nuevo registro de Dispositivo (Create Device Modal).
    Envía los datos mediante POST a 'devices.store' e incluye el formulario parcial ('device.form').
    Usa los estilos compartidos building-modal-* de crud.css.
--================================================================================================== --}}

<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">

            {{-- Encabezado --}}
            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="createModalLabel">{{ __('Create Device') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('devices.store') }}" method="POST" novalidate>
                @csrf

                {{-- Cuerpo: resumen de errores + campos --}}
                <div class="modal-body building-modal-body">
                    @include('device.form')
                </div>

                {{-- Pie --}}
                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Save') }}</button>
                </div>
            </form>

        </div>
    </div>
</div>