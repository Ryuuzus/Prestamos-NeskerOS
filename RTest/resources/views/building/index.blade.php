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

                        {{-- Botón de exportación reservado (deshabilitado por el momento) --}}
                        {{-- <a href="{{ route('pdf.exportar-todas') }}" class="btn btn-light" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Exportar a PDF"></a> --}}
                    </div>
                </div>

                {{-- Listado principal de edificios --}}
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th>Edificio</th>
                                <th>Pisos</th>
                                <th style="width: 280px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($buildings as $building)
                                <tr>
                                    <td><span class="text-muted fw-bold">{{ $building->id }}</span></td>
                                    <td class="fw-semibold">{{ $building->name }}</td>
                                    <td>{{ $building->floors }}</td>
                                    
                                    {{-- Acciones de edición (vía modal) y eliminación del registro --}}
                                    <td>
                                        <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="action-btns">
                                            @csrf
                                            <input type="hidden" name="editing_id" value="{{ $building->id }}">
                                            
                                            {{-- Botón para detonar el modal de edición --}}
                                            <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $building->id }}">
                                                <i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}
                                            </button>
                                            
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;">
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

{{-- Script JS para la reapertura automática del modal correspondiente en caso de errores de validación --}}
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