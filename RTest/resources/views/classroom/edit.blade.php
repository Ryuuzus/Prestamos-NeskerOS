{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la edición de registros de Aulas (Edit Classroom Modal).
    Se renderiza una vez por aula (ID único) FUERA de la tabla. Envía PATCH a 'classrooms.update'
    e incluye el formulario parcial reutilizable ('classroom.form').
--================================================================================================== --}}

<div class="modal fade" id="editModal{{ $classroom->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $classroom->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">

            {{-- Encabezado --}}
            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $classroom->id }}">{{ __('Update Classroom') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('classrooms.update', $classroom->id) }}" novalidate>
                @csrf
                @method('PATCH')

                {{-- Identificador para reabrir ESTE modal si falla la validación --}}
                <input type="hidden" name="editing_id" value="{{ $classroom->id }}">

                {{-- Cuerpo: resumen de errores + campos --}}
                <div class="modal-body building-modal-body">
                    @include('classroom.form')
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