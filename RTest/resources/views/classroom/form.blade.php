{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Formulario parcial reutilizable de Laravel Blade para la creación y edición de registros de Aulas
    (Classroom Form Component).
    Genera identificadores únicos para los campos según la existencia del registro ($classroom->id) o 'create'.
    Soporta la precarga de datos existentes (old() e instancia), selección dinámica de piso mediante JavaScript
    y asignación del estado del aula.
--================================================================================================== --}}

{{-- Inicialización de variable para generación de IDs únicos --}}
@php
    // Genera un ID único: usa el ID del aula si existe, o 'create' si es un aula nueva
    $uniqueId = $classroom->id ?? 'create';
@endphp

{{-- Selección de Edificio con dataset para vinculación dinámica de pisos --}}
<div class="mb-3">
    <label for="buildings_id_{{ $uniqueId }}" class="form-label text-muted small mb-1">Building</label>
    <select name="buildings_id" id="buildings_id_{{ $uniqueId }}" class="form-select dynamic-building-select" data-target="#floor_select_{{ $uniqueId }}" required>
        <option value="">-- Select a Building --</option>
        @foreach($buildings as $building)
            <option value="{{ $building->id }}" 
                    data-floors="{{ $building->floors }}"
                    {{ old('buildings_id', $classroom->buildings_id) == $building->id ? 'selected' : '' }}>
                {{ $building->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- Campo de texto para el Nombre del Aula --}}
<div class="mb-3">
    <label for="classroom" class="form-label text-muted small mb-1">Classroom Name</label>
    <input type="text" name="classroom" class="form-control bg-light" value="{{ old('classroom', $classroom->classroom) }}" placeholder="Ej: Casado" required>
</div>

{{-- Selección de Piso (poblado dinámicamente vía JS según el edificio seleccionado) --}}
<div class="mb-3">
    <label for="floor_select_{{ $uniqueId }}" class="form-label text-muted small mb-1">Floor</label>
    <select name="floor" id="floor_select_{{ $uniqueId }}" class="form-select" data-selected-floor="{{ old('floor', $classroom->floor) }}" required>
        <option value="">-- Select a Floor --</option>
    </select>
</div>

{{-- Selección de Estado del Aula (Available, Maintenance, Occupied) --}}
<div class="mb-3">
    <label for="status" class="form-label text-muted small mb-1">Status</label>
    <select name="status" class="form-select" required>
        <option value="">-- Select Status --</option>
        <option value="available" {{ old('status', $classroom->status) == 'available' ? 'selected' : '' }}>Available</option>
        <option value="maintenance" {{ old('status', $classroom->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        <option value="occupied" {{ old('status', $classroom->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
    </select>
</div>