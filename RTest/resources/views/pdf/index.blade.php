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

</body>
</html>