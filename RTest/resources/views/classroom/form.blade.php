{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Formulario parcial reutilizable para la creación y edición de Aulas (Classroom Form).

    - Genera IDs únicos por aula ($classroom->id) o 'create'.
    - Muestra un resumen de errores de validación ARRIBA de los campos (igual que el CRUD de Edificios)
      y marca en rojo el borde de los campos con error.
    - Los errores y valores old() solo se aplican al modal que realmente se envió
      (evita que todos los modales de edición muestren los datos/errores de otro registro).
--================================================================================================== --}}

@php
    // ID único para los campos de este formulario.
    $uniqueId = $classroom->exists ? $classroom->id : 'create';

    // ¿Este formulario es el que se envió? (edit: coincide editing_id | create: no hay editing_id)
    $isCurrentForm = old('editing_id')
        ? (string) old('editing_id') === (string) $classroom->id
        : ! $classroom->exists;

    $showErrors = $errors->any() && $isCurrentForm;

    // Valor del campo: old() solo si es el formulario enviado; si no, el valor guardado.
    $val = fn ($field, $default = '') => $isCurrentForm ? old($field, $default) : $default;

    // Clase de error por campo.
    $invalid = fn ($field) => ($showErrors && $errors->has($field)) ? 'is-invalid' : '';
@endphp

{{-- Resumen de errores de validación --}}
@if($showErrors)
    <div class="validation-summary" role="alert">
        <strong>
            {{ $classroom->exists ? 'No se pudo actualizar el aula.' : 'No se pudo guardar el aula.' }}
            Revisa los siguientes errores:
        </strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Selección de Edificio con dataset para vinculación dinámica de pisos --}}
<div class="mb-3">
    <label for="buildings_id_{{ $uniqueId }}" class="custom-label">{{ __('Building') }}</label>
    <select name="buildings_id"
            id="buildings_id_{{ $uniqueId }}"
            class="form-select custom-input dynamic-building-select {{ $invalid('buildings_id') }}"
            data-target="#floor_select_{{ $uniqueId }}"
            required>
        <option value="">-- {{ __('Select a Building') }} --</option>
        @foreach($buildings as $building)
            <option value="{{ $building->id }}"
                    data-floors="{{ $building->floors }}"
                    {{ (string) $val('buildings_id', $classroom->buildings_id) === (string) $building->id ? 'selected' : '' }}>
                {{ $building->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Nombre del Aula --}}
<div class="mb-3">
    <label for="classroom_{{ $uniqueId }}" class="custom-label">{{ __('Classroom Name') }}</label>
    <input type="text"
           name="classroom"
           id="classroom_{{ $uniqueId }}"
           class="form-control custom-input {{ $invalid('classroom') }}"
           value="{{ $val('classroom', $classroom->classroom) }}"
           placeholder="Ej: Casado"
           maxlength="100"
           required>
</div>

{{-- Selección de Piso (poblado dinámicamente vía JS según el edificio seleccionado) --}}
<div class="mb-3">
    <label for="floor_select_{{ $uniqueId }}" class="custom-label">{{ __('Floor') }}</label>
    <select name="floor"
            id="floor_select_{{ $uniqueId }}"
            class="form-select custom-input {{ $invalid('floor') }}"
            data-selected-floor="{{ $val('floor', $classroom->floor) }}"
            required>
        <option value="">-- {{ __('Select a Floor') }} --</option>
    </select>
</div>

{{-- Estado del Aula --}}
<div class="mb-3">
    <label for="status_{{ $uniqueId }}" class="custom-label">{{ __('Status') }}</label>
    <select name="status"
            id="status_{{ $uniqueId }}"
            class="form-select custom-input {{ $invalid('status') }}"
            required>
        @php $currentStatus = $val('status', $classroom->status ?? 'available'); @endphp
        <option value="">-- {{ __('Select Status') }} --</option>
        <option value="available"   {{ $currentStatus === 'available'   ? 'selected' : '' }}>{{ __('Available') }}</option>
        <option value="maintenance" {{ $currentStatus === 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
    </select>
    <div class="form-text text-muted small mt-1">La ocupación del aula se calcula con las reservaciones aprobadas; aquí solo se marca el mantenimiento.</div>
</div>
