<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ClassroomRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\ClassroomExport;
use App\Imports\ClassroomImport;
use Maatwebsite\Excel\Facades\Excel;

/**
<<<<<<< HEAD
 * Controlador de Gestión de Aulas (ClassroomController)
 * 
 * Administra las operaciones CRUD para la entidad Classroom,
 * permitiendo el listado con búsqueda y paginación, la consulta de detalles,
 * y la creación, edición y eliminación de aulas en relación con sus edificios.
=======
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Aulas (ClassroomController).
 * 
 * Administra las operaciones CRUD de aulas incorporando Eager Loading (with)
 * para evitar el problema de consultas N+1 y resolviendo modelos de forma implícita.
 * ==================================================================================================
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
 */
class ClassroomController extends Controller
{
    /**
<<<<<<< HEAD
     * Muestra el listado de aulas con soporte para búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Búsqueda condicional sobre las columnas classroom, floor y status
        $classrooms = Classroom::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('classroom', 'LIKE', "%{$search}%");
            });
        })->paginate(10);

        // Obtención de edificios para asociar o filtrar en la vista principal
        $buildings = Building::all();

        return view('classroom.index', compact('classrooms', 'buildings'))
            ->with('i', ($request->input('page', 1) - 1) * $classrooms->perPage());
=======
     * Muestra el listado de aulas con precarga de relaciones (Eager Loading), búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        // Se incluye eager loading (with('building')) para optimizar el rendimiento de la consulta SQL
        $classrooms = Classroom::with('building')
            ->when($search, function ($query, $search) {
                $query->where('classroom', 'LIKE', "%{$search}%");
            })->paginate(10);

        $buildings = Building::all();

        return view('classroom.index', compact('classrooms', 'buildings'));
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    }

    /**
     * Muestra el formulario para registrar una nueva aula.
     */
    public function create(): View
    {
        $classroom = new Classroom();
        $buildings = Building::all();

        return view('classroom.create', compact('classroom', 'buildings'));
    }

    /**
     * Valida y almacena una nueva aula en la base de datos.
     */
    public function store(ClassroomRequest $request): RedirectResponse
    {
        Classroom::create($request->validated());

        return Redirect::route('classrooms.index')
<<<<<<< HEAD
            ->with('success', 'Classroom created successfully.');
    }

    /**
     * Muestra la información detallada de una aula específica.
     */
    public function show($id): View
    {
        $classroom = Classroom::find($id);

=======
            ->with('success', 'Aula creada exitosamente.');
    }

    /**
     * Muestra la información detallada de un aula específica.
     */
    public function show(Classroom $classroom): View
    {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        return view('classroom.show', compact('classroom'));
    }

    /**
     * Muestra el formulario para editar un aula existente.
     */
<<<<<<< HEAD
    public function edit($id): View
    {
        $classroom = Classroom::find($id);
=======
    public function edit(Classroom $classroom): View
    {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        $buildings = Building::all();

        return view('classroom.edit', compact('classroom', 'buildings'));
    }

    /**
     * Valida y actualiza los datos de un aula en la base de datos.
     */
    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return Redirect::route('classrooms.index')
<<<<<<< HEAD
            ->with('success', 'Classroom updated successfully');
=======
            ->with('success', 'Aula actualizada exitosamente.');
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    }

    /**
     * Elimina el registro del aula indicada de la base de datos.
     */
<<<<<<< HEAD
    public function destroy($id): RedirectResponse
    {
        Classroom::find($id)->delete();

        return Redirect::route('classrooms.index')
            ->with('success', 'Classroom deleted successfully');
    }

=======
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return Redirect::route('classrooms.index')
            ->with('success', 'Aula eliminada exitosamente.');
    }

    /**
     * Exporta el catálogo de aulas/salones a un archivo Excel (.xlsx).
     */
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    public function export()
    {
        return Excel::download(new ClassroomExport, 'salones.xlsx');
    }

    /**
<<<<<<< HEAD
     * Importa edificios desde un archivo Excel o CSV.
=======
     * Importa aulas masivamente desde un archivo Excel o CSV.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
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