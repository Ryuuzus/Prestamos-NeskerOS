<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Aula (Classroom).
 * 
 * Representa las aulas o salones disponibles para reservación. Relaciona cada espacio
 * con su respectivo edificio y mantiene el historial de reservaciones vinculadas.
 * ==================================================================================================
 * 
 * @property int $id
 * @property int $buildings_id
 * @property string $classroom
 * @property int $floor
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property Building $building
 * @property Reservation[] $reservations
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Classroom extends Model
{
    /**
     * Cantidad de registros por página por defecto en paginación.
     *
     * @var int
     */
    protected $perPage = 20;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = ['buildings_id', 'classroom', 'floor', 'status'];

    /**
     * Relación con el edificio al que pertenece el aula.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building()
    {
        return $this->belongsTo(Building::class, 'buildings_id');
    }
    
    /**
     * Relación con las reservaciones asociadas a esta aula.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class, 'classroom_id', 'id');
    }
}