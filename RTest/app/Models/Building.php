<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    }
}