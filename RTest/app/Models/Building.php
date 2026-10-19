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
 * Administra el número de niveles, su estado y la relación uno a muchos con las aulas asociadas.
 * Las reglas que ligan el estado del edificio con el de sus aulas viven en
 * App\Services\BuildingStatusService.
 * ==================================================================================================
 */
class Building extends Model
{
    public const STATUS_AVAILABLE   = 'available';
    public const STATUS_MAINTENANCE = 'maintenance';

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'floors',
        'status',
    ];

    /**
     * Relación con las aulas pertenecientes a este edificio.
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'buildings_id', 'id');
    }

    /**
     * ¿El edificio está en mantenimiento?
     */
    public function isUnderMaintenance(): bool
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }
}
