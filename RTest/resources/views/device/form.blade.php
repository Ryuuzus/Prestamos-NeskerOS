{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Dispositivos (Device Form Fields).
    Incluye Nombre, Número de Serie, Cantidad (quantity) y Estado.

    - Genera IDs únicos por dispositivo ($device->id) o 'create'.
    - Muestra un resumen de errores ARRIBA de los campos (igual que el CRUD de Edificios) y marca
      en rojo el borde de los campos con error.
    - Los errores y valores old() solo se aplican al modal que realmente se envió
      (evita que todos los modales de edición muestren los datos/errores de otro registro).
--================================================================================================== --}}

@php
    // ID único para los campos de este formulario.
    $uniqueId = $device->exists ? $device->id : 'create';

    // ¿Este formulario es el que se envió? (edit: coincide editing_id | create: no hay editing_id)
    $isCurrentForm = old('editing_id')
        ? (string) old('editing_id') === (string) $device->id
        : ! $device->exists;

    $showErrors = $errors->any() && $isCurrentForm;

    // Valor del campo: old() solo si es el formulario enviado; si no, el valor guardado.
    $val = fn ($field, $default = '') => $isCurrentForm ? old($field, $default) : $default;

    // Clase de error por campo.
    $invalid = fn ($field) => ($showErrors && $errors->has($field)) ? 'is-invalid' : '';

    $currentStatus = $val('status', $device->status ?? 'available');
@endphp

{{-- Resumen de errores de validación --}}
@if($showErrors)
    <div class="validation-summary" role="alert">
        <strong>
            {{ $device->exists ? 'No se pudo actualizar el dispositivo.' : 'No se pudo guardar el dispositivo.' }}
            Revisa los siguientes errores:
        </strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Nombre del Dispositivo --}}
<div class="mb-3">
    <label for="name_{{ $uniqueId }}" class="custom-label">{{ __('Device Name') }}</label>
    <input type="text"
           name="name"
           id="name_{{ $uniqueId }}"
           class="form-control custom-input {{ $invalid('name') }}"
           value="{{ $val('name', $device->name) }}"
           placeholder="Enter device name"
           maxlength="100"
           required>
</div>

{{-- Número de Serie (único) --}}
<div class="mb-3">
    <label for="serial_number_{{ $uniqueId }}" class="custom-label">{{ __('Serial Number') }}</label>
    <input type="text"
           name="serial_number"
           id="serial_number_{{ $uniqueId }}"
           class="form-control custom-input {{ $invalid('serial_number') }}"
           value="{{ $val('serial_number', $device->serial_number) }}"
           placeholder="Enter serial number"
           maxlength="50"
           required>
</div>

{{-- Cantidad --}}
<div class="mb-3">
    <label for="quantity_{{ $uniqueId }}" class="custom-label">{{ __('Quantity') }}</label>
    <input type="number"
           name="quantity"
           id="quantity_{{ $uniqueId }}"
           class="form-control custom-input {{ $invalid('quantity') }}"
           value="{{ $val('quantity', $device->quantity ?? 1) }}"
           min="1"
           max="9999"
           step="1"
           required>
</div>

{{-- Estado --}}
<div class="mb-3">
    <label for="status_{{ $uniqueId }}" class="custom-label">{{ __('Status') }}</label>
    <select name="status"
            id="status_{{ $uniqueId }}"
            class="form-select custom-input {{ $invalid('status') }}"
            required>
        <option value="">-- {{ __('Select Status') }} --</option>
        <option value="available"   {{ $currentStatus === 'available'   ? 'selected' : '' }}>{{ __('Available') }}</option>
        <option value="maintenance" {{ $currentStatus === 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
    </select>
    <div class="form-text text-muted small mt-1">Las unidades en uso se calculan con las reservaciones aprobadas; aquí solo se marca el mantenimiento.</div>
</div>
