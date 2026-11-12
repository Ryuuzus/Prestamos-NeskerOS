<details class="p-3 border-top">
    <summary class="text-danger"><i class="fa-solid fa-screwdriver-wrench me-2" aria-hidden="true"></i>Reportar avería</summary>
    <p class="small mt-2">El recurso pasa a mantenimiento y se cancelan las solicitudes sin entregas. Las devoluciones pendientes se conservan.</p>
    <form method="POST" action="{{ route('maintenance.store', ['type' => $type, 'id' => $resource->id]) }}">
        @csrf
        <label for="maintenance-{{ $type }}-{{ $resource->id }}" class="form-label">Motivo de mantenimiento</label>
        <textarea id="maintenance-{{ $type }}-{{ $resource->id }}" name="reason" class="form-control mb-2" rows="2" maxlength="500" required></textarea>
        <button type="submit" class="btn btn-outline-danger">Registrar avería</button>
    </form>
</details>
