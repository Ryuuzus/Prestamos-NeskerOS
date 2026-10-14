<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\BuildingRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\BuildingExport;
use App\Imports\BuildingImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controlador para la gestión de edificios.
 * 
 * Administra las operaciones CRUD para el modelo Building,
 * incluyendo listado con búsqueda y paginación, creación,
 * consulta detallada, edición y eliminación de registros.
 */
class BuildingController extends Controller
{
    /**
     * Muestra el listado de edificios con opción de búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        // Filtramos la base de datos si hay búsqueda, si no, traemos todos
        $buildings = Building::when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        })->paginate(10);

        return view('building.index', compact('buildings'))
            ->with('i', (request()->input('page', 1) - 1) * $buildings->perPage());
    }

    /**
     * Muestra el formulario para crear un nuevo edificio.
     */
    public function create(): View
    {
        $building = new Building();

        return view('building.create', compact('building'));
    }

    /**
     * Almacena un nuevo edificio en la base de datos previa validación.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Validamos que sea único en la tabla 'buildings', columna 'name'
            'name' => 'required|string|max:100|unique:buildings,name',
            'floors' => 'required|integer|min:1',
        ]);

        Building::create($request->all());

        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    /**
     * Muestra los detalles de un edificio específico.
     */
    public function show($id): View
    {
        $building = Building::find($id);

        return view('building.show', compact('building'));
    }

    /**
     * Muestra el formulario para editar un edificio existente.
     */
    public function edit($id): View
    {
        $building = Building::find($id);

        return view('building.edit', compact('building'));
    }

    /**
     * Actualiza la información de un edificio en la base de datos previa validación.
     */
    public function update(Request $request, Building $building)
    {
        $request->validate([
            // Al editar, le decimos a unique que ignore el ID actual ($building->id)
            'name' => 'required|string|max:100|unique:buildings,name,' . $building->id,
            'floors' => 'required|integer|min:1',
        ]);

        $building->update($request->all());

        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    /**
     * Elimina el edificio especificado de la base de datos.
     */
    public function destroy($id): RedirectResponse
    {
        Building::find($id)->delete();

        return Redirect::route('buildings.index')
            ->with('success', 'Building deleted successfully');
    }

        public function export()
    {
        return Excel::download(new BuildingExport, 'edificios.xlsx');
    }

    /**
     * Importa edificios desde un archivo Excel o CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048'
        ]);

        Excel::import(new BuildingImport, $request->file('file'));

        return back()->with('success', '¡Edificios importados exitosamente!');
    }
}