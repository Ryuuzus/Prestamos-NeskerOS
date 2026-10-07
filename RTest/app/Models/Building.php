<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Edificio (Building).
 * 
 * Representa la infraestructura física donde se ubican las aulas dentro de la institución.
 * Administra el número de niveles y la relación uno a muchos con las aulas asociadas.
 * ==================================================================================================
 */
class Building extends Model
{
    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'floors',
    ];

    /**
     * Relación con las aulas pertenecientes a este edificio.
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'buildings_id', 'id');
    }
}