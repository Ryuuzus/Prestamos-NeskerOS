<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['name', 'floors'];

    /**
     * Relación con las aulas pertenecientes a este edificio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classrooms()
    {
        return $this->hasMany(\App\Models\Classroom::class, 'buildings_id', 'id');
    }
}