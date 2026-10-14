<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

/**
<<<<<<< HEAD
 * Class Device
 *
 * @property $id
 * @property $name
 * @property $serial_number
 * @property $status
 * @property $created_at
 * @property $updated_at
 *
 * @property DeviceRequest[] $deviceRequests
 * @property DeviceReservation[] $deviceReservations
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Device extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'serial_number', 'status'];


    
    /**
     * @return HasMany
=======
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Dispositivo (Device).
 * 
 * Representa los equipos y dispositivos tecnológicos asignables.
 * Permite llevar el control de números de serie, estados de disponibilidad y solicitudes de reserva.
 * ==================================================================================================
 */
class Device extends Model
{
    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'serial_number',
        'status',
    ];

    /**
     * Relación con las reservaciones vinculadas a este dispositivo.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'device_id', 'id');
    }
<<<<<<< HEAD
    
=======
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
}
