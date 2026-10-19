<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Building;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ClassroomRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\ClassroomExport;
use App\Imports\ClassroomImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Aulas (ClassroomController).
 *
 * Administra las operaciones CRUD de aulas incorporando Eager Loading (with)
 * para evitar el problema de consultas N+1 y resolviendo modelos de forma implícita.
 * La creación y edición se realizan desde modales dentro de la vista index.
 * ==================================================================================================
 */
class ClassroomController extends Controller
{
    /**
     * Muestra el listado de aulas con precarga de relaciones (Eager Loading), búsqueda y paginación.
     * La búsqueda coincide con el nombre del aula o con el nombre de su edificio.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        // in_use = el aula tiene una reservación aprobada que ya inició y no se ha completado.
        $classrooms = Classroom::with('building')
            ->withExists(['reservations as in_use' => fn ($query) => $query->approved()->started()])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('classroom', 'LIKE', "%{$search}%")
                      ->orWhereHas('building', fn ($b) => $b->where('name', 'LIKE', "%{$search}%"));
                });
            })
            ->orderBy('id')
            ->paginate(10);

        $buildings = Building::orderBy('name')->get();

        return view('classroom.index', compact('classrooms', 'buildings'));
    }

    /**
     * La creación se hace desde el modal del index; esta ruta redirige para evitar
     * renderizar un parcial (modal) sin layout.
     */
    public function create(): RedirectResponse
    {
        return Redirect::route('classrooms.index');
    }

    /**
     * Valida y almacena una nueva aula en la base de datos.
     */
    public function store(ClassroomRequest $request): RedirectResponse
    {
        Classroom::create($request->validated());

        return Redirect::route('classrooms.index')
            ->with('success', 'Aula creada exitosamente.');
    }

    /**
     * No existe vista de detalle independiente; se regresa al listado.
     */
    public function show(Classroom $classroom): RedirectResponse
    {
        return Redirect::route('classrooms.index');
    }

    /**
     * La edición se hace desde el modal del index; esta ruta redirige al listado.
     */
    public function edit(Classroom $classroom): RedirectResponse
    {
        return Redirect::route('classrooms.index');
    }

    /**
     * Valida y actualiza los datos de un aula en la base de datos.
     */
    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return Redirect::route('classrooms.index')
            ->with('success', 'Aula actualizada exitosamente.');
    }

    /**
     * Elimina el aula. Si tiene reservaciones asociadas (llave foránea), muestra un
     * mensaje claro en lugar de una pantalla de error.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        try {
            $classroom->delete();
        } catch (QueryException $e) {
            // 23000 = violación de integridad referencial
            if ($e->getCode() === '23000') {
                return Redirect::route('classrooms.index')
                    ->with('error', 'No se puede eliminar el aula porque tiene reservaciones asociadas.');
            }

            throw $e;
        }

        return Redirect::route('classrooms.index')
            ->with('success', 'Aula eliminada exitosamente.');
    }

    /**
     * Exporta el catálogo de aulas/salones a un archivo Excel (.xlsx).
     */
    public function export()
    {
        return Excel::download(new ClassroomExport, 'salones.xlsx');
    }

    /**
     * Importa aulas masivamente desde un archivo Excel o CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048'
        ]);

        Excel::import(new ClassroomImport, $request->file('file'));

        return back()->with('success', '¡Salones importados exitosamente!');
    }
}
