{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Formulario parcial reutilizable de Laravel Blade para la creación y edición de registros de Aulas
    (Classroom Form Component).
    Genera identificadores únicos para los campos según la existencia del registro ($classroom->id) o 'create'.
    Soporta la precarga de datos existentes (old() e instancia), selección dinámica de piso mediante JS
    y mensajes de validación contextuales.
--================================================================================================== --}}

{{-- Inicialización de variable para generación de IDs únicos --}}
@php
    $uniqueId = $classroom->id ?? 'create';
@endphp

{{-- Selección de Edificio con dataset para vinculación dinámica de pisos --}}
<div class="mb-3">
    <label for="buildings_id_{{ $uniqueId }}" class="custom-label">{{ __('Building') }}</label>
    <select name="buildings_id" 
            id="buildings_id_{{ $uniqueId }}" 
            class="form-select custom-input dynamic-building-select @error('buildings_id') is-invalid @enderror" 
            data-target="#floor_select_{{ $uniqueId }}" 
            required>
        <option value="">-- {{ __('Select a Building') }} --</option>
        @foreach($buildings as $building)
            <option value="{{ $building->id }}" 
                    data-floors="{{ $building->floors }}"
                    {{ old('buildings_id', $classroom->buildings_id ?? '') == $building->id ? 'selected' : '' }}>
                {{ $building->name }}
            </option>
        @endforeach
    </select>
    @error('buildings_id')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Campo de texto para el Nombre del Aula --}}
<div class="mb-3">
    <label for="classroom_{{ $uniqueId }}" class="custom-label">{{ __('Classroom Name') }}</label>
    <input type="text" 
           name="classroom" 
           id="classroom_{{ $uniqueId }}" 
           class="form-control custom-input @error('classroom') is-invalid @enderror" 
           value="{{ old('classroom', $classroom->classroom ?? '') }}" 
           placeholder="Ej: Casado" 
           required>
    @error('classroom')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Selección de Piso (poblado dinámicamente vía JS según el edificio seleccionado) --}}
<div class="mb-3">
    <label for="floor_select_{{ $uniqueId }}" class="custom-label">{{ __('Floor') }}</label>
    <select name="floor" 
            id="floor_select_{{ $uniqueId }}" 
            class="form-select custom-input @error('floor') is-invalid @enderror" 
            data-selected-floor="{{ old('floor', $classroom->floor ?? '') }}" 
            required>
        <option value="">-- {{ __('Select a Floor') }} --</option>
    </select>
    @error('floor')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Selección de Estado del Aula (Available, Maintenance, Occupied) --}}
<div class="mb-3">
    <label for="status_{{ $uniqueId }}" class="custom-label">{{ __('Status') }}</label>
    <select name="status" 
            id="status_{{ $uniqueId }}" 
            class="form-select custom-input @error('status') is-invalid @enderror" 
            required>
        <option value="">-- {{ __('Select Status') }} --</option>
        <option value="available" {{ old('status', $classroom->status ?? 'available') == 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
        <option value="maintenance" {{ old('status', $classroom->status ?? '') == 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
        <option value="occupied" {{ old('status', $classroom->status ?? '') == 'occupied' ? 'selected' : '' }}>{{ __('Occupied') }}</option>
    </select>
    @error('status')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>
