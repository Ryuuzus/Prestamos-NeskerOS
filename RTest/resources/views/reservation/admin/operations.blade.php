@extends('layouts.app')

@section('content')
@php
    $isActive = $reservation->status === 'approved';
    $canIssue = $isActive && ! $reservation->hasUnverifiedCustody() && $reservation->start_at->lte(now()) && $reservation->end_at->gt(now());
@endphp
<div class="app-content reservation-page reservation-operation-page">
    <div class="reservation-navigation">
        <a href="{{ route($isActive || $reservation->status === 'pending' ? 'admin.reservations.index' : 'reservations.history') }}" class="reservation-text-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Reservaciones</a>
        @include('reservation.guide-link')
    </div>
    @include('reservation.alerts')

    <div class="reservation-page-heading">
        <div>
            <p class="reservation-eyebrow">Entrega y devolución</p>
            <h1 class="app-page-title">Préstamo #{{ $reservation->id }}</h1>
        </div>
        @include('reservation.status')
    </div>

    <div class="reservation-overview">
        <div class="reservation-overview-person">
            <span class="reservation-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::substr($reservation->user?->name ?? 'U', 0, 1) }}</span>
            <div><span class="reservation-meta-label">Solicitante</span><strong>{{ $reservation->user?->name ?? 'Cuenta eliminada' }}</strong></div>
        </div>
        <div><span class="reservation-meta-label">Inicio</span><strong>{{ $reservation->start_at->format('d/m/Y · H:i') }}</strong></div>
        <div><span class="reservation-meta-label">Devolución prevista</span><strong>{{ $reservation->end_at->format('d/m/Y · H:i') }}</strong></div>
    </div>

    @if($isActive && $reservation->hasUnverifiedCustody())
        <p class="reservation-notice reservation-notice--amber"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Confirma las entregas anteriores para continuar.</p>
    @elseif($isActive && $reservation->start_at->gt(now()))
        <p class="reservation-notice"><i class="fa-regular fa-clock" aria-hidden="true"></i>La entrega se habilita al comenzar el horario.</p>
    @elseif($isActive && $reservation->hasOutstandingResources() && $reservation->end_at->lte(now()))
        <p class="reservation-notice reservation-notice--rose"><i class="fa-solid fa-clock" aria-hidden="true"></i>El horario terminó. Registra los recursos que regresen.</p>
    @endif

    <div class="reservation-resource-grid">
        @foreach(['key' => 'Llave del aula', 'devices' => 'Equipos'] as $part => $label)
            @php
                $isKey = $part === 'key';
                $exists = $isKey ? $reservation->classroom_id : $reservation->device_id;
                $issued = $reservation->{$part.'_issued_at'};
                $returned = $reservation->{$part.'_returned_at'};
                $released = $reservation->{$part.'_released_at'};
                $unverified = $reservation->{$part.'_custody_unverified'};
                $quantity = $isKey ? 1 : $reservation->device_quantity;
                $received = $isKey ? ($returned ? 1 : 0) : $reservation->devices_returned;
                $resourceName = $isKey ? $reservation->classroom?->classroom : $reservation->device?->name;
            @endphp
            @if($exists)
                <section class="reservation-resource-card" aria-labelledby="resource-{{ $part }}">
                    <div class="reservation-resource-heading">
                        <span class="reservation-resource-icon" aria-hidden="true"><i class="fa-solid fa-{{ $isKey ? 'key' : 'laptop' }}"></i></span>
                        <div>
                            <p class="reservation-meta-label">{{ $label }}</p>
                            <h2 id="resource-{{ $part }}">{{ $resourceName ?? 'Recurso no disponible' }}</h2>
                        </div>
                        @if($returned)<span class="reservation-complete-icon" title="Devolución completa"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>@endif
                    </div>

                    <div class="reservation-resource-progress">
                        @if($unverified)
                            <strong class="reservation-resource-message">Entrega por confirmar</strong>
                            <span class="reservation-resource-caption">Reservación anterior al registro de entregas.</span>
                        @elseif($issued)
                            @if($isKey)
                                <strong class="reservation-resource-message">{{ $returned ? 'Llave recibida' : 'Llave por devolver' }}</strong>
                            @else
                                <div class="reservation-resource-number">{{ $received }} <span>/ {{ $quantity }}</span></div>
                                <span class="reservation-resource-caption">equipos devueltos</span>
                                <progress value="{{ $received }}" max="{{ $quantity }}" aria-label="{{ $label }}: {{ $received }} de {{ $quantity }} devueltos"></progress>
                            @endif
                        @elseif($released)
                            <strong class="reservation-resource-message">Sin recoger</strong>
                            <span class="reservation-resource-caption">Recurso liberado.</span>
                        @elseif($reservation->status === 'completed')
                            <strong class="reservation-resource-message">Préstamo cerrado</strong>
                            <span class="reservation-resource-caption">Registro anterior sin detalle de entrega.</span>
                        @elseif(! $isActive)
                            <strong class="reservation-resource-message">{{ $reservation->status === 'pending' ? 'Por aprobar' : 'Sin entrega registrada' }}</strong>
                            <span class="reservation-resource-caption">{{ $quantity }} {{ $isKey ? 'llave solicitada' : 'equipos solicitados' }}</span>
                        @elseif($reservation->end_at->lte(now()))
                            <strong class="reservation-resource-message">Sin recoger</strong>
                            <span class="reservation-resource-caption">El horario terminó.</span>
                        @else
                            <div class="reservation-resource-number">{{ $quantity }}</div>
                            <span class="reservation-resource-caption">{{ $isKey ? 'llave por entregar' : 'equipos por entregar' }}</span>
                        @endif
                    </div>

                    @if($isActive && $unverified)
                        <details class="reservation-verification" @if($errors->any() && old('action') === 'verify_'.$part) open @endif>
                            <summary class="btn-submit"><i class="fa-solid fa-check-to-slot" aria-hidden="true"></i>Verificar entrega anterior</summary>
                            <form method="POST" action="{{ route('reservations.operations.update', $reservation) }}" class="reservation-inline-form">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="verify_{{ $part }}">
                                <label for="custody-{{ $part }}" class="form-label">¿Se {{ $isKey ? 'entregó la llave' : 'entregaron todos los equipos' }}?</label>
                                <select class="form-select mb-3" name="custody" id="custody-{{ $part }}" required data-custody-select>
                                    <option value="">Selecciona</option>
                                    <option value="issued">Sí, se entregó</option>
                                    <option value="not_issued">No se entregó</option>
                                </select>
                                <div data-custody-date hidden>
                                    <label class="form-label" for="at-{{ $part }}">Fecha y hora de entrega</label>
                                    <input type="datetime-local" class="form-control mb-3" name="occurred_at" id="at-{{ $part }}" min="{{ $reservation->start_at->format('Y-m-d\\TH:i') }}" max="{{ now()->format('Y-m-d\\TH:i') }}">
                                </div>
                                <label class="form-label" for="verify-reason-{{ $part }}">Cómo se confirmó</label>
                                <textarea name="reason" id="verify-reason-{{ $part }}" class="form-control mb-3" maxlength="500" rows="2" required></textarea>
                                <button class="btn-submit">Guardar verificación</button>
                            </form>
                        </details>
                    @elseif($isActive && $issued && ! $returned)
                        <form method="POST" action="{{ route('reservations.operations.update', $reservation) }}" class="reservation-resource-action">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="return_{{ $part }}">
                            @unless($isKey)
                                <label class="form-label" for="returned-quantity">¿Cuántos regresaron?</label>
                                <div class="reservation-quantity-action">
                                    <input class="form-control" id="returned-quantity" type="number" name="quantity" min="1" max="{{ $quantity - $received }}" step="1" placeholder="Cantidad" required aria-describedby="pending-devices">
                                    <button class="btn-submit" aria-label="Registrar devolución de equipos"><i class="fa-solid fa-arrow-turn-down" aria-hidden="true"></i>Recibir equipos</button>
                                </div>
                                <small id="pending-devices" class="reservation-resource-caption">{{ $quantity - $received }} pendientes de devolución</small>
                            @else
                                <button class="btn-submit w-100" aria-label="Registrar devolución de llave del aula"><i class="fa-solid fa-arrow-turn-down" aria-hidden="true"></i>Recibir llave</button>
                            @endunless
                        </form>
                    @elseif($canIssue && ! $issued && ! $released)
                        <form method="POST" action="{{ route('reservations.operations.update', $reservation) }}" class="reservation-resource-action">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="issue_{{ $part }}">
                            <button class="btn-submit w-100" aria-label="Registrar entrega de {{ $isKey ? 'llave del aula' : 'equipos' }}"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>{{ $isKey ? 'Entregar llave' : 'Entregar '.$quantity.' equipos' }}</button>
                        </form>
                    @elseif($returned)
                        <p class="reservation-resource-done"><i class="fa-solid fa-check" aria-hidden="true"></i>Devuelto el {{ $returned->format('d/m/Y · H:i') }}</p>
                    @elseif($isActive && ! $released && $reservation->hasUnverifiedCustody())
                        <p class="reservation-resource-caption">Primero verifica la entrega anterior pendiente.</p>
                    @endif

                    @if($issued)
                        <div class="reservation-resource-footer"><i class="fa-regular fa-clock" aria-hidden="true"></i>Entregado el {{ $issued->format('d/m/Y · H:i') }}</div>
                    @endif
                </section>
            @endif
        @endforeach
    </div>

    @if($reservation->closure_reason || $reservation->rejection_reason)
        <details class="reservation-disclosure">
            <summary><span><i class="fa-regular fa-message" aria-hidden="true"></i>Motivo y observaciones</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
            <div class="reservation-disclosure-body"><p class="mb-0">{{ $reservation->closure_reason ?? $reservation->rejection_reason }}</p></div>
        </details>
    @endif

    @if($isActive && ! $reservation->hasUnverifiedCustody() && (($reservation->classroom_id && ! $reservation->key_issued_at && ! $reservation->key_released_at) || ($reservation->device_id && ! $reservation->devices_issued_at && ! $reservation->devices_released_at)))
        <details class="reservation-disclosure" @if($errors->any() && in_array(old('action'), ['release', 'no_show'])) open @endif>
            <summary><span><i class="fa-solid fa-ban" aria-hidden="true"></i>Cancelar recursos sin recoger</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
            <form method="POST" action="{{ route('reservations.operations.update', $reservation) }}" class="reservation-disclosure-body">
                @csrf @method('PATCH')
                <p class="small text-muted">Solo se liberan los recursos que no se entregaron.</p>
                <label class="form-label" for="release-reason">Motivo</label>
                <textarea name="reason" id="release-reason" class="form-control mb-3" maxlength="500" rows="2" required></textarea>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-danger" name="action" value="release">Confirmar cancelación</button>
                    @if(! $reservation->hasIssuedResources() && $reservation->start_at->lte(now()))
                        <button class="btn-cancel" name="action" value="no_show">Registrar que no se recogió</button>
                    @endif
                </div>
            </form>
        </details>
    @endif

    <details class="reservation-disclosure" @if(request()->has('page')) open @endif>
        <summary><span><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>Movimientos registrados <b class="reservation-count">{{ $events->total() }}</b></span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
        <div class="reservation-disclosure-body">
            <ol class="reservation-timeline">
                @forelse($events as $event)
                    <li>
                        <span class="reservation-timeline-dot" aria-hidden="true"></span>
                        <div class="reservation-timeline-heading"><strong>{{ $event->label() }} @if($event->quantity)<span class="reservation-count">{{ $event->quantity }}</span>@endif</strong><time>{{ $event->occurred_at->format('d/m/Y · H:i') }}</time></div>
                        <small>{{ $event->actor?->name ?? 'Sistema / cuenta eliminada' }}</small>
                        @if($event->reason)<p>{{ $event->reason }}</p>@endif
                        @if(! $event->occurred_at->equalTo($event->created_at))<small class="d-block">Registrado el {{ $event->created_at->format('d/m/Y · H:i') }}</small>@endif
                    </li>
                @empty
                    <li class="text-muted">Todavía no hay movimientos registrados.</li>
                @endforelse
            </ol>
            {{ $events->links() }}
        </div>
    </details>
</div>
<script>
document.querySelectorAll('[data-custody-select]').forEach(select => {
    const input = select.form.querySelector('[name="occurred_at"]');
    const container = select.form.querySelector('[data-custody-date]');
    const update = () => {
        const issued = select.value === 'issued';
        input.required = issued;
        input.disabled = !issued;
        container.hidden = !issued;
    };
    select.addEventListener('change', update);
    update();
});
</script>
@endsection
