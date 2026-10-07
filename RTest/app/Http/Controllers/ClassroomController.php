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
 * Administra las operaciones CRUD de aulas incorporando Eager Loading (with)
 * para evitar el problema de consultas N+1 y resolviendo modelos de forma implícita.
 * ==================================================================================================
 */
class ClassroomController extends Controller
{
    /**
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
            ->with('success', 'Aula creada exitosamente.');
    }

    /**
     * Muestra la información detallada de un aula específica.
     */
    public function show(Classroom $classroom): View
    {
        return view('classroom.show', compact('classroom'));
    }

    /**
     * Muestra el formulario para editar un aula existente.
     */
    public function edit(Classroom $classroom): View
    {
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
            ->with('success', 'Aula actualizada exitosamente.');
    }

    /**
     * Elimina el registro del aula indicada de la base de datos.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

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