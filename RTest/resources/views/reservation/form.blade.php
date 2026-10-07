{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Subvista o componente reutilizable con los campos del formulario de reservación (Reservation Form Fields).
    Proporciona los selectores opcionales de Aulas y Dispositivos, así como los campos de tipo `datetime-local`
    para definir el horario de inicio y fin esperados de la reservación.
--================================================================================================== --}}

<div class="row">
    
    {{-- Campo: Selección opcional de Aula --}}
    <div class="col-md-6 mb-3">
        <label for="classroom_id" class="custom-label">Aula (Opcional)</label>
        <select name="classroom_id" class="custom-input">
            <option value="">Ninguna</option>
            @foreach ($classrooms as $classroom)
                <option value="{{ $classroom->id }}" {{ $reservation->classroom_id ==$classroom->id ? 'selected' : '' }}>
                    {{ $classroom->classroom }} (Piso {{$classroom->floor }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Campo: Selección opcional de Dispositivo --}}
    <div class="col-md-6 mb-3">
        <label for="device_id" class="custom-label">Dispositivo (Opcional)</label>
        <select name="device_id" class="custom-input">
            <option value="">Ninguno</option>
            @foreach ($devices as $device)
                <option value="{{ $device->id }}" {{ $reservation->device_id ==$device->id ? 'selected' : '' }}>
                    {{ $device->device_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Campo: Fecha y hora de inicio --}}
    <div class="col-md-6 mb-3">
        <label for="start_time" class="custom-label">Fecha y hora de inicio</label>
        <input type="datetime-local" name="start_time" class="custom-input" value="{{ old('start_time', $reservation->start_time) }}" required>
    </div>

    {{-- Campo: Fecha y hora de entrega esperada --}}
    <div class="col-md-6 mb-3">
        <label for="end_time" class="custom-label">Fecha y hora de entrega esperada</label>
        <input type="datetime-local" name="end_time" class="custom-input" value="{{ old('end_time', $reservation->end_time) }}" required>
    </div>

</div>