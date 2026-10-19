<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error de regla de negocio en una reservación (por ejemplo, aprobar una reservación
 * cuyo aula está en mantenimiento). El mensaje está pensado para mostrarse al usuario.
 */
class ReservationException extends RuntimeException
{
}
