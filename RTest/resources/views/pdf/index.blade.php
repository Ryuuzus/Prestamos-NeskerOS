<<<<<<< HEAD
Plantilla de exportación a PDF para las reservas. Esta vista se utiliza para generar un documento PDF que contiene la información de todas las reservas, incluyendo detalles como el aula, el dispositivo, las fechas y el estado de cada reserva. La plantilla está diseñada para ser clara y legible, facilitando la revisión y el registro de las reservas realizadas en el sistema.

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Reservas</title>
  <style>
    /* Estilos generales */
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #333333;
      margin: 0;
      padding: 20px;
      font-size: 12px;
      line-height: 1.4;
    }

    /* Encabezado del documento */
    .header {
      border-bottom: 2px solid #2c3e50;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }
    .header table {
      width: 100%;
      border-collapse: collapse;
    }
    .header .title {
      font-size: 20px;
      font-weight: bold;
      color: #2c3e50;
      text-transform: uppercase;
    }
    .header .meta {
      text-align: right;
      color: #7f8c8d;
      font-size: 10px;
    }

    /* Tabla de reservas */
    .table-reservas {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .table-reservas th {
      background-color: #2c3e50;
      color: #ffffff;
      text-align: left;
      padding: 8px 10px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .table-reservas td {
      padding: 8px 10px;
      border-bottom: 1px solid #ecf0f1;
      vertical-align: middle;
    }
    .table-reservas tr:nth-child(even) {
      background-color: #f8f9fa;
    }

    /* Badges para los estados */
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: bold;
      text-align: center;
      text-transform: capitalize;
    }
    .badge-confirmada {
      background-color: #e8f8f5;
      color: #27ae60;
      border: 1px solid #a3e4d7;
    }
    .badge-pendiente {
      background-color: #fef9e7;
      color: #f39c12;
      border: 1px solid #f9e79f;
    }
    .badge-cancelada {
      background-color: #fadbd8;
      color: #e74c3c;
      border: 1px solid #f5b7b1;
    }

    /* Pie de página y resumen */
    .summary {
      margin-top: 20px;
      float: right;
      width: 250px;
    }
    .summary table {
      width: 100%;
      border-collapse: collapse;
    }
    .summary td {
      padding: 4px 0;
    }
    .summary td.label {
      font-weight: bold;
      color: #7f8c8d;
    }
    .summary td.value {
      text-align: right;
      font-weight: bold;
    }

    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 9px;
      color: #95a5a6;
      border-top: 1px solid #ecf0f1;
      padding-top: 5px;
    }

    /* Configuración para la generación de PDF / Impresión */
    @page {
      size: A4 portrait;
      margin: 15mm 10mm 15mm 10mm;
    }
    @media print {
      body {
        padding: 0;
      }
      .table-reservas tr {
        page-break-inside: avoid; /* Evita que una fila se rompa entre páginas */
      }
      .header {
        page-break-after: avoid;
      }
    }
  </style>
</head>
<body>

  <!-- Encabezado -->
  <div class="header">
    <table>
      <tr>
        <td>
          <div class="title">Reporte de Reservas</div>
          <div style="font-size: 11px; color: #7f8c8d; margin-top: 3px;">
            Sistema de Gestión de Aulas y Dispositivos
          </div>
        </td>
        <td class="meta">
          <strong>Fecha de emisión:</strong> 05/10/2026<br>
          <strong>Generado por:</strong> Sistema Admin<br>
          <strong>Total registros:</strong> 3
        </td>
      </tr>
    </table>
  </div>

  <!-- Tabla Principal -->
  <table class="table-reservas">
    <thead>
      <tr>
        <th style="width: 8%;">ID</th>
        <th style="width: 20%;">Aula</th>
        <th style="width: 22%;">Dispositivo</th>
        <th style="width: 25%;">Fecha y Hora</th>
        <th style="width: 13%;">Estado</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>#001</td>
        <td><strong>Aula 101</strong><br><small style="color:#7f8c8d;">Edificio A - Piso 1</small></td>
        <td>Proyector Epson EB-X05</td>
        <td>12/10/2026<br><small style="color:#7f8c8d;">08:00 - 10:00</small></td>
        <td><span class="badge badge-confirmada">Confirmada</span></td>
      </tr>
      <tr>
        <td>#002</td>
        <td><strong>Laboratorio B</strong><br><small style="color:#7f8c8d;">Edificio C - Piso 2</small></td>
        <td>Kit de Portátiles (x15)</td>
        <td>12/10/2026<br><small style="color:#7f8c8d;">10:30 - 12:30</small></td>
        <td><span class="badge badge-pendiente">Pendiente</span></td>
      </tr>
      <tr>
        <td>#003</td>
        <td><strong>Aula Magna</strong><br><small style="color:#7f8c8d;">Edificio Central</small></td>
        <td>Sistema de Audio e Inalámbrico</td>
        <td>14/10/2026<br><small style="color:#7f8c8d;">15:00 - 18:00</small></td>
        <td><span class="badge badge-cancelada">Cancelada</span></td>
      </tr>
    </tbody>
  </table>

  <!-- Resumen al final de la tabla -->
  <div class="summary">
    <table>
      <tr>
        <td class="label">Confirmadas:</td>
        <td class="value" style="color: #27ae60;">1</td>
      </tr>
      <tr>
        <td class="label">Pendientes:</td>
        <td class="value" style="color: #f39c12;">1</td>
      </tr>
      <tr>
        <td class="label">Canceladas:</td>
        <td class="value" style="color: #e74c3c;">1</td>
      </tr>
    </table>
  </div>

  <!-- Pie de página -->
  <div class="footer">
    Documento generado automáticamente por el Sistema de Reservas &bull; Página 1 de 1
  </div>
=======
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Reservación</title>

    {{-- Si usas Tailwind CSS vía Vite o CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* Reglas CSS específicas para la impresión / generación de PDF */
        @page {
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Evita que una tarjeta de reservación se corte entre dos páginas */
        .page-break-inside-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        /* Fuerza un salto de página después de cada elemento si es un consolidado */
        .page-break-after {
            break-after: page;
            page-break-after: always;
        }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased p-4">

    @php
        // Normalizamos los datos: si viene un solo elemento ($reservation), lo convertimos a colección
        $list = isset($reservations) ? $reservations : collect([$reservation]);
    @endphp

    @foreach($list as $index => $item)
        <div class="page-break-inside-avoid {{ !$loop->last ? 'mb-8' : '' }}">
            
            <!-- Encabezado del Documento -->
            <header class="flex justify-between items-center border-b-2 border-indigo-600 pb-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-indigo-900 uppercase tracking-wide">
                        Solicitud de Reservación
                    </h1>
                    <p class="text-sm text-gray-500">Folio #{{ $item->id ?? $item->getKey() }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 uppercase">
                        {{ $item->status ?? 'Confirmada' }}
                    </span>
                    <p class="text-xs text-gray-400 mt-1">
                        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                    </p>
                </div>
            </header>

            <!-- Información General en Cuadrícula -->
            <section class="grid grid-cols-2 gap-4 mb-6 text-sm">
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <h3 class="text-xs font-bold uppercase text-gray-400 mb-1">Usuario Solicitante</h3>
                    <p class="font-semibold text-gray-700">{{ $item->user->name ?? 'N/A' }}</p>
                    <p class="text-gray-500 text-xs">{{ $item->user->email ?? '' }}</p>
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <h3 class="text-xs font-bold uppercase text-gray-400 mb-1">Ubicación / Aula</h3>
                    <p class="font-semibold text-gray-700">
                        {{ $item->classroom->name ?? 'Aula N/A' }}
                    </p>
                    <p class="text-gray-500 text-xs">
                        Edificio: {{ $item->classroom->building->name ?? 'N/A' }}
                    </p>
                </div>
            </section>

            <!-- Detalles del Recurso / Dispositivo -->
            <section class="mb-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase mb-2">Detalles del Equipo</h2>
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="p-2 border-b">Dispositivo</th>
                            <th class="p-2 border-b">Modelo / Serie</th>
                            <th class="p-2 border-b text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="p-2 font-medium">{{ $item->device->name ?? 'Dispositivo no especificado' }}</td>
                            <td class="p-2 text-gray-500">{{ $item->device->serial_number ?? 'S/N' }}</td>
                            <td class="p-2 text-right text-gray-600">{{ $item->device->status ?? 'Activo' }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Fechas de Uso -->
            <section class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg mb-8 text-sm">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="block text-xs font-semibold uppercase text-indigo-700">Inicio de Reserva</span>
                        <span class="font-bold text-indigo-900">
                            {{ isset($item->start_date) ? \Carbon\Carbon::parse($item->start_date)->format('d/m/Y h:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold uppercase text-indigo-700">Término de Reserva</span>
                        <span class="font-bold text-indigo-900">
                            {{ isset($item->end_date) ? \Carbon\Carbon::parse($item->end_date)->format('d/m/Y h:i A') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Firmas o Pie de Solicitud -->
            <footer class="mt-12 pt-4 text-xs text-gray-400 border-t border-gray-200 text-center">
                <p>Este documento es un comprobante oficial de la solicitud de reservación en el sistema.</p>
            </footer>

        </div>

        {{-- Si es un consolidado con múltiples elementos, forzamos salto de página salvo para el último --}}
        @if(isset($reservations) && !$loop->last)
            <div class="page-break-after"></div>
        @endif
    @endforeach
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2

</body>
</html>