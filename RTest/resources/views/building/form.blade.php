{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Edificios (Building Form Fields).
    Maneja los inputs para el Nombre y Cantidad de Pisos con identificadores dinámicos por ID
    para evitar duplicaciones en el DOM, persistencia de datos (old) y despliegue de validaciones.
--================================================================================================== --}}

{{-- Sufijo dinámico para evitar duplicación de ID en el DOM al iterar modales en bucles --}}
@php
    $fieldSuffix = isset($building->id) ? $building->id : 'create';
@endphp

{{-- Campo de texto: Nombre del Edificio --}}
<div class="mb-3">
    <label for="name_{{ $fieldSuffix }}" class="custom-label">{{ __('Name') }}</label>
    <input type="text" name="name" id="name_{{ $fieldSuffix }}" class="form-control custom-input @error('name') is-invalid @enderror" value="{{ old('name', $building->name ?? '') }}" placeholder="Enter building name" required>
    
    @error('name')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Campo numérico: Cantidad de Pisos --}}
<div class="mb-3">
    <label for="floors_{{ $fieldSuffix }}" class="custom-label">{{ __('Floors') }}</label>
    <input type="number" name="floors" id="floors_{{ $fieldSuffix }}" class="form-control custom-input @error('floors') is-invalid @enderror" value="{{ old('floors', $building->floors ?? '') }}" placeholder="Number of floors" min="1" required>
    
    @error('floors')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>