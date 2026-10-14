<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Http\Requests\BuildingRequest;
=======
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\BuildingExport;
use App\Imports\BuildingImport;
use Maatwebsite\Excel\Facades\Excel;

/**
<<<<<<< HEAD
 * Controlador para la gestión de edificios.
 * 
 * Administra las operaciones CRUD para el modelo Building,
 * incluyendo listado con búsqueda y paginación, creación,
 * consulta detallada, edición y eliminación de registros.
=======
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Edificios (BuildingController).
 * 
 * Administra las operaciones CRUD para el modelo Building usando Route Model Binding,
 * filtrado seguro y exportación/importación en Excel.
 * ==================================================================================================
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
 */
class BuildingController extends Controller
{
    /**
     * Muestra el listado de edificios con opción de búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

<<<<<<< HEAD
        // Filtramos la base de datos si hay búsqueda, si no, traemos todos
=======
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        $buildings = Building::when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        })->paginate(10);

<<<<<<< HEAD
        return view('building.index', compact('buildings'))
            ->with('i', (request()->input('page', 1) - 1) * $buildings->perPage());
=======
        return view('building.index', compact('buildings'));
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
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
<<<<<<< HEAD
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
=======
     * Almacena un nuevo edificio en la base de datos validando los datos recibidos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100|unique:buildings,name',
            'floors' => 'required|integer|min:1',
        ]);

        Building::create($validated);

        return redirect()->route('buildings.index')->with('success', 'Edificio creado exitosamente.');
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    }

    /**
     * Muestra los detalles de un edificio específico.
     */
<<<<<<< HEAD
    public function show($id): View
    {
        $building = Building::find($id);

=======
    public function show(Building $building): View
    {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        return view('building.show', compact('building'));
    }

    /**
     * Muestra el formulario para editar un edificio existente.
     */
<<<<<<< HEAD
    public function edit($id): View
    {
        $building = Building::find($id);

=======
    public function edit(Building $building): View
    {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        return view('building.edit', compact('building'));
    }

    /**
<<<<<<< HEAD
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
=======
     * Actualiza la información de un edificio en la base de datos.
     */
    public function update(Request $request, Building $building): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100|unique:buildings,name,' . $building->id,
            'floors' => 'required|integer|min:1',
        ]);

        $building->update($validated);

        return redirect()->route('buildings.index')->with('success', 'Edificio actualizado exitosamente.');
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    }

    /**
     * Elimina el edificio especificado de la base de datos.
     */
<<<<<<< HEAD
    public function destroy($id): RedirectResponse
    {
        Building::find($id)->delete();

        return Redirect::route('buildings.index')
            ->with('success', 'Building deleted successfully');
    }

        public function export()
=======
    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();

        return Redirect::route('buildings.index')
            ->with('success', 'Edificio eliminado exitosamente.');
    }

    /**
     * Exporta el catálogo de edificios a un archivo Excel (.xlsx).
     */
    public function export()
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    {
        return Excel::download(new BuildingExport, 'edificios.xlsx');
    }

    /**
<<<<<<< HEAD
     * Importa edificios desde un archivo Excel o CSV.
=======
     * Importa edificios masivamente desde un archivo Excel o CSV.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048'
        ]);

        Excel::import(new BuildingImport, $request->file('file'));

        return back()->with('success', '¡Edificios importados exitosamente!');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
