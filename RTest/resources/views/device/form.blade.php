{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Dispositivos (Device Form Fields).
    Proporciona los elementos de entrada para el Nombre del Dispositivo, Número de Serie y Estado,
    gestionando la recuperación de valores previos (old values) y la visualización de errores de validación.
--================================================================================================== --}}

{{-- Campo: Nombre del Dispositivo --}}
<div class="mb-3">
    <label for="name" class="custom-label">{{ __('Device Name') }}</label>
    <input type="text" name="name" id="name" class="form-control custom-input @error('name') is-invalid @enderror" value="{{ old('name', $device->name ?? '') }}" placeholder="Enter device name" required>
    @error('name')
        <span class="text-danger small mt-1">{{ $message }}</span>
    @enderror
</div>

{{-- Campo: Número de Serie --}}
<div class="mb-3">
    <label for="serial_number" class="custom-label">{{ __('Serial Number') }}</label>
    <input type="text" name="serial_number" id="serial_number" class="form-control custom-input @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $device->serial_number ?? '') }}" placeholder="Enter serial number" required>
    @error('serial_number')
        <span class="text-danger small mt-1">{{ $message }}</span>
    @enderror
</div>

{{-- Selección de Estado (Available, Maintenance, Occupied) --}}
<div class="mb-3">
    <label for="status" class="form-label text-muted small mb-1">{{ __('Status') }}</label>
    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
        <option value="">-- Select Status --</option>
        <option value="available" @selected(old('status', $device->status ?? '') == 'available')>Available</option>
        <option value="maintenance" @selected(old('status', $device->status ?? '') == 'maintenance')>Maintenance</option>
        <option value="occupied" @selected(old('status', $device->status ?? '') == 'occupied')>Occupied</option>
    </select>
    @error('status')
        <span class="text-danger small mt-1">{{ $message }}</span>
    @enderror
</div>