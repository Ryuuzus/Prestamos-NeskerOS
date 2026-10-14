{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Campos reutilizables del formulario para la gestión de Edificios.
    Mantiene aislados los errores y valores anteriores de cada modal (crear y editar).
--================================================================================================== --}}

@php
    $isCreateForm = !($building->exists ?? false);
    $fieldSuffix = $isCreateForm ? 'create' : $building->id;

    // Laravel conserva los datos del último envío; solo deben aplicarse al modal que falló.
    $editingId = old('editing_id');
    $isActiveForm = $isCreateForm
        ? empty($editingId)
        : (string) $editingId === (string) $building->id;

    // Un almacén de errores distinto para crear y para cada edificio editado.
    $errorBagName = $isCreateForm ? 'createBuilding' : 'editBuilding' . $building->id;
    $formErrors = $errors->getBag($errorBagName);

    $nameValue = $isActiveForm
        ? old('name', $building->name ?? '')
        : ($building->name ?? '');

    $floorsValue = $isActiveForm
        ? old('floors', $building->floors ?? '')
        : ($building->floors ?? '');

    $statusValue = $isActiveForm
        ? old('status', $building->status ?? 'available')
        : ($building->status ?? 'available');
@endphp

{{-- Resumen de validación en la parte superior del formulario activo --}}
@if($isActiveForm && $formErrors->any())
    <div class="validation-summary" role="alert" aria-live="polite">
        <p class="fw-semibold mb-1">No se pudo guardar el edificio. Revisa los siguientes errores:</p>
        <ul class="mb-0 ps-3">
            @foreach($formErrors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Campo de texto: Nombre del Edificio --}}
<div class="mb-3">
    <label for="name_{{ $fieldSuffix }}" class="custom-label">{{ __('Name') }}</label>
    <input
        type="text"
        name="name"
        id="name_{{ $fieldSuffix }}"
        class="form-control custom-input {{ $isActiveForm && $formErrors->has('name') ? 'is-invalid' : '' }}"
        value="{{ $nameValue }}"
        placeholder="Enter building name"
        maxlength="100"
        required
        @if($isActiveForm && $formErrors->has('name')) aria-invalid="true" @endif
    >
</div>

{{-- Campo numérico: Cantidad de Pisos --}}
<div class="mb-3">
    <label for="floors_{{ $fieldSuffix }}" class="custom-label">{{ __('Floors') }}</label>
    <input
        type="number"
        name="floors"
        id="floors_{{ $fieldSuffix }}"
        class="form-control custom-input {{ $isActiveForm && $formErrors->has('floors') ? 'is-invalid' : '' }}"
        value="{{ $floorsValue }}"
        placeholder="Number of floors"
        min="1"
        required
        @if($isActiveForm && $formErrors->has('floors')) aria-invalid="true" @endif
    >
</div>

{{-- Campo ENUM: Estado del edificio. Los valores deben coincidir exactamente con MySQL. --}}
<div class="mb-3">
    <label for="status_{{ $fieldSuffix }}" class="custom-label">{{ __('Status') }}</label>
    <select
        name="status"
        id="status_{{ $fieldSuffix }}"
        class="form-select custom-input {{ $isActiveForm && $formErrors->has('status') ? 'is-invalid' : '' }}"
        required
        @if($isActiveForm && $formErrors->has('status')) aria-invalid="true" @endif
    >
        <option value="available" {{ $statusValue === 'available' ? 'selected' : '' }}>Available</option>
        <option value="maintenance" {{ $statusValue === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
    </select>
</div>
