<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class Building extends Model
{
    protected $perPage = 20;

    protected $fillable = ['name', 'floors'];

    /**
     * Relación con Classrooms (Clave foránea en classrooms, clave local en buildings)
     */
    public function classrooms()
    {
        return $this->hasMany(\App\Models\Classroom::class, 'buildings_id', 'id');
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    }
}