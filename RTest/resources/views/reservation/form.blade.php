{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Subvista reutilizable con los campos del formulario de reservación (Reservation Form Fields).
    Selectores opcionales de Aula y Dispositivo (solo los que no están en mantenimiento) y campos
    SEPARADOS de fecha y hora para el inicio y la entrega esperada.
    La anticipación mínima y la duración mínima salen de config/reservations.php.
--================================================================================================== --}}

@php
    $minDays  = (int) config('reservations.min_days_in_advance', 7);
    $maxDays  = config('reservations.max_days_ahead');
    $minHours = (int) config('reservations.min_duration_hours', 24);
    $minLabel = $minHours % 24 === 0 ? ($minHours / 24) . ' día(s)' : "{$minHours} hora(s)";

    // La primera fecha reservable es hoy + la anticipación mínima.
    $minDate = now()->addDays($minDays)->toDateString();
    $maxDate = $maxDays !== null ? now()->addDays((int) $maxDays)->toDateString() : null;

    // Muestra la hora como HH:MM (la columna TIME viene como HH:MM:SS).
    $hhmm = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : '';

    $startDate = old('start_date', $reservation->start_date?->format('Y-m-d'));
    $startTime = old('start_time', $hhmm($reservation->start_time));
    $endDate   = old('end_date', $reservation->end_date?->format('Y-m-d'));
    $endTime   = old('end_time', $hhmm($reservation->end_time));
@endphp

<div class="row">

    {{-- Recordatorio de las reglas de reserva --}}
    <div class="col-12 mb-3">
        <div class="small" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; color: #475569;">
            Las reservaciones deben hacerse con al menos <strong>{{ $minDays }} días de anticipación</strong>
            (desde el <strong>{{ now()->addDays($minDays)->format('d/m/Y') }}</strong>) y durar al menos
            <strong>{{ $minLabel }}</strong>. No se pueden reservar aulas ni dispositivos en mantenimiento.
        </div>
    </div>

    {{-- Campo: Selección opcional de Aula --}}
    <div class="col-md-6 mb-3">
        <label for="classroom_id" class="custom-label">Aula (Opcional)</label>
        <select name="classroom_id" id="classroom_id" class="custom-input @error('classroom_id') is-invalid @enderror">
            <option value="">Ninguna</option>
            @if(isset($classrooms) && $classrooms->count() > 0)
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}"
                        {{ (string) old('classroom_id', $reservation->classroom_id ?? '') === (string) $classroom->id ? 'selected' : '' }}>
                        {{ $classroom->building->name ?? 'Sin edificio' }} · {{ $classroom->classroom }} (Piso {{ $classroom->floor ?? 'N/A' }})
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    {{-- Campo: Selección opcional de Dispositivo --}}
    <div class="col-md-4 mb-3">
        <label for="device_id" class="custom-label">Dispositivo (Opcional)</label>
        <select name="device_id" id="device_id" class="custom-input @error('device_id') is-invalid @enderror">
            <option value="">Ninguno</option>
            @if(isset($devices) && $devices->count() > 0)
                @foreach ($devices as $device)
                    <option value="{{ $device->id }}" data-quantity="{{ $device->quantity }}"
                        {{ (string) old('device_id', $reservation->device_id ?? '') === (string) $device->id ? 'selected' : '' }}>
                        {{ $device->name }} (Serie: {{ $device->serial_number ?? 'N/A' }} · {{ $device->quantity }} unidad(es))
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    {{-- Campo: Unidades del dispositivo (se limita al total del dispositivo elegido) --}}
    <div class="col-md-2 mb-3">
        <label for="device_quantity" class="custom-label">Unidades</label>
        <input type="number" name="device_quantity" id="device_quantity"
               class="custom-input @error('device_quantity') is-invalid @enderror"
               value="{{ old('device_quantity', $reservation->device_quantity ?? 1) }}"
               min="1" step="1">
    </div>

    {{-- Inicio: fecha y hora por separado --}}
    <div class="col-md-4 mb-3">
        <label for="start_date" class="custom-label">Fecha de inicio</label>
        <input type="date" name="start_date" id="start_date"
               class="custom-input @error('start_date') is-invalid @enderror"
               value="{{ $startDate }}" min="{{ $minDate }}" @if($maxDate) max="{{ $maxDate }}" @endif required>
    </div>

    <div class="col-md-2 mb-3">
        <label for="start_time" class="custom-label">Hora de inicio</label>
        <input type="time" name="start_time" id="start_time"
               class="custom-input @error('start_time') is-invalid @enderror"
               value="{{ $startTime }}" required>
    </div>

    {{-- Entrega esperada: fecha y hora por separado --}}
    <div class="col-md-4 mb-3">
        <label for="end_date" class="custom-label">Fecha de entrega esperada</label>
        <input type="date" name="end_date" id="end_date"
               class="custom-input @error('end_date') is-invalid @enderror"
               value="{{ $endDate }}" min="{{ $minDate }}" @if($maxDate) max="{{ $maxDate }}" @endif required>
    </div>

    <div class="col-md-2 mb-3">
        <label for="end_time" class="custom-label">Hora de entrega</label>
        <input type="time" name="end_time" id="end_time"
               class="custom-input @error('end_time') is-invalid @enderror"
               value="{{ $endTime }}" required>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const device = document.getElementById('device_id');
    const qty = document.getElementById('device_quantity');

    if (!device || !qty) return;

    // La cantidad solo aplica si hay un dispositivo elegido y no puede pasar de su total.
    function syncQuantity() {
        const option = device.options[device.selectedIndex];
        const max = option ? parseInt(option.getAttribute('data-quantity')) : NaN;

        if (device.value && !isNaN(max)) {
            qty.disabled = false;
            qty.max = max;
            if (parseInt(qty.value) > max) qty.value = max;
            if (!qty.value || parseInt(qty.value) < 1) qty.value = 1;
        } else {
            qty.disabled = true;
            qty.value = 1;
        }
    }

    device.addEventListener('change', syncQuantity);
    syncQuantity();
});
</script>
