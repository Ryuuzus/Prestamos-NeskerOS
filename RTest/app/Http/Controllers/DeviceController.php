<?php

namespace App\Http\Controllers;

use App\Exports\DeviceExport;
use App\Http\Requests\DeviceRequest;
use App\Imports\DeviceImport;
use App\Models\Device;
use App\Rules\DeviceQuantityCoversReservations;
use App\Rules\NoActiveReservations;
use App\Services\CatalogSpreadsheetService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Dispositivos (DeviceController).
 *
 * Gestiona el mantenimiento del catálogo de equipos/dispositivos (CRUD) usando
 * Route Model Binding, validación centralizada en DeviceRequest y transferencia
 * masiva de datos en Excel. La creación y edición se realizan desde modales del index.
 * ==================================================================================================
 */
class DeviceController extends Controller
{
    /**
     * Muestra la vista principal del centro de importación/exportación.
     */
    public function home(): View
    {
        return view('excel.index');
    }

    /**
     * Muestra el listado de dispositivos con búsqueda (nombre o número de serie) y paginación.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        // units_in_use = unidades en uso ahora (reservaciones aprobadas que ya iniciaron y no se han completado).
        $devices = Device::query()
            ->withSum(['reservations as units_in_use' => fn ($query) => $query->devicesOutstanding()], DB::raw('device_quantity - devices_returned'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('serial_number', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate(10);

        return view('device.index', compact('devices'));
    }

    /**
     * La creación se hace desde el modal del index; esta ruta redirige para evitar
     * renderizar un parcial (modal) sin layout.
     */
    public function create(): RedirectResponse
    {
        return Redirect::route('devices.index');
    }

    /**
     * Valida y almacena un nuevo dispositivo en la base de datos.
     */
    public function store(DeviceRequest $request): RedirectResponse
    {
        Device::create($request->validated());

        return Redirect::route('devices.index')
            ->with('success', 'Dispositivo creado exitosamente.');
    }

    /**
     * No existe vista de detalle independiente; se regresa al listado.
     */
    public function show(Device $device): RedirectResponse
    {
        return Redirect::route('devices.index');
    }

    /**
     * La edición se hace desde el modal del index; esta ruta redirige al listado.
     */
    public function edit(Device $device): RedirectResponse
    {
        return Redirect::route('devices.index');
    }

    /**
     * Valida y actualiza los datos de un dispositivo.
     * El número de serie único ignora al propio dispositivo (ver DeviceRequest).
     */
    public function update(DeviceRequest $request, Device $device): RedirectResponse
    {
        DB::transaction(function () use ($request, $device) {
            $device = Device::lockForUpdate()->findOrFail($device->id);
            $data = $request->validated();
            Validator::make($data, [
                'quantity' => [new DeviceQuantityCoversReservations($device)],
                'status' => [new NoActiveReservations($device, 'device_id')],
            ])->validate();
            $device->update($data);
        }, 5);

        return Redirect::route('devices.index')
            ->with('success', 'Dispositivo actualizado exitosamente.');
    }

    /**
     * Elimina el dispositivo. Si tiene reservaciones asociadas (llave foránea),
     * muestra un mensaje claro en lugar de una pantalla de error.
     */
    public function destroy(Device $device): RedirectResponse
    {
        if ($device->reservations()->exists()) {
            return Redirect::route('devices.index')->with('error', 'No se puede eliminar el equipo: conserva reservaciones e historial de préstamos. Puedes ponerlo en mantenimiento.');
        }
        try {
            $device->delete();
        } catch (QueryException $e) {
            // 23000 = violación de integridad referencial
            if ($e->getCode() === '23000') {
                return Redirect::route('devices.index')
                    ->with('error', 'No se puede eliminar el dispositivo porque tiene reservaciones asociadas.');
            }

            throw $e;
        }

        return Redirect::route('devices.index')
            ->with('success', 'Dispositivo eliminado exitosamente.');
    }

    /**
     * Exporta los dispositivos a un archivo Excel (.xlsx).
     */
    public function export(CatalogSpreadsheetService $spreadsheets)
    {
        return $spreadsheets->export(new DeviceExport, 'dispositivos.xlsx', 'Dispositivos');
    }

    /**
     * Importa dispositivos masivamente desde un archivo Excel o CSV.
     */
    public function import(Request $request, CatalogSpreadsheetService $spreadsheets): RedirectResponse
    {
        return $spreadsheets->import($request, new DeviceImport, 'Dispositivos');
    }
}
