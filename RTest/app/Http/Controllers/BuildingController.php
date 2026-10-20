<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Services\BuildingStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\BuildingExport;
use App\Imports\BuildingImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Edificios (BuildingController).
 *
 * Administra las operaciones CRUD para el modelo Building usando Route Model Binding,
 * filtrado seguro y exportación/importación en Excel.
 * El cambio de estado de un edificio se delega a BuildingStatusService, que mantiene
 * sincronizado el estado de sus aulas.
 * ==================================================================================================
 */
class BuildingController extends Controller
{
    /**
     * Muestra el listado de edificios con opción de búsqueda y paginación.
     * Incluye el conteo de aulas con reservaciones aprobadas por edificio para avisar en el formulario
     * cuando un edificio no puede pasar a mantenimiento.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $buildings = Building::withCount([
                'classrooms as reserved_classrooms_count' => fn ($query) => $query->whereHas('reservations', fn ($r) => $r->approved()),
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%");
                });
            })->paginate(10);

        return view('building.index', compact('buildings'));
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
     * Almacena un nuevo edificio en la base de datos validando los datos recibidos.
     * Un edificio nuevo no tiene aulas, por lo que no hay nada que sincronizar.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('createBuilding', [
            'name'   => 'required|string|max:100|unique:buildings,name',
            'floors' => 'required|integer|min:1',
            'status' => 'required|in:available,maintenance',
        ]);

        Building::create($validated);

        return redirect()->route('buildings.index')->with('success', 'Edificio creado exitosamente.');
    }

    /**
     * Muestra los detalles de un edificio específico.
     */
    public function show(Building $building): View
    {
        $building->load([
            'classrooms' => fn ($query) => $query->orderBy('floor')->orderBy('classroom'),
        ]);

        return view('building.show', compact('building'));
    }

    /**
     * Muestra el formulario para editar un edificio existente.
     */
    public function edit(Building $building): View
    {
        return view('building.edit', compact('building'));
    }

    /**
     * Actualiza el edificio. Si su estado cambia:
     *  - a mantenimiento: se bloquea si hay aulas ocupadas; si no, todas sus aulas pasan a mantenimiento.
     *  - a disponible: sus aulas vuelven a disponible.
     */
    public function update(Request $request, Building $building, BuildingStatusService $statusService): RedirectResponse
    {
        $validated = $request->validateWithBag('editBuilding' . $building->id, [
            'name'   => 'required|string|max:100|unique:buildings,name,' . $building->id,
            'floors' => 'required|integer|min:1',
            'status' => 'required|in:available,maintenance',
        ]);

        // Lanza ValidationException (con el bag del modal correcto) si hay aulas ocupadas.
        $affected = $statusService->update($building, $validated);

        $message = 'Edificio actualizado exitosamente.';

        if ($affected > 0) {
            $message .= $validated['status'] === Building::STATUS_MAINTENANCE
                ? " {$affected} aula(s) pasaron a mantenimiento."
                : " {$affected} aula(s) volvieron a estar disponibles.";
        }

        return redirect()->route('buildings.index')->with('success', $message);
    }

    /**
     * Elimina el edificio especificado de la base de datos.
     */
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
    {
        return Excel::download(new BuildingExport, 'edificios.xlsx');
    }

    /**
     * Importa edificios masivamente desde un archivo Excel o CSV.
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
