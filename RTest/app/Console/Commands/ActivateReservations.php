<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Pone en "occupied" las aulas y dispositivos cuya reservación aprobada ya llegó a su fecha y hora
 * de inicio. Se programa para correr cada minuto (ver routes/console.php).
 *
 * Si este comando no corre, los horarios siguen protegidos (los cruces se validan por fecha y hora),
 * pero el estado "occupied" no se reflejará en las aulas y dispositivos.
 * ==================================================================================================
 */
class ActivateReservations extends Command
{
    protected $signature = 'reservations:activate';

    protected $description = 'Marca como ocupados las aulas y dispositivos cuya reservación aprobada ya inició';

    public function handle(ReservationService $reservations): int
    {
        $changed = $reservations->activateStarted();

        $this->info("Recursos marcados como ocupados: {$changed}");

        return self::SUCCESS;
    }
}
