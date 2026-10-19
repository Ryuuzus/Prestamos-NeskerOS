<?php

/**
 * Reglas de tiempo de las reservaciones.
 * Cambia estos valores aquí; no hay que tocar el código.
 */
return [

    // Anticipación mínima: el inicio de la reservación debe ser al menos N días después de hoy.
    'min_days_in_advance' => 7,

    // Duración mínima de una reservación, en horas (24 = 1 día).
    'min_duration_hours' => 24,

    // Opcional: hasta cuántos días hacia adelante se puede reservar (null = sin límite).
    'max_days_ahead' => null,

    // Opcional: duración máxima de una reservación, en horas (null = sin límite).
    'max_duration_hours' => null,

];
