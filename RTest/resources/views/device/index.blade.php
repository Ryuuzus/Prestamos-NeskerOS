{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista principal del módulo de Dispositivos (Devices).
    Permite visualizar la lista de dispositivos registrados, filtrar resultados mediante la barra de
    búsqueda, gestionar la paginación y realizar operaciones CRUD mediante modales de creación/edición.
    El diseño sigue el del CRUD de Edificios (columnas centradas, badges tipo píldora,
    botones Edit/Delete de texto).
--================================================================================================== --}}

@extends('layouts.app')

@section('Devices')
    Devices
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-10">

            {{-- Mensaje de éxito --}}
            @if ($message = Session::get('success'))
                <div class="custom-alert shadow-sm">
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            {{-- Mensaje de error (por ejemplo, al eliminar un dispositivo con reservaciones) --}}
            @if ($message = Session::get('error'))
                <div class="custom-alert custom-alert-error shadow-sm">
                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                </div>
            @endif

            <div class="table-wrapper">

                {{-- Encabezado: título, buscador y botón Create --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 id="card_title" class="m-0">{{ __('Dispositivos') }}</h4>

                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">

                        <form action="{{ route('devices.index') }}" method="GET" class="m-0 d-flex" style="max-width: 300px; width: 100%;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control custom-input"
                                       placeholder="Search..." value="{{ request('search') }}"
                                       style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <button type="submit" class="btn-submit"
                                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin: 0; padding: 8px 16px;">
                                    Buscar
                                </button>
                            </div>
                        </form>

                        @if(request('search'))
                            <a href="{{ route('devices.index') }}" class="btn btn-light"
                               style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Limpiar búsqueda">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        @can('manage-catalogs')
                        <button type="button" class="btn-create text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                            {{ __('Create') }}
                        </button>
                        @endcan
                    </div>
                </div>

                {{-- Tabla --}}
                {{-- El estado guardado solo distingue disponible / mantenimiento; las unidades en uso se calculan con las reservaciones aprobadas que ya iniciaron. --}}

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="building-id-column">No</th>
                                <th>Dispositivo</th>
                                <th>Número serial</th>
                                <th class="building-floors-column" title="Disponibles ahora / total">Unidades</th>
                                <th class="building-status-column">Estado</th>
                                @can('manage-catalogs')
                                <th class="building-actions-column">Acciones</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($devices as $device)
                                <tr>
                                    <td class="building-id-column"><span class="text-muted fw-bold">{{ $device->id }}</span></td>

                                    <td class="building-name-cell fw-semibold">{{ $device->name }}</td>

                                    <td class="building-name-cell">{{ $device->serial_number }}</td>

                                    <td class="building-floors-column" title="Disponibles ahora / total">{{ $device->available_units_now }} / {{ $device->quantity }}</td>

                                    <td class="building-status-column">
                                        @if($device->status === 'maintenance')
                                            <span class="building-status-badge status-maintenance">Mantenimiento</span>
                                        @elseif($device->available_units_now === 0)
                                            <span class="building-status-badge status-occupied">Sin unidades ahora</span>
                                        @else
                                            <span class="building-status-badge status-available">Disponible</span>
                                        @endif
                                    </td>

                                    @can('manage-catalogs')
                                    <td class="building-actions-column">
                                        <form action="{{ route('devices.destroy', $device->id) }}" method="POST" class="action-btns">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $device->id }}">
                                                {{ __('Edit') }}
                                            </button>

                                            <button type="submit" class="btn-action btn-delete"
                                                    onclick="return confirm('{{ __('Are you sure you want to delete this device?') }}')">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No se encontraron dispositivos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación conservando la búsqueda --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $devices->withQueryString()->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- Modales de edición: fuera de la tabla (un <div> dentro de <tbody> es HTML inválido) --}}
@can('manage-catalogs')
@foreach ($devices as $device)
    @include('device.edit')
@endforeach

{{-- Modal de creación --}}
@include('device.create', ['device' => new \App\Models\Device()])
@endcan

{{-- Reabre el modal (crear o editar) que tuvo errores de validación --}}
@if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editingId = @json(old('editing_id'));
            const modalEl = document.getElementById(editingId ? 'editModal' + editingId : 'createModal');

            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        });
    </script>
@endif
@endsection
