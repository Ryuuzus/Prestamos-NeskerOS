{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Ventana modal de Bootstrap para la creación de un nuevo registro de Aula (Create Classroom Modal).
    Proporciona el formulario para almacenar la información inicial (Edificio, Nombre del Aula,
    Piso y Estado) enviando los datos mediante el método POST a la ruta 'classrooms.store'.
--================================================================================================== --}} 

{{-- Estructura principal del Modal de Creación --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            {{-- Encabezado del Modal --}}
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createModalLabel">Create Classroom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Formulario de creación con método POST --}}
            <form action="{{ route('classrooms.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    {{-- Selección de Edificio --}}
                    <div class="mb-3">
                        <label for="buildings_id" class="form-label text-muted small mb-1">Building</label>
                        <select name="buildings_id" id="create_building_select" class="form-select" required>
                            <option value="">-- Select a Building --</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->id }}" data-floors="{{ $building->floors }}">
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Campo de texto para el Nombre del Aula --}}
                    <div class="mb-3">
                        <label for="classroom" class="form-label text-muted small mb-1">Classroom Name</label>
                        <input type="text" name="classroom" class="form-control bg-light" placeholder="Ej: Casado" required>
                    </div>

                    {{-- Selección de Piso (poblado dinámicamente mediante JS) --}}
                    <div class="mb-3">
                        <label for="floor" class="form-label text-muted small mb-1">Floor</label>
                        <select name="floor" id="create_floor_select" class="form-select" required>
                            <option value="">-- Select a Floor --</option>
                        </select>
                    </div>

                    {{-- Selección de Estado del Aula --}}
                    <div class="mb-3">
                        <label for="status" class="form-label text-muted small mb-1">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Select Status --</option>
                            <option value="available" selected>Available</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div>

                </div>
                
                {{-- Pie del Modal con botones de acción --}}
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white" style="background-color: #5558ff;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>