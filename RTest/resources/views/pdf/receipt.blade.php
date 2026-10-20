<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de préstamo #{{ $reservation->id }}</title>
    <style>
        @page { margin: 24mm 18mm; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #172033; margin: 0; }
        .header { padding-bottom: 15px; border-bottom: 3px solid #22334b; margin-bottom: 24px; }
        .brand { color: #22334b; font-size: 26px; font-weight: bold; }
        h1 { font-size: 18px; margin: 8px 0; }
        h2 { font-size: 14px; margin: 22px 0 9px; color: #22334b; }
        .muted { color: #667085; }
        .pill { display: inline-block; padding: 6px 10px; border-radius: 4px; background: #e7f7ee; color: #136c37; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        td { border-bottom: 1px solid #e6eaf0; padding: 9px 7px; vertical-align: top; }
        td:first-child { width: 35%; color: #525f70; font-weight: bold; }
        .note { margin-top: 24px; padding: 12px; border: 1px solid #cfd8e3; background: #f7f9fc; line-height: 1.5; }
        .footer { margin-top: 30px; color: #667085; font-size: 11px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 18px;">
        <button onclick="window.print()" type="button">Imprimir</button>
    </div>
    <div class="header">
        <div class="brand">NeskerOS</div>
        <h1>Recibo de préstamo de recursos</h1>
        <div class="muted">Folio: #{{ $reservation->id }}</div>
    </div>

    <p><span class="pill">{{ $reservation->status === 'completed' ? 'COMPLETADO' : 'APROBADO' }}</span></p>

    <h2>Persona solicitante</h2>
    <table>
        <tr><td>Nombre</td><td>{{ $reservation->user?->name ?? 'No disponible' }}</td></tr>
        <tr><td>Correo</td><td>{{ $reservation->user?->email ?? 'No disponible' }}</td></tr>
    </table>

    <h2>Recursos solicitados</h2>
    <table>
        @if($reservation->classroom)
            <tr><td>Aula</td><td>{{ $reservation->classroom->classroom }}</td></tr>
            <tr><td>Edificio</td><td>{{ $reservation->classroom->building?->name ?? 'No disponible' }}</td></tr>
            <tr><td>Piso</td><td>{{ $reservation->classroom->floor ?? 'No disponible' }}</td></tr>
        @endif
        @if($reservation->device)
            <tr><td>Dispositivo</td><td>{{ $reservation->device->name }}</td></tr>
            <tr><td>Número de serie</td><td>{{ $reservation->device->serial_number }}</td></tr>
            <tr><td>Unidades solicitadas</td><td>{{ $reservation->device_quantity }}</td></tr>
        @endif
        @if(!$reservation->classroom && !$reservation->device)
            <tr><td>Recurso</td><td>El recurso ya no se encuentra registrado en el sistema.</td></tr>
        @endif
    </table>

    <h2>Periodo del préstamo</h2>
    <table>
        <tr><td>Fecha de inicio</td><td>{{ $reservation->start_at?->format('d/m/Y H:i') ?? 'No disponible' }}</td></tr>
        <tr><td>Devolución programada</td><td>{{ $reservation->end_at?->format('d/m/Y H:i') ?? 'No disponible' }}</td></tr>
        @if($reservation->status === 'completed')
            <tr><td>Entrega/devolución registrada</td><td>{{ $reservation->actual_return_time?->format('d/m/Y H:i') ?? 'No registrada' }}</td></tr>
        @endif
    </table>

    <div class="note">
        Este documento es un comprobante digital de una reservación aprobada o completada.
        No sustituye la comprobación física de entrega del recurso.
    </div>
    <div class="footer">Documento generado por NeskerOS el {{ now()->format('d/m/Y H:i') }}.</div>
</body>
</html>
