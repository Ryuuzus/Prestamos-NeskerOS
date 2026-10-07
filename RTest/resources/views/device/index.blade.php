{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista principal del módulo de Dispositivos (Devices). 
    Permite visualizar la lista de dispositivos registrados, filtrar resultados mediante la barra de búsqueda,
    gestionar la paginación y realizar operaciones CRUD mediante modales de creación/edición y acciones directas.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Definición del título de la sección --}}
@section('Devices')
    Devices
@endsection

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-10">
            
            {{-- Alerta de éxito al completar operaciones --}}
            @if ($message = Session::get('success'))
                <div class="custom-alert shadow-sm">
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            <div class="table-wrapper">
                
                {{-- Encabezado con título de sección, buscador y botón para abrir el Modal de Creación --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 id="card_title" class="m-0">{{ __('Dispositivos') }}</h4>
                    
                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">
                        
                        {{-- Formulario de búsqueda --}}
                        <form action="{{ route('devices.index') }}" method="GET" class="m-0 d-flex" style="max-width: 300px; width: 100%;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control custom-input" placeholder="Search..." value="{{ request('search') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; height: 100%;">
                                <button type="submit" class="btn-submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin: 0; padding: 8px 16px;">
                                    Buscar
                                </button>
                            </div>
                        </form>

                        {{-- Botón para restablecer el filtro de búsqueda --}}
                        @if(request('search'))
                            <a href="{{ route('devices.index') }}" class="btn btn-light" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Clear Search">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        {{-- Botón para detonar el modal de creación --}}
                        <button type="button" class="btn-create text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa fa-plus"></i> {{ __('Create') }}
                        </button>
                    </div>
                </div>

                {{-- Tabla de listado de dispositivos --}}
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th>Dispositivo</th>
                                <th>Número serial</th>
                                <th>Estado</th>
                                <th style="width: 280px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td><span class="text-muted fw-bold">{{ $device->id }}</span></td>
                                    <td class="fw-semibold">{{ $device->name }}</td>
                                    <td>{{ $device->serial_number }}</td>
                                    
                                    {{-- Estilo dinámico para la insignia del Estado --}}
                                    <td>
                                        @if($device->status == 'available')
                                            <span class="badge bg-success" style="border-radius: 6px; padding: 6px 10px;">Available</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="border-radius: 6px; padding: 6px 10px;">{{ ucfirst($device->status) }}</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Formulario para eliminación y detonador del modal de edición --}}
                                    <td>
                                        <form action="{{ route('devices.destroy', $device->id) }}" method="POST" class="action-btns">
                                            @csrf
                                            <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $device->id }}">
                                                <i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}
                                            </button>
                                            
                                            <input type="hidden" name="editing_id" value="{{ $device->id }}">
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;">
                                                <i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                {{-- Inclusión parcial del modal de edición para cada dispositivo --}}
                                @include('device.edit')
                            
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación conservando parámetros de consulta --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $devices->withQueryString()->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- Inclusión del modal de creación --}}
@include('device.create', ['device' => new App\Models\Device()])

{{-- Lógica JS para reabrir automáticamente el modal correspondiente si existen errores de validación --}}
@if($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            var editingId = "{{ old('editing_id') }}";
            
            if(editingId) {
                var editModal = new bootstrap.Modal(document.getElementById('editModal' + editingId));
                editModal.show();
            } else {
                var createModal = new bootstrap.Modal(document.getElementById('createModal'));
                createModal.show();
            }
        });
    </script>
@endif

@endsection