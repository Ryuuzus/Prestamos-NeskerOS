<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_NO_SHOW = 'no_show';

    public const CLOSED_STATUSES = ['completed', 'rejected', 'cancelled', 'expired', 'no_show'];

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
        'key_issued_at', 'key_returned_at', 'key_released_at',
        'devices_issued_at', 'devices_returned', 'devices_returned_at', 'devices_released_at',
        'key_custody_unverified', 'devices_custody_unverified', 'closed_at', 'closure_reason',
    ];

    /**
     * Conversiones de tipo. Las horas (start_time / end_time) se conservan como texto "HH:MM:SS".
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'actual_return_time' => 'datetime',
            'key_issued_at' => 'datetime', 'key_returned_at' => 'datetime', 'key_released_at' => 'datetime',
            'devices_issued_at' => 'datetime', 'devices_returned_at' => 'datetime', 'devices_released_at' => 'datetime',
            'devices_returned' => 'integer', 'device_quantity' => 'integer',
            'key_custody_unverified' => 'boolean', 'devices_custody_unverified' => 'boolean', 'closed_at' => 'datetime',
        ];
    }

    /**
     * Momento completo de inicio (fecha + hora) como Carbon.
     */
    protected function startAt(): Attribute
    {
        return Attribute::get(fn () => $this->combine($this->start_date, $this->start_time));
    }

    /** Older completions were stored in UTC before the local operational timezone was configured. */
    protected function actualReturnTime(): Attribute
    {
        return Attribute::get(function (?string $value): ?Carbon {
            if ($value === null) {
                return null;
            }
            $legacy = $this->status === self::STATUS_COMPLETED && ! $this->closed_at
                && ! $this->key_issued_at && ! $this->devices_issued_at;

            return Carbon::parse($value, $legacy ? 'UTC' : config('app.timezone'))->setTimezone(config('app.timezone'));
        });
    }

    protected function startDate(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->toDateString());
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->toDateString());
    }

    protected function startTime(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->format('H:i:s'));
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(set: fn ($value) => Carbon::parse($value)->format('H:i:s'));
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

        return Carbon::parse($date->format('Y-m-d').' '.$time);
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
        return $query->where(fn ($q) => $q->where('start_date', '<', now()->toDateString())
            ->orWhere(fn ($q) => $q->where('start_date', now()->toDateString())->where('start_time', '<=', now()->format('H:i:s'))));
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
            ->where(fn ($q) => $q->where('start_date', '<', $end->toDateString())
                ->orWhere(fn ($q) => $q->where('start_date', $end->toDateString())->where('start_time', '<', $end->format('H:i:s'))))
            ->where(fn ($q) => $q->where('end_date', '>', $start->toDateString())
                ->orWhere(fn ($q) => $q->where('end_date', $start->toDateString())->where('end_time', '>', $start->format('H:i:s'))));
    }

    public function scopeKeyOutstanding(Builder $query): Builder
    {
        return $query->approved()->where(fn ($q) => $q->where('key_custody_unverified', true)
            ->orWhere(fn ($q) => $q->whereNotNull('key_issued_at')->whereNull('key_returned_at')));
    }

    public function scopeDevicesOutstanding(Builder $query): Builder
    {
        return $query->approved()->where(fn ($q) => $q->where('devices_custody_unverified', true)
            ->orWhere(fn ($q) => $q->whereNotNull('devices_issued_at')->whereColumn('devices_returned', '<', 'device_quantity')));
    }

    public function hasUnverifiedCustody(): bool
    {
        return $this->key_custody_unverified || $this->devices_custody_unverified;
    }

    public function hasIssuedResources(): bool
    {
        return $this->key_issued_at !== null || $this->devices_issued_at !== null;
    }

    public function hasOutstandingResources(): bool
    {
        return $this->hasUnverifiedCustody() || ($this->key_issued_at && ! $this->key_returned_at)
            || ($this->devices_issued_at && $this->devices_returned < $this->device_quantity);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true)
            && ! $this->hasIssuedResources() && ! $this->hasUnverifiedCustody();
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_APPROVED) {
            if ($this->hasUnverifiedCustody()) {
                return 'Por verificar entrega anterior';
            }
            if ($this->hasOutstandingResources()) {
                return $this->end_at->lte(now()) ? 'Devolución atrasada' : 'En préstamo';
            }

            return $this->end_at->lte(now()) ? 'Sin recoger / por cerrar' : 'Aprobada · pendiente de recogida';
        }

        return match ($this->status) {
            'pending' => 'Pendiente', 'completed' => 'Completada', 'rejected' => 'Rechazada',
            'cancelled' => 'Cancelada', 'expired' => 'Vencida sin aprobación', 'no_show' => 'Sin recoger',
            default => $this->status,
        };
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReservationEvent::class);
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
