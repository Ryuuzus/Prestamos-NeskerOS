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
        <select name="classroom_id" id="classroom_id" class="custom-input">
            <option value="">Ninguna</option>
            @if(isset($classrooms) &&$classrooms->count() > 0)
                @foreach ($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" 
                        {{ old('classroom_id', $reservation->classroom_id ?? '') == $classroom->id ? 'selected' : '' }}>
                        {{ $classroom->classroom ?? $classroom->name ?? 'Aula #'.$classroom->id }} (Piso {{$classroom->floor ?? 'N/A' }})
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    {{-- Campo: Selección opcional de Dispositivo --}}
    <div class="col-md-6 mb-3">
        <label for="device_id" class="custom-label">Dispositivo (Opcional)</label>
        <select name="device_id" id="device_id" class="custom-input">
            <option value="">Ninguno</option>
            @if(isset($devices) &&$devices->count() > 0)
                @foreach ($devices as $device)
                    <option value="{{ $device->id }}" 
                        {{ old('device_id', $reservation->device_id ?? '') == $device->id ? 'selected' : '' }}>
                        {{ $device->name }} (Numero de serie: {{ $device->serial_number ?? 'N/A' }})
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    {{-- Campo: Fecha y hora de inicio --}}
    <div class="col-md-6 mb-3">
        <label for="start_time" class="custom-label">Fecha y hora de inicio</label>
        <input type="datetime-local" name="start_time" id="start_time" class="custom-input" 
               value="{{ old('start_time', isset($reservation->start_time) ? \Carbon\Carbon::parse($reservation->start_time)->format('Y-m-d\TH:i') : '') }}" 
               required>
    </div>

    {{-- Campo: Fecha y hora de entrega esperada --}}
    <div class="col-md-6 mb-3">
        <label for="end_time" class="custom-label">Fecha y hora de entrega esperada</label>
        <input type="datetime-local" name="end_time" id="end_time" class="custom-input" 
               value="{{ old('end_time', isset($reservation->end_time) ? \Carbon\Carbon::parse($reservation->end_time)->format('Y-m-d\TH:i') : '') }}" 
               required>
    </div>

</div>