<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Changes building status while preserving independently maintained classrooms. */
class BuildingStatusService
{
    /**
     * Actualiza el edificio y, si su estado cambió, sincroniza el estado de sus aulas.
     *
     * @param  array<string, mixed>  $data  Datos ya validados (name, floors, status).
     * @return int Cantidad de aulas cuyo estado fue modificado por el cambio de estado del edificio.
     *
     * @throws ValidationException Si se intenta poner en mantenimiento un edificio con aulas reservadas.
     */
    public function update(Building $building, array $data): int
    {
        return DB::transaction(function () use ($building, $data) {
            $building = Building::lockForUpdate()->findOrFail($building->id);
            $newStatus = $data['status'];
            $statusChanged = $building->status !== $newStatus;

            // Regla 1: no se puede entrar a mantenimiento con aulas que tengan reservaciones aprobadas.
            if ($statusChanged && $newStatus === Building::STATUS_MAINTENANCE) {
                // Se bloquean las aulas del edificio mientras se valida, para que nadie
                // pueda marcar una como ocupada justo en este instante.
                $classrooms = $building->classrooms()
                    ->lockForUpdate()
                    ->get(['id', 'classroom', 'status']);

                $reservedIds = Reservation::query()
                    ->approved()
                    ->whereIn('classroom_id', $classrooms->pluck('id'))
                    ->pluck('classroom_id')
                    ->unique();

                $blocked = $classrooms->filter(fn ($classroom) => $reservedIds->contains($classroom->id));

                if ($blocked->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'status' => $this->blockedMessage($blocked),
                    ])->errorBag('editBuilding'.$building->id);
                }
            }

            $building->update($data);

            // Reglas 2 y 3: sincronizar las aulas solo cuando el estado del edificio cambió.
            if (! $statusChanged) {
                return 0;
            }

            if ($newStatus === Building::STATUS_MAINTENANCE) {
                return $building->classrooms()->where('status', 'available')->update(['status' => 'maintenance', 'maintenance_from_building' => true]);
            }

            return $building->classrooms()->where('maintenance_from_building', true)->update(['status' => 'available', 'maintenance_from_building' => false]);
        }, 5);
    }

    /**
     * Mensaje de error con los nombres de las aulas que lo impiden (máximo 5).
     */
    private function blockedMessage(Collection $blocked): string
    {
        $names = $blocked->pluck('classroom');
        $shown = $names->take(5)->implode(', ');
        $rest = $names->count() - 5;

        $list = $rest > 0 ? "{$shown} y {$rest} más" : $shown;

        return "No se puede poner el edificio en mantenimiento porque tiene aulas con reservaciones aprobadas sin completar: {$list}.";
    }
}
