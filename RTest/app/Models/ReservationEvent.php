<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationEvent extends Model
{
    protected $fillable = ['actor_id', 'action', 'quantity', 'reason', 'occurred_at'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'quantity' => 'integer'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function label(): string
    {
        return match ($this->action) {
            'approved' => 'Aprobación', 'rejected' => 'Rechazo',
            'issue_key' => 'Entrega de llave', 'return_key' => 'Devolución de llave',
            'issue_devices' => 'Entrega de equipos', 'return_devices' => 'Devolución de equipos',
            'release' => 'Liberación de recursos sin recoger', 'cancelled' => 'Cancelación',
            'no_show' => 'Sin recoger', 'expired' => 'Solicitud vencida', 'completed' => 'Cierre del préstamo',
            'maintenance' => 'Incidencia de mantenimiento',
            'verify_key' => 'Confirmación de custodia de llave anterior',
            'verify_devices' => 'Confirmación de custodia de equipos anteriores',
            default => $this->action,
        };
    }
}
