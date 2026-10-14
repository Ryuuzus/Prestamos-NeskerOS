{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Edificios (Building Form Fields).
<<<<<<< HEAD
    Este partial maneja los inputs para el Nombre del Edificio y la Cantidad de Pisos,
    incluyendo la persistencia de datos (old values), soporte para edición y despliegue de mensajes de error de validación.
--================================================================================================== --}}

{{-- Campo de texto: Nombre del Edificio --}}
<div class="mb-3">
    <label for="name" class="custom-label">{{ __('Name') }}</label>
    <input type="text" name="name" id="name" class="form-control custom-input @error('name') is-invalid @enderror" value="{{ old('name', $building->name ?? '') }}" placeholder="Enter building name" required>
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    
    @error('name')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Campo numérico: Cantidad de Pisos --}}
<div class="mb-3">
<<<<<<< HEAD
    <label for="floors" class="custom-label">{{ __('Floors') }}</label>
    <input type="number" name="floors" id="floors" class="form-control custom-input @error('floors') is-invalid @enderror" value="{{ old('floors', $building->floors ?? '') }}" placeholder="Number of floors" min="1" required>
=======
    <label for="floors_{{ $fieldSuffix }}" class="custom-label">{{ __('Floors') }}</label>
    <input type="number" name="floors" id="floors_{{ $fieldSuffix }}" class="form-control custom-input @error('floors') is-invalid @enderror" value="{{ old('floors', $building->floors ?? '') }}" placeholder="Number of floors" min="1" required>
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    
    @error('floors')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>