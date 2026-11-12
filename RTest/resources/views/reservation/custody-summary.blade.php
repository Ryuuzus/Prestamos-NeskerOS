<details class="reservation-detail">
    <summary>Ver detalle <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
    <div class="reservation-detail-body">
        @if($reservation->classroom_id)
            <p><strong>Llave</strong>
                @if($reservation->key_custody_unverified)Entrega anterior por verificar.
                @elseif($reservation->key_returned_at)Devuelta · {{ $reservation->key_returned_at->format('d/m/Y H:i') }}.
                @elseif($reservation->key_issued_at)Por devolver · entregada el {{ $reservation->key_issued_at->format('d/m/Y H:i') }}.
                @elseif($reservation->key_released_at)Liberada sin recoger.
                @elseif($reservation->status === 'completed')Registro anterior sin detalle de entrega.
                @elseSin entrega registrada.
                @endif
            </p>
        @endif
        @if($reservation->device_id)
            <p><strong>Equipos</strong>
                @if($reservation->devices_custody_unverified)Entrega anterior por verificar.
                @elseif($reservation->devices_issued_at){{ $reservation->devices_returned }} de {{ $reservation->device_quantity }} devueltos.
                @elseif($reservation->devices_released_at)Liberados sin recoger.
                @elseif($reservation->status === 'completed')Registro anterior sin detalle de entrega.
                @elseSin entrega registrada.
                @endif
            </p>
        @endif
        @if($reservation->actual_return_time)
            <p><strong>Devolución final</strong>{{ $reservation->actual_return_time->format('d/m/Y H:i') }}</p>
        @endif
        @if($reservation->closure_reason || $reservation->rejection_reason)
            <p><strong>Motivo</strong>{{ $reservation->closure_reason ?? $reservation->rejection_reason }}</p>
        @endif
    </div>
</details>
