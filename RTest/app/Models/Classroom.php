<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Aula (Classroom).
 * 
 * Representa las aulas o salones disponibles para reservación. Relaciona cada espacio
 * con su respectivo edificio y mantiene el historial de reservaciones vinculadas.
 * ==================================================================================================
 */
class Classroom extends Model
{
    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'buildings_id',
        'classroom',
        'floor',
        'status',
    ];

    /**
     * Relación con el edificio al que pertenece el aula.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'buildings_id');
    }
    
    /**
     * Relación con las reservaciones asociadas a esta aula.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'classroom_id', 'id');
    }
}
