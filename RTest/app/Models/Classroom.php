<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    {
        return $this->belongsTo(Building::class, 'buildings_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function reservations()
    {
        return $this->hasMany(\App\Models\Reservation::class, 'classroom_id', 'id');
    }
    
}
