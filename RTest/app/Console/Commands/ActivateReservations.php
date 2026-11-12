<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

/** Reconciles elapsed requests without assuming physical delivery or return. */
class ActivateReservations extends Command
{
    protected $signature = 'reservations:activate';

    protected $description = 'Cierra solicitudes vencidas y reservas sin recoger; conserva préstamos pendientes de devolución';

    public function handle(ReservationService $reservations): int
    {
        $changed = $reservations->reconcileDue();

        $this->info("Reservaciones actualizadas: {$changed}");

        return self::SUCCESS;
    }
}
