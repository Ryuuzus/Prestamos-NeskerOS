<div class="row">
    <!-- Aula (Opcional) -->
    <div class="col-md-6 mb-3">
        <label for="classroom_id" class="custom-label">Aula (Opcional)</label>
        <select name="classroom_id" class="custom-input">
            <option value="">Ninguna</option>
            @foreach($classrooms as $classroom)
                <option value="{{ $classroom->id }}" {{ $reservation->classroom_id == $classroom->id ? 'selected' : '' }}>
                    {{ $classroom->classroom }} (Piso {{ $classroom->floor }})
                </option>
            @endforeach
        </select>
    </div>

    <!-- Dispositivo (Opcional) -->
    <div class="col-md-6 mb-3">
        <label for="device_id" class="custom-label">Dispositivo (Opcional)</label>
        <select name="device_id" class="custom-input">
            <option value="">Ninguno</option>
            @foreach($devices as $device)
                <option value="{{ $device->id }}" {{ $reservation->device_id == $device->id ? 'selected' : '' }}>
                    {{ $device->device_name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Fechas -->
    <div class="col-md-6 mb-3">
        <label for="start_time" class="custom-label">Fecha y hora de inicio</label>
        <input type="datetime-local" name="start_time" class="custom-input" value="{{ old('start_time', $reservation->start_time) }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label for="end_time" class="custom-label">Fecha y hora de entrega esperada</label>
        <input type="datetime-local" name="end_time" class="custom-input" value="{{ old('end_time', $reservation->end_time) }}" required>
    </div>
</div>