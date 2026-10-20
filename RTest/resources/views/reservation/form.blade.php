{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Subvista reutilizable con los campos del formulario de reservación (Reservation Form Fields).
    Aula y dispositivo opcionales (solo los que no están en mantenimiento), unidades del dispositivo y
    fecha + hora de inicio y de entrega por separado.

    Las horas son listas desplegables: solo aparecen las del horario permitido (por defecto 7:00 AM a
    9:00 PM) y las que no están disponibles se ven DESHABILITADAS, sin poder elegirse. La
    disponibilidad se consulta a reservations.availability según el aula, dispositivo, cantidad y fecha.
    Todos los límites salen de config/reservations.php. El servidor vuelve a validar al guardar.
--================================================================================================== --}}

@php
    $minDays      = (int) config('reservations.min_days_in_advance', 7);
    $maxDaysAhead = config('reservations.max_days_ahead');
    $minHours     = (int) config('reservations.min_duration_hours', 2);
    $maxHours     = (int) config('reservations.max_duration_hours', 48);
    $openHour     = (int) config('reservations.open_hour', 7);
    $closeHour    = (int) config('reservations.close_hour', 21);
    $slotMinutes  = max(5, (int) config('reservations.slot_minutes', 60));

    // 2 -> "2 horas", 24 -> "1 día", 48 -> "2 días"
    $fmtDuration = function (int $h) {
        if ($h % 24 === 0) {
            $d = intdiv($h, 24);
            return $d . ($d === 1 ? ' día' : ' días');
        }

        return $h . ($h === 1 ? ' hora' : ' horas');
    };

    $minDate  = now()->addDays($minDays)->toDateString();
    $maxDate  = $maxDaysAhead !== null ? now()->addDays((int) $maxDaysAhead)->toDateString() : null;
    $minStart = now()->addDays($minDays)->addMinute()->format('Y-m-d H:i'); // primer momento reservable

    // Horas del día: de apertura a cierre, cada $slotMinutes minutos. valor 24 h => texto 12 h.
    $slots = [];
    for ($m = $openHour * 60; $m <= $closeHour * 60; $m += $slotMinutes) {
        $value = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        $slots[$value] = \Carbon\Carbon::createFromFormat('H:i', $value)->format('g:i A');
    }

    // Muestra la hora como HH:MM (la columna TIME viene como HH:MM:SS).
    $hhmm = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('H:i') : '';

    $startDate = old('start_date', $reservation->start_date?->format('Y-m-d'));
    $startTime = old('start_time', $hhmm($reservation->start_time));
    $endDate   = old('end_date', $reservation->end_date?->format('Y-m-d'));
    $endTime   = old('end_time', $hhmm($reservation->end_time));
@endphp

