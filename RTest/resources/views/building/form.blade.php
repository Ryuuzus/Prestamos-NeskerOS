{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Edificios (Building Form Fields).
    Este partial maneja los inputs para el Nombre del Edificio y la Cantidad de Pisos,
    incluyendo la persistencia de datos (old values), soporte para edición y despliegue de mensajes de error de validación.
--================================================================================================== --}}

{{-- Campo de texto: Nombre del Edificio --}}
<div class="mb-3">
    <label for="name" class="custom-label">{{ __('Name') }}</label>
    <input type="text" name="name" id="name" class="form-control custom-input @error('name') is-invalid @enderror" value="{{ old('name', $building->name ?? '') }}" placeholder="Enter building name" required>
    
    @error('name')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Campo numérico: Cantidad de Pisos --}}
<div class="mb-3">
    <label for="floors" class="custom-label">{{ __('Floors') }}</label>
    <input type="number" name="floors" id="floors" class="form-control custom-input @error('floors') is-invalid @enderror" value="{{ old('floors', $building->floors ?? '') }}" placeholder="Number of floors" min="1" required>
    
    @error('floors')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>