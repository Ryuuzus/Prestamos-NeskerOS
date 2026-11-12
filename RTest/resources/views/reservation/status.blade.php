@php
    [$statusText, $statusTone, $statusIcon] = match ($reservation->status) {
        'pending' => ['Pendiente', 'amber', 'clock'],
        'completed' => ['Completada', 'green', 'check'],
        'rejected' => ['Rechazada', 'rose', 'xmark'],
        'cancelled' => ['Cancelada', 'slate', 'ban'],
        'expired' => ['Vencida', 'slate', 'clock'],
        'no_show' => ['Sin recoger', 'slate', 'calendar-xmark'],
        default => $reservation->hasUnverifiedCustody()
            ? ['Por verificar', 'amber', 'circle-question']
            : ($reservation->hasOutstandingResources()
                ? ($reservation->end_at->lte(now()) ? ['Atrasada', 'rose', 'clock'] : ['En préstamo', 'indigo', 'arrow-right-arrow-left'])
                : ($reservation->end_at->lte(now()) ? [$reservation->hasIssuedResources() ? 'Por cerrar' : 'Sin recoger', 'slate', 'clock'] : ['Aprobada', 'green', 'check'])),
    };
@endphp
<span class="reservation-status reservation-status--{{ $statusTone }}" title="{{ $reservation->statusLabel() }}">
    <i class="fa-solid fa-{{ $statusIcon }}" aria-hidden="true"></i>{{ $statusText }}
</span>
