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

</body>
</html>