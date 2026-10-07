<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\DeviceExport;
use App\Imports\DeviceImport;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Dispositivos (DeviceController).
 * 
 * Gestiona el mantenimiento del catálogo de equipos/dispositivos (CRUD),
 * la navegación hacia el centro de importación/exportación y la transferencia masiva de datos.
 * ==================================================================================================
 */
class DeviceController extends Controller
{
    /**
     * Muestra la vista principal del módulo Excel para importar/exportar.
     */
    public function home(): View
    {
        return view('excel.index');
    }

    /**
     * Muestra el listado de dispositivos almacenados con soporte de búsqueda y paginación.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $devices = Device::when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('serial_number', 'LIKE', "%{$search}%");
        })->paginate(10);

        return view('device.index', compact('devices'))
            ->with('i', (request()->input('page', 1) - 1) * $devices->perPage());
    }

    /**
     * Muestra el formulario para registrar un nuevo dispositivo.
     */
    public function create(): View
    {
        $device = new Device();
        return view('device.create', compact('device'));
    }

    /**
     * Valida y almacena un nuevo dispositivo en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'serial_number' => 'required|string|max:15|unique:devices,serial_number',
            'status'        => 'required|in:available,maintenance,occupied',
        ]);

        Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Dispositivo creado exitosamente.');
    }

    /**
     * Muestra la información detallada de un dispositivo específico.
     */
    public function show($id): View
    {
        $device = Device::findOrFail($id);
        return view('device.show', compact('device'));
    }

    /**
     * Muestra el formulario para editar un dispositivo existente.
     */
    public function edit($id): View
    {
        $device = Device::findOrFail($id);
        return view('device.edit', compact('device'));
    }

    /**
     * Valida y actualiza los datos de un dispositivo en la base de datos.
     */
    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100|unique:devices,name,' . $device->id,
            'serial_number' => 'required|string|max:15|unique:devices,serial_number,' . $device->id,
            'status'        => 'required|in:available,maintenance,occupied',
        ]);

        $device->update($validated);

        return redirect()->route('devices.index')->with('success', 'Dispositivo actualizado exitosamente.');
    }

    /**
     * Elimina el registro del dispositivo indicado.
     */
    public function destroy($id): RedirectResponse
    {
        Device::findOrFail($id)->delete();

        return Redirect::route('devices.index')
            ->with('success', 'Dispositivo eliminado exitosamente.');
    }

    /**
     * Exporta los dispositivos a un archivo Excel (.xlsx).
     */
    public function export()
    {
        return Excel::download(new DeviceExport, 'dispositivos.xlsx');
    }

    /**
     * Importa dispositivos masivamente desde un archivo Excel o CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        Excel::import(new DeviceImport, $request->file('file'));

        return back()->with('success', '¡Dispositivos importados exitosamente!');
    }
}