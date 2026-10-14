{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Modal independiente para la creación de un edificio.
    Utiliza su propio almacén de errores y el formulario parcial compartido.
--================================================================================================== --}}

<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">
            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="createModalLabel">{{ __('Create Building') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('buildings.store') }}" method="POST">
                @csrf
                <div class="modal-body building-modal-body">
                    @include('building.form')
                </div>

                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
