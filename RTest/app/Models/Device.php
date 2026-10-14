<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

/**
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
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'device_id', 'id');
    }
    
}
