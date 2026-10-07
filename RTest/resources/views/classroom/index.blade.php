{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista principal de gestión de Aulas (Classrooms). Permite listar, buscar, crear, 
    editar y eliminar aulas mediante tablas interactivas y ventanas modales de Bootstrap, 
    así como actualizar dinámicamente las opciones de pisos según el edificio seleccionado.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Nombre de la sección actual --}}
@section('classroom')
    Classrooms
@endsection

{{-- Contenido principal de la página --}}
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-10">
            
            {{-- Mensaje de éxito al completar una acción --}}
            @if ($message = Session::get('success'))
                <div class="custom-alert shadow-sm">
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
            @endif

            <div class="table-wrapper">
                
                {{-- Encabezado con título, buscador y botón de nuevo registro --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 id="card_title" class="m-0">{{ __('Aulas') }}</h4>
                    
                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">
                        
                        {{-- Formulario de búsqueda --}}
                        <form action="{{ route('classrooms.index') }}" method="GET" class="m-0 d-flex" style="max-width: 300px; width: 100%;">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control custom-input" placeholder="Search..." value="{{ request('search') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; height: 100%;">
                                <button type="submit" class="btn-submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin: 0; padding: 8px 16px;">
                                    Buscar
                                </button>
                            </div>
                        </form>

                        {{-- Botón para restablecer el filtro de búsqueda --}}
                        @if(request('search'))
                            <a href="{{ route('classrooms.index') }}" class="btn btn-light" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Clear Search">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        {{-- Botón para activar el modal de creación --}}
                        <button type="button" class="btn-create text-nowrap" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fa fa-plus"></i> {{ __('Create') }}
                        </button>
                    </div>
                </div>

                {{-- Tabla de listado de registros --}}
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th>Edificio</th>
                                <th>Aula</th>
                                <th>Piso</th>
                                <th>Estado</th>
                                <th style="width: 280px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classrooms as $classroom)
                                <tr>
                                    <td><span class="text-muted fw-bold">{{ $classroom->id }}</span></td>
                                    
                                    {{-- Relación con el modelo de Edificio --}}
                                    <td class="fw-semibold">{{ $classroom->building->name ?? 'Edificio desconocido' }}</td>
                                    
                                    <td>{{ $classroom->classroom }}</td>
                                    <td>{{ $classroom->floor }}</td>
                                    
                                    {{-- Insignia según el estado del aula --}}
                                    <td>
                                        @if($classroom->status == 'available')
                                            <span class="badge bg-success" style="border-radius: 6px; padding: 6px 10px;">Available</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="border-radius: 6px; padding: 6px 10px;">{{ ucfirst($classroom->status) }}</span>
                                        @endif
                                    </td>

                                    {{-- Acciones de edición y eliminación --}}
                                    <td>
                                        <form action="{{ route('classrooms.destroy', $classroom->id) }}" method="POST" class="action-btns">
                                            @csrf
                                            @method('DELETE')

                                            {{-- Botón para abrir modal de edición --}}
                                            <button type="button" class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $classroom->id }}">
                                                <i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}
                                            </button>
                                            
                                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('{{ __('Are you sure you want to delete this classroom?') }}')">
                                                <i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal de edición para cada aula en la iteración --}}
                                @include('classroom.edit')
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Enlaces de paginación conservando los parámetros de búsqueda --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $classrooms->withQueryString()->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- Modal para crear una nueva aula --}}
@include('classroom.create', ['classroom' => new \App\Models\Classroom()])

{{-- Lógica JavaScript unificada para poblar pisos dinámicamente y reabrir modales con errores --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Función centralizada para popular selectores de pisos según el edificio seleccionado
    function populateFloors(buildingSelect, floorSelect, preselectedFloor = null) {
        if (!buildingSelect || !floorSelect) return;

        floorSelect.innerHTML = '<option value="">-- Select a Floor --</option>';
        
        const selectedOption = buildingSelect.options[buildingSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const maxFloors = parseInt(selectedOption.getAttribute('data-floors'));
            
            if(!isNaN(maxFloors)) {
                for (let i = 1; i <= maxFloors; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i;
                    
                    if (preselectedFloor && parseInt(preselectedFloor) === i) {
                        option.selected = true;
                    }
                    
                    floorSelect.appendChild(option);
                }
            }
        }
    }

    // Inicializa todos los elementos select de edificios
    const buildingSelects = document.querySelectorAll('.dynamic-building-select');

    buildingSelects.forEach(function(buildingSelect) {
        const targetId = buildingSelect.getAttribute('data-target');
        const floorSelect = document.querySelector(targetId);
        
        if (floorSelect) {
            const savedFloor = floorSelect.getAttribute('data-selected-floor');
            populateFloors(buildingSelect, floorSelect, savedFloor);

            buildingSelect.addEventListener('change', function() {
                populateFloors(buildingSelect, floorSelect, null); 
            });
        }
    });

    // Reapertura automática de modales en caso de errores de validación del servidor
    @if($errors->any())
        var editingId = "{{ old('editing_id') }}";
        
        if (editingId) {
            var editModalEl = document.getElementById('editModal' + editingId);
            if (editModalEl) {
                var editModal = new bootstrap.Modal(editModalEl);
                editModal.show();
            }
        } else {
            var createModalEl = document.getElementById('createModal');
            if (createModalEl) {
                var createModal = new bootstrap.Modal(createModalEl);
                createModal.show();
            }
        }
    @endif
});
</script>

@endsection
