{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista principal del módulo de Edificios (Buildings). 
    Muestra el listado de edificios en una tabla interactiva con búsqueda y paginación,
    permite abrir modales de Bootstrap para crear y editar registros, e incluye la lógica
    en JavaScript para reabrir automáticamente el modal correspondiente en caso de errores de validación.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Nombre de la sección del título en el layout --}}
@section('Buildings')
    Buildings
@endsection

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-10">
            
            {{-- Alerta de confirmación de operaciones exitosas --}}
            @if ($message = Session::get('success'))
                <div class="custom-alert shadow-sm">
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            <div class="table-wrapper">
                
                {{-- Encabezado con título de sección, filtro de búsqueda y botón de creación --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 id="card_title" class="m-0">{{ __('Edificios') }}</h4>
                    
                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">
                        
                        {{-- Formulario de búsqueda rápida --}}
                        <form action="{{ route('buildings.index') }}" method="GET" class="m-0 d-flex" style="max-width: 300px; width: 100%;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control custom-input" placeholder="Search..." value="{{ request('search') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; height: 100%;">
                                <button type="submit" class="btn-submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin: 0; padding: 8px 16px;">
                                    Buscar
                                </button>
                            </div>
                        </form>

                        {{-- Botón para restablecer el filtro de búsqueda --}}
                        @if(request('search'))
                            <a href="{{ route('buildings.index') }}" class="btn btn-light" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Clear Search">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        {{-- Botón para abrir el modal de creación --}}
                        <button type="button" class="btn-create text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa fa-plus"></i> {{ __('Create') }}
                        </button>

                    </div>
                </div>

                {{-- Listado principal de edificios --}}
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="building-id-column">No</th>
                                <th class="building-name-column">Edificio</th>
                                <th class="building-floors-column">Pisos</th>
                                <th class="building-status-column">Estado</th>
                                <th class="building-actions-column">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($buildings as $building)
                                <tr>
                                    <td class="building-id-column"><span class="text-muted fw-bold">{{ $building->id }}</span></td>
                                    <td class="building-name-cell fw-semibold" title="{{ $building->name }}">{{ $building->name }}</td>
                                    <td class="building-floors-column">{{ $building->floors }}</td>
                                    <td class="building-status-column">
                                        @if($building->status === 'available')
                                            <span class="building-status-badge status-available">Disponible</span>
                                        @else
                                            <span class="building-status-badge status-maintenance">Mantenimiento</span>
                                        @endif
                                    </td>
                                    
                                    {{-- Acciones de edición (vía modal) y eliminación del registro --}}
                                    <td class="building-actions-column">
                                        <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="action-btns">
                                            @csrf
                                            @method('DELETE')


                                            {{-- Botón para detonar el modal de edición --}}
                                            <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $building->id }}">
                                                <i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}
                                            </button>

                                            {{-- Botón con confirmación nativa para eliminación --}}
                                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('{{ __('Are you sure you want to delete this building?') }}')">

                                                <i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                {{-- Inclusión de la vista parcial del modal de edición --}}
                                @include('building.edit')
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Enlaces de paginación conservando los parámetros de búsqueda --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $buildings->withQueryString()->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- Modal parcial para la creación de un nuevo edificio --}}
@include('building.create', ['building' => new \App\Models\Building()])

{{-- Reabre únicamente el modal que contiene los errores de validación del CRUD de edificios. --}}
@php
    $validationEditingId = old('editing_id');
    $createBuildingHasErrors = $errors->getBag('createBuilding')->any();
    $editBuildingHasErrors = $validationEditingId
        && $errors->getBag('editBuilding' . $validationEditingId)->any();
@endphp

@if($createBuildingHasErrors || $editBuildingHasErrors)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editingId = @json($validationEditingId);
            const modalId = editingId ? 'editModal' + editingId : 'createModal';
            const modalElement = document.getElementById(modalId);

            if (modalElement && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        });
    </script>
@endif

@endsection

