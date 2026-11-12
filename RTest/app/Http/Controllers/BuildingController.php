<?php

namespace App\Http\Controllers;

use App\Exports\BuildingExport;
use App\Http\Requests\BuildingRequest;
use App\Imports\BuildingImport;
use App\Models\Building;
use App\Services\BuildingStatusService;
use App\Services\CatalogSpreadsheetService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

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
        $search = $request->input('search', '');

        $buildings = Building::withCount([
            'classrooms as reserved_classrooms_count' => fn ($query) => $query->whereHas('reservations', fn ($r) => $r->approved()),
        ])
            ->withMax('classrooms as highest_classroom_floor', 'floor')
            ->when($search !== '', function ($query) use ($search) {
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
    public function create(): RedirectResponse
    {
        return redirect()->route('buildings.index');
    }

    /**
     * Almacena un nuevo edificio en la base de datos validando los datos recibidos.
     * Un edificio nuevo no tiene aulas, por lo que no hay nada que sincronizar.
     */
    public function store(BuildingRequest $request): RedirectResponse
    {
        Building::create($request->validated());

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
    public function edit(Building $building): RedirectResponse
    {
        return redirect()->route('buildings.index');
    }

    /**
     * Actualiza el edificio. Si su estado cambia:
     *  - a mantenimiento: se bloquea si hay aulas ocupadas; si no, todas sus aulas pasan a mantenimiento.
     *  - a disponible: sus aulas vuelven a disponible.
     */
    public function update(BuildingRequest $request, Building $building, BuildingStatusService $statusService): RedirectResponse
    {
        $validated = $request->validated();

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
        if ($building->classrooms()->whereHas('reservations')->exists()) {
            return Redirect::route('buildings.index')->with('error', 'No se puede eliminar el edificio: sus aulas conservan reservaciones e historial de llaves.');
        }
        try {
            $building->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return Redirect::route('buildings.index')->with('error', 'No se puede eliminar el edificio: sus aulas tienen reservaciones asociadas.');
            }
            throw $e;
        }

        return Redirect::route('buildings.index')
            ->with('success', 'Edificio eliminado exitosamente.');
    }

    /**
     * Exporta el catálogo de edificios a un archivo Excel (.xlsx).
     */
    public function export(CatalogSpreadsheetService $spreadsheets)
    {
        return $spreadsheets->export(new BuildingExport, 'edificios.xlsx', 'Edificios');
    }

    /**
     * Importa edificios masivamente desde un archivo Excel o CSV.
     */
    public function import(Request $request, CatalogSpreadsheetService $spreadsheets): RedirectResponse
    {
        return $spreadsheets->import($request, new BuildingImport, 'Edificios');
    }
}
