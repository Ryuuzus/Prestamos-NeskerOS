{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Dispositivos (Device Form Fields).
    Proporciona los elementos de entrada para el Nombre del Dispositivo, Número de Serie y Estado,
    utilizando identificadores dinámicos para evitar duplicidades en el DOM y gestionando la
    recuperación de valores previos (old values) y errores de validación.
--================================================================================================== --}}

{{-- Inicialización de sufijo único para evitar IDs duplicados en el DOM --}}
@php
    $uniqueId = $device->id ?? 'create';
@endphp

{{-- Campo: Nombre del Dispositivo --}}
<div class="mb-3">
    <label for="name_{{ $uniqueId }}" class="custom-label">{{ __('Device Name') }}</label>
    <input type="text" 
           name="name" 
           id="name_{{ $uniqueId }}" 
           class="form-control custom-input @error('name') is-invalid @enderror" 
           value="{{ old('name', $device->name ?? '') }}" 
           placeholder="Enter device name" 
           required>
    @error('name')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Campo: Número de Serie --}}
<div class="mb-3">
    <label for="serial_number_{{ $uniqueId }}" class="custom-label">{{ __('Serial Number') }}</label>
    <input type="text" 
           name="serial_number" 
           id="serial_number_{{ $uniqueId }}" 
           class="form-control custom-input @error('serial_number') is-invalid @enderror" 
           value="{{ old('serial_number', $device->serial_number ?? '') }}" 
           placeholder="Enter serial number" 
           required>
    @error('serial_number')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>

{{-- Selección de Estado (Available, Maintenance, Occupied) --}}
<div class="mb-3">
    <label for="status_{{ $uniqueId }}" class="custom-label">{{ __('Status') }}</label>
    <select name="status" 
            id="status_{{ $uniqueId }}" 
            class="form-select custom-input @error('status') is-invalid @enderror" 
            required>
        <option value="">-- {{ __('Select Status') }} --</option>
        <option value="available" @selected(old('status', $device->status ?? 'available') == 'available')>{{ __('Available') }}</option>
        <option value="maintenance" @selected(old('status', $device->status ?? '') == 'maintenance')>{{ __('Maintenance') }}</option>
        <option value="occupied" @selected(old('status', $device->status ?? '') == 'occupied')>{{ __('Occupied') }}</option>
    </select>
    @error('status')
        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
    @enderror
</div>
