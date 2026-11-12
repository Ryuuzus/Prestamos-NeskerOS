@extends('layouts.app')
@section('content')
<div class="app-content reservation-page">
    <div class="reservation-navigation">
        <a href="{{ route('agenda.index') }}" class="reservation-text-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Volver a la agenda</a>
        @can('manage-own-reservations')<a href="{{ route('reservations.create') }}" class="btn-create"><i class="fa-solid fa-plus" aria-hidden="true"></i>Crear una reservación</a>@endcan
    </div>
    <div class="reservation-page-heading">
        <div>
            <h1 id="reservation-process-title" class="app-page-title">¿Cómo funcionan las reservaciones?</h1>
            <p class="app-page-subtitle">De la solicitud a la devolución, en cuatro pasos.</p>
        </div>
    </div>
    <ol class="reservation-guide-steps">
        <li><i class="fa-regular fa-calendar-plus" aria-hidden="true"></i><h2>Solicita</h2><p>Consulta la agenda y elige un aula, equipos o ambos, con su horario.</p></li>
        <li><i class="fa-regular fa-circle-check" aria-hidden="true"></i><h2>Espera la aprobación</h2><p>El personal revisa la solicitud. Al aprobarla, tu recibo aparece en Mis recibos.</p></li>
        <li><i class="fa-solid fa-key" aria-hidden="true"></i><h2>Recoge</h2><p>Dentro del horario aprobado, el personal registra la entrega de la llave y los equipos.</p></li>
        <li><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i><h2>Devuelve</h2><p>Regresa la llave del aula y los equipos. Puedes devolver los equipos por partes.</p></li>
    </ol>
    <div class="reservation-rule-strip" aria-label="Reglas de horarios">
        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i><strong>{{ config('reservations.min_days_in_advance') }}</strong> días de anticipación</span>
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i><strong>{{ config('reservations.min_duration_hours') }}@if(config('reservations.max_duration_hours') !== null)–{{ config('reservations.max_duration_hours') }}@endif horas</strong> de duración</span>
        <span><i class="fa-regular fa-sun" aria-hidden="true"></i><strong>{{ sprintf('%02d:00', config('reservations.open_hour')) }}–{{ sprintf('%02d:00', config('reservations.close_hour')) }}</strong> inicio y devolución</span>
    </div>
    <h2 class="h6 fw-bold mb-3">Dudas frecuentes</h2>
    <details class="reservation-disclosure"><summary><span>¿Qué significa devolver un aula?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">Significa regresar su llave. El personal registra la llave y los equipos por separado. El préstamo se cierra cuando todo lo entregado se devuelve y se liberan los recursos que no se recogieron.</div></details>
    <details class="reservation-disclosure"><summary><span>¿Cuándo queda confirmado el préstamo?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">Una solicitud pendiente todavía no garantiza la disponibilidad. La aprobación aparta el horario; la entrega física se registra cuando recoges los recursos. Si hay una devolución atrasada, debe resolverse antes de la siguiente entrega.</div></details>
    <details class="reservation-disclosure"><summary><span>¿Puedo editar o cancelar?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">Puedes editar solicitudes pendientes y cancelar las pendientes o aprobadas mientras no hayas recogido recursos. Escribe el motivo; la cancelación se conserva en el historial.</div></details>
    <details class="reservation-disclosure"><summary><span>¿Qué pasa si no recojo los recursos?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">Una solicitud sin aprobar vence al llegar su inicio. Una aprobación sin recogida se cierra al terminar su horario; el personal puede registrar antes la falta de recogida con un motivo. Los recursos entregados siguen pendientes hasta su devolución.</div></details>
    <details class="reservation-disclosure"><summary><span>¿Cómo se cuentan los horarios y los días?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">El préstamo es continuo entre inicio y devolución, incluso durante la noche. No se repite cada día: para días separados, envía una solicitud por intervalo. Elige horas en intervalos de {{ config('reservations.slot_minutes') }} minutos. Las horas no disponibles se deshabilitan en el formulario.@if(config('reservations.max_days_ahead') !== null) Puedes solicitar hasta {{ config('reservations.max_days_ahead') }} días por adelantado.@endif</div></details>
    <details class="reservation-disclosure"><summary><span>¿Y si hay una avería?</span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary><div class="reservation-disclosure-body">El administrador puede suspender las reservas afectadas, conservando las devoluciones pendientes. Revisa el motivo en Mis reservaciones; si se cancela, solicita otro recurso u horario.</div></details>
</div>
@endsection
