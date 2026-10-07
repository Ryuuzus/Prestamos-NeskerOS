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
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Aulas (ClassroomController).
 * 
 * Administra las operaciones CRUD para la entidad Classroom,
 * permitiendo el listado con búsqueda y paginación, la consulta de detalles,
 * la creación, edición, eliminación e importación/exportación masiva de aulas.
 * ==================================================================================================
 */
class ClassroomController extends Controller
{
    /**
     * Muestra el listado de aulas con soporte para búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Búsqueda condicional sobre el campo de identificación del aula
        $classrooms = Classroom::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('classroom', 'LIKE', "%{$search}%");
            });
        })->paginate(10);

        // Obtención de edificios para asociar o filtrar en la vista principal
        $buildings = Building::all();

        return view('classroom.index', compact('classrooms', 'buildings'))
            ->with('i', ($request->input('page', 1) - 1) * $classrooms->perPage());
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
            ->with('success', 'Classroom created successfully.');
    }

    /**
     * Muestra la información detallada de una aula específica.
     */
    public function show($id): View
    {
        $classroom = Classroom::find($id);

        return view('classroom.show', compact('classroom'));
    }

    /**
     * Muestra el formulario para editar un aula existente.
     */
    public function edit($id): View
    {
        $classroom = Classroom::find($id);
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
            ->with('success', 'Classroom updated successfully');
    }

    /**
     * Elimina el registro del aula indicada de la base de datos.
     */
    public function destroy($id): RedirectResponse
    {
        Classroom::find($id)->delete();

        return Redirect::route('classrooms.index')
            ->with('success', 'Classroom deleted successfully');
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