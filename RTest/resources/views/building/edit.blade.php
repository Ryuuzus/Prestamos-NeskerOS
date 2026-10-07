{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la edición de registros de Edificios (Edit Building Modal).
    Se renderiza dinámicamente utilizando el ID de cada edificio para vinculación única.
    Envía peticiones mediante el método PATCH a la ruta 'buildings.update' e incluye
    la plantilla parcial reutilizable del formulario ('building.form').
--================================================================================================== --}}

{{-- Estructura principal del Modal de Edición vinculada al ID del edificio --}}
<div class="modal fade" id="editModal{{ $building->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $building->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            
            {{-- Encabezado del Modal con título y botón de cierre --}}
            <div class="modal-header" style="border-bottom: 1px solid #f0f2f5; padding: 20px 30px;">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $building->id }}" style="color: #2b3445;">{{ __('Update Building') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Formulario con método PATCH para la actualización de datos --}}
            <form method="POST" action="{{ route('buildings.update', $building->id) }}" role="form" enctype="multipart/form-data">
                {{ method_field('PATCH') }}
                @csrf
                
                {{-- Cuerpo del Modal que incluye los campos parciales del formulario --}}
                <div class="modal-body" style="padding: 30px;">
                    @include('building.form')
                </div>
                
                {{-- Pie del Modal con botones de cancelación y envío --}}
                <div class="modal-footer" style="border-top: 1px solid #f0f2f5; padding: 15px 30px;">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Update') }}</button>
                </div>
            </form>

        </div>
    </div>
</div>