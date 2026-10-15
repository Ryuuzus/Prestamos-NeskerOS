<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Reglas de negocio del estado de un Edificio y su efecto sobre sus Aulas.
 *
 *  1. Un edificio NO puede pasar a "maintenance" si alguna de sus aulas está "occupied".
 *  2. Al pasar a "maintenance", TODAS sus aulas pasan a "maintenance".
 *  3. Al volver a "available", sus aulas vuelven a "available" y desde ahí cada aula puede
 *     cambiar de forma individual (available / occupied / maintenance).
 *  4. Si el estado del edificio no cambia (por ejemplo, solo se edita el nombre), NO se toca
 *     el estado de ninguna aula.
 *
 * Todo ocurre dentro de una transacción: o se aplica completo o no se aplica nada.
 * ==================================================================================================
 */
class BuildingStatusService
{
    /**
     * Actualiza el edificio y, si su estado cambió, sincroniza el estado de sus aulas.
     *
     * @param  array<string, mixed>  $data  Datos ya validados (name, floors, status).
     * @return int  Cantidad de aulas cuyo estado fue modificado por el cambio de estado del edificio.
     *
     * @throws ValidationException  Si se intenta poner en mantenimiento un edificio con aulas ocupadas.
     */
    public function update(Building $building, array $data): int
    {
        return DB::transaction(function () use ($building, $data) {
            $newStatus     = $data['status'];
            $statusChanged = $building->status !== $newStatus;

            // Regla 1: no se puede entrar a mantenimiento con aulas ocupadas.
            if ($statusChanged && $newStatus === Building::STATUS_MAINTENANCE) {
                // Se bloquean las aulas del edificio mientras se valida, para que nadie
                // pueda marcar una como ocupada justo en este instante.
                $occupied = $building->classrooms()
                    ->lockForUpdate()
                    ->get(['id', 'classroom', 'status'])
                    ->where('status', 'occupied');

                if ($occupied->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'status' => $this->occupiedMessage($occupied),
                    ])->errorBag('editBuilding' . $building->id);
                }
            }

            $building->update($data);

            // Reglas 2 y 3: sincronizar las aulas solo cuando el estado del edificio cambió.
            if (! $statusChanged) {
                return 0;
            }

            return $building->classrooms()->update([
                'status' => $newStatus === Building::STATUS_MAINTENANCE ? 'maintenance' : 'available',
            ]);
        });
    }

    /**
     * Mensaje de error con los nombres de las aulas ocupadas (máximo 5).
     */
    private function occupiedMessage(Collection $occupied): string
    {
        $names = $occupied->pluck('classroom');
        $shown = $names->take(5)->implode(', ');
        $rest  = $names->count() - 5;

        $list = $rest > 0 ? "{$shown} y {$rest} más" : $shown;

        return "No se puede poner el edificio en mantenimiento porque tiene aulas ocupadas: {$list}.";
    }
}