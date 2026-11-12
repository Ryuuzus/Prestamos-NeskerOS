<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function __construct(private ReservationService $reservations) {}

    public function report(Building|Classroom|Device $resource, string $reason): int
    {
        return DB::transaction(function () use ($resource, $reason) {
            $resource = $resource->newQuery()->lockForUpdate()->findOrFail($resource->id);
            $resource->update(['status' => 'maintenance']);
            if ($resource instanceof Building) {
                $classrooms = $resource->classrooms()->orderBy('id')->lockForUpdate()->get();
                foreach ($classrooms as $classroom) {
                    if ($classroom->status !== 'maintenance') {
                        $classroom->forceFill(['status' => 'maintenance', 'maintenance_from_building' => true])->save();
                    }
                }
                $query = Reservation::whereIn('classroom_id', $classrooms->modelKeys());
                $part = 'key';
                $label = "Edificio {$resource->name}";
            } elseif ($resource instanceof Classroom) {
                $resource->forceFill(['maintenance_from_building' => false])->save();
                $query = Reservation::where('classroom_id', $resource->id);
                $part = 'key';
                $label = "Aula {$resource->classroom}";
            } else {
                $query = Reservation::where('device_id', $resource->id);
                $part = 'devices';
                $label = "Equipo {$resource->name}";
            }
            $affected = $query->whereIn('status', ['pending', 'approved'])->orderBy('id')->lockForUpdate()->get();
            $note = mb_substr("{$label}: {$reason}", 0, 500);
            foreach ($affected as $reservation) {
                $this->reservations->affectedByMaintenance($reservation, $part, $note);
            }

            return $affected->count();
        }, 5);
    }
}