<div class="row" id="reservation-fields"
     data-availability-url="{{ route('reservations.availability') }}"
     data-min-start="{{ $minStart }}"
     data-open="{{ $openHour }}"
     data-close="{{ $closeHour }}"
     data-slot="{{ $slotMinutes }}"
     data-min-duration="{{ $minHours * 60 }}"
     data-max-duration="{{ $maxHours * 60 }}">

    {{-- Recordatorio de las reglas de reserva --}}
    <div class="col-12 mb-3">
        <div class="small" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; color: #475569;">
            Reserva con al menos <strong>{{ $minDays }} días de anticipación</strong>
            (desde el <strong>{{ now()->addDays($minDays)->format('d/m/Y') }}</strong>), de
            <strong>{{ $slots[array_key_first($slots)] ?? '' }}</strong> a
            <strong>{{ $slots[array_key_last($slots)] ?? '' }}</strong>, por entre
            <strong>{{ $fmtDuration($minHours) }}</strong> y <strong>{{ $fmtDuration($maxHours) }}</strong>.
            Las horas que no están disponibles aparecen deshabilitadas.
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
        <select name="start_time" id="start_time" class="custom-input @error('start_time') is-invalid @enderror" required>
            <option value="">--</option>
            @foreach($slots as $value => $label)
                <option value="{{ $value }}" data-label="{{ $label }}" {{ $startTime === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Entrega esperada: fecha y hora por separado --}}
    <div class="col-md-4 mb-3">
        <label for="end_date" class="custom-label">Fecha de entrega esperada</label>
        <input type="date" name="end_date" id="end_date"
               class="custom-input @error('end_date') is-invalid @enderror"
               value="{{ $endDate }}" min="{{ $minDate }}" required>
    </div>

    <div class="col-md-2 mb-3">
        <label for="end_time" class="custom-label">Hora de entrega</label>
        <select name="end_time" id="end_time" class="custom-input @error('end_time') is-invalid @enderror" required>
            <option value="">--</option>
            @foreach($slots as $value => $label)
                <option value="{{ $value }}" data-label="{{ $label }}" {{ $endTime === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('reservation-fields');
    if (!root) return;

    const el = (id) => document.getElementById(id);
    const classroom = el('classroom_id'), device = el('device_id'), qty = el('device_quantity');
    const startDate = el('start_date'), startTime = el('start_time');
    const endDate = el('end_date'), endTime = el('end_time');

    // Los momentos se manejan como "minutos desde 1970" tomando la hora tal cual la da el servidor
    // (sin conversiones de zona horaria), así coinciden con las horas que se muestran.
    function parseStamp(str) {
        const m = str.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
        return Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]) / 60000;
    }
    const dayStart = (dateStr) => parseStamp(dateStr + ' 00:00');
    const dateOf = (minutes) => new Date(minutes * 60000).toISOString().slice(0, 10);
    const slotMinute = (value) => { const [h, m] = value.split(':').map(Number); return h * 60 + m; };

    const cfg = {
        url: root.dataset.availabilityUrl,
        minStart: parseStamp(root.dataset.minStart),
        open: parseInt(root.dataset.open) * 60,
        close: parseInt(root.dataset.close) * 60,
        slot: parseInt(root.dataset.slot),
        minDur: parseInt(root.dataset.minDuration),
        maxDur: parseInt(root.dataset.maxDuration),
    };

    let blocked = [];   // intervalos [inicio, fin) no disponibles, en minutos
    let requestId = 0;

    const isBlocked = (t) => blocked.some(([a, b]) => a <= t && t < b);
    const nextBlockStart = (t) => blocked.reduce((min, [a]) => (a > t && a < min ? a : min), Infinity);

    // Rango de horas de entrega válidas para un inicio S: mínimo de duración, máximo de duración,
    // y sin llegar a la siguiente reservación que bloquee.
    const endLimits = (S) => [S + cfg.minDur, Math.min(S + cfg.maxDur, nextBlockStart(S))];

    // ¿Existe al menos una hora de entrega permitida para este inicio?
    function hasValidEnd(S) {
        const [lo, hi] = endLimits(S);
        if (hi < lo) return false;

        for (let day = Math.floor(S / 1440) * 1440; day <= hi; day += 1440) {
            for (let m = cfg.open; m <= cfg.close; m += cfg.slot) {
                const t = day + m;
                if (t >= lo && t <= hi) return true;
            }
        }
        return false;
    }

    // Habilita o deshabilita una opción y lo indica en su texto.
    function setOption(option, enabled) {
        if (!option.value) return;
        const label = option.dataset.label || option.textContent.trim();
        option.disabled = !enabled;
        option.textContent = enabled ? label : label + ' — no disponible';
    }

    const clearIfDisabled = (select) => {
        const selected = select.selectedOptions[0];
        if (selected && selected.disabled) select.value = '';
    };

    function startMinute() {
        return (startDate.value && startTime.value)
            ? dayStart(startDate.value) + slotMinute(startTime.value)
            : null;
    }

    function refreshStartTimes() {
        const date = startDate.value;

        Array.from(startTime.options).forEach((option) => {
            if (!option.value) return;

            if (!date) {            // sin fecha todavía: no se marca nada como no disponible
                setOption(option, true);
                return;
            }

            const S = dayStart(date) + slotMinute(option.value);
            setOption(option, S >= cfg.minStart && !isBlocked(S) && hasValidEnd(S));
        });

        clearIfDisabled(startTime);
        refreshEndDate();
    }

    function refreshEndDate() {
        if (startDate.value) {
            const S = startMinute();
            const latest = (S !== null ? S : dayStart(startDate.value)) + cfg.maxDur;

            endDate.min = startDate.value;
            endDate.max = dateOf(latest);

            if (!endDate.value || endDate.value < endDate.min) endDate.value = endDate.min;
            if (endDate.value > endDate.max) endDate.value = endDate.max;
        }

        refreshEndTimes();
    }

    function refreshEndTimes() {
        const S = startMinute();

        if (S === null || !endDate.value) {   // hay que elegir primero el inicio
            Array.from(endTime.options).forEach((option) => setOption(option, true));
            endTime.disabled = S === null;
            if (S === null) endTime.value = '';
            return;
        }

        endTime.disabled = false;
        const [lo, hi] = endLimits(S);

        Array.from(endTime.options).forEach((option) => {
            if (!option.value) return;
            const T = dayStart(endDate.value) + slotMinute(option.value);
            setOption(option, T >= lo && T <= hi);
        });

        clearIfDisabled(endTime);
    }

    // Las unidades solo aplican si hay un dispositivo elegido y no pueden pasar de su total.
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

    // Pide al servidor los horarios no disponibles y vuelve a calcular las horas elegibles.
    async function loadBlocked() {
        const current = ++requestId;
        const date = startDate.value;

        if (!date || (!classroom.value && !device.value)) {
            blocked = [];
            refreshStartTimes();
            return;
        }

        const params = new URLSearchParams({ date });
        if (classroom.value) params.set('classroom_id', classroom.value);
        if (device.value) {
            params.set('device_id', device.value);
            params.set('device_quantity', qty.disabled ? 1 : (qty.value || 1));
        }

        try {
            const response = await fetch(cfg.url + '?' + params.toString(), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('availability');

            const data = await response.json();
            if (current !== requestId) return;   // llegó una respuesta más reciente

            blocked = data.blocked.map(([a, b]) => [parseStamp(a), parseStamp(b)]);
        } catch (e) {
            if (current !== requestId) return;
            blocked = [];   // sin datos: el servidor igual valida al guardar
        }

        refreshStartTimes();
    }

    let timer;
    classroom.addEventListener('change', loadBlocked);
    device.addEventListener('change', () => { syncQuantity(); loadBlocked(); });
    qty.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(loadBlocked, 300); });
    startDate.addEventListener('change', loadBlocked);
    startTime.addEventListener('change', refreshEndDate);
    endDate.addEventListener('change', refreshEndTimes);

    syncQuantity();
    loadBlocked();
});
</script>
