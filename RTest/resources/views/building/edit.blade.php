{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Modal independiente para editar cada edificio, identificado por su ID.
    Los errores y valores anteriores se aíslan por registro.
--================================================================================================== --}}

<div class="modal fade" id="editModal{{ $building->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $building->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">
            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="editModalLabel{{ $building->id }}">{{ __('Update Building') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('buildings.update', $building->id) }}">
                @csrf
                @method('PATCH')

                {{-- Permite reabrir este modal si su validación falla. --}}
                <input type="hidden" name="editing_id" value="{{ $building->id }}">

                <div class="modal-body building-modal-body">
                    @include('building.form')
                </div>

                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
