<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">

            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">
                    Rechazar reservación <span data-reservation-label></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-body building-modal-body">
                    <label for="rejection_reason" class="custom-label">Motivo del rechazo</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control custom-input"
                              rows="3" maxlength="255" required
                              placeholder="Explica por qué se rechaza la solicitud"></textarea>
                </div>

                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">Rechazar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rejectModal');

    // Al abrir el modal, apunta el formulario a la reservación del botón que lo abrió.
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        modal.querySelector('form').action = button.getAttribute('data-action');
        modal.querySelector('[data-reservation-label]').textContent = button.getAttribute('data-label');
        modal.querySelector('textarea').value = '';
    });
});
</script>
