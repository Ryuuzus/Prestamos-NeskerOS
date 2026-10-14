<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

/**
 * Class Classroom
 *
 * @property $id
 * @property $buildings_id
 * @property $classroom
 * @property $floor
 * @property $status
 * @property $created_at
 * @property $updated_at
 *
 * @property Building $building
 * @property Reservation[] $reservations
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Classroom extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['buildings_id', 'classroom', 'floor', 'status'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building()
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    {
        return $this->belongsTo(Building::class, 'buildings_id');
    }
    
    /**
<<<<<<< HEAD
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class, 'classroom_id', 'id');
    }
    
=======
     * Relación con las reservaciones asociadas a esta aula.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'classroom_id', 'id');
    }
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
}
