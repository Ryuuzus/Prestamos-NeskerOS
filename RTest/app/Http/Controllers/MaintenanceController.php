<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceRequest;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;

class MaintenanceController extends Controller
{
    public function store(MaintenanceRequest $request, string $type, int $id, MaintenanceService $service): RedirectResponse
    {
        $model = match ($type) {
            'building' => Building::class, 'classroom' => Classroom::class, 'device' => Device::class,
            default => abort(404),
        };
        $affected = $service->report($model::findOrFail($id), $request->validated('reason'));
        $route = match ($type) {
            'building' => 'buildings.index', 'classroom' => 'classrooms.index', 'device' => 'devices.index'
        };

        return redirect()->route($route)->with('success', "Mantenimiento registrado. Se revisaron {$affected} reservación(es); las entregas pendientes de devolución siguen abiertas.");
    }
}
