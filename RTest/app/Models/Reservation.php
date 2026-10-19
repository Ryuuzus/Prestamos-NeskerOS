<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Reservación (Reservation).
 *
 * Gestiona el registro centralizado de reservaciones de recursos (aulas o dispositivos).
 * La fecha y la hora se guardan en columnas separadas:
 *   start_date (DATE) + start_time (TIME)  ->  inicio
 *   end_date   (DATE) + end_time   (TIME)  ->  fin
 * Para trabajar con el momento completo se usan los accesores $reservation->start_at y ->end_at.
 * ==================================================================================================
 */
class Reservation extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'classroom_id',
        'device_id',
        'device_quantity',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'status',
        'rejection_reason',
        'actual_return_time',
    ];

    /**
     * Conversiones de tipo. Las horas (start_time / end_time) se conservan como texto "HH:MM:SS".
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date'         => 'date',
            'end_date'           => 'date',
            'actual_return_time' => 'datetime',
        ];
    }

    /**
     * Momento completo de inicio (fecha + hora) como Carbon.
     */
    protected function startAt(): Attribute
    {
        return Attribute::get(fn () => $this->combine($this->start_date, $this->start_time));
    }

    /**
     * Momento completo de fin (fecha + hora) como Carbon.
     */
    protected function endAt(): Attribute
    {
        return Attribute::get(fn () => $this->combine($this->end_date, $this->end_time));
    }

    private function combine(?CarbonInterface $date, mixed $time): ?Carbon
    {
        if (! $date || ! $time) {
            return null;
        }

        return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
    }

    /**
     * Reservaciones aprobadas.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Reservaciones cuyo inicio (fecha + hora) ya llegó.
     */
    public function scopeStarted(Builder $query): Builder
    {
        return $query->whereRaw('TIMESTAMP(reservations.start_date, reservations.start_time) <= ?', [now()->format('Y-m-d H:i:s')]);
    }

    /**
     * Reservaciones cuyo horario se cruza con [$start, $end).
     * Dos reservaciones que se tocan en el borde (una termina cuando la otra empieza) NO se cruzan.
     *
     * Usa TIMESTAMP(fecha, hora) de MySQL para combinar las dos columnas.
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $start, CarbonInterface $end): Builder
    {
        return $query
            ->whereRaw('TIMESTAMP(reservations.start_date, reservations.start_time) < ?', [$end->format('Y-m-d H:i:s')])
            ->whereRaw('TIMESTAMP(reservations.end_date, reservations.end_time) > ?', [$start->format('Y-m-d H:i:s')]);
    }

    /**
     * Relación con el usuario solicitante de la reservación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el aula reservada (si aplica).
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relación con el dispositivo reservado (si aplica).
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
