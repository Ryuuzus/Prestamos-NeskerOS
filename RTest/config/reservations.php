<?php

/**
 * Reglas de tiempo de las reservaciones.
 * Cambia estos valores aquí; no hay que tocar el código.
 */
return [

    // Anticipación mínima: el inicio de la reservación debe ser al menos N días después de hoy.
    'min_days_in_advance' => 7,

    // Duración mínima y máxima de una reservación, en horas (2 horas mínimo, 48 = 2 días máximo).
    'min_duration_hours' => 2,
    'max_duration_hours' => 48,

    // Horario permitido (hora de apertura y de cierre, en formato 24 horas) y paso entre horas.
    // Con 7, 21 y 60 las opciones son 7:00 AM, 8:00 AM ... 9:00 PM. Con 30 habría también las y media.
    'open_hour'    => 7,
    'close_hour'   => 21,
    'slot_minutes' => 60,

    // Opcional: hasta cuántos días hacia adelante se puede reservar (null = sin límite).
    'max_days_ahead' => null,

];
