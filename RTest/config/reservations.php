<?php

/**
 * Reglas de tiempo de las reservaciones.
 * Cambia estos valores aquí; no hay que tocar el código.
 */
return [

    // La reservación completa (inicio y fin) debe caer dentro de los próximos N días.
    'max_days_ahead' => 30,

    // Duración mínima de una reservación, en horas (24 = 1 día).
    'min_duration_hours' => 24,

];