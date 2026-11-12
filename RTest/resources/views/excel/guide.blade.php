@extends('layouts.app')

@section('content')
<div class="app-content help-page">
    <a href="{{ route('reports.excel') }}" class="reservation-text-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Ir a importar y exportar</a>
    <div class="app-section-heading mt-4">
        <div><h1 class="app-page-title">Guía de importación y exportación</h1><p class="app-page-subtitle">Prepara tu archivo y agrega solo los registros nuevos.</p></div>
    </div>
    <ol class="help-steps">
        <li><span>1</span><div><h2>Descarga la plantilla vacía</h2><p>Elige el catálogo. El archivo .xlsx incluye únicamente los encabezados, sin datos ni ejemplos.</p></div></li>
        <li><span>2</span><div><h2>Completa las filas</h2><p>Escribe un registro por fila a partir de la segunda. Conserva los nombres de las columnas y guarda un archivo .xlsx, .xls o .csv de hasta 2 MB.</p></div></li>
        <li><span>3</span><div><h2>Importa y revisa el resultado</h2><p>Selecciona el archivo y pulsa Importar. Verás cuántos registros se agregaron y cuántos duplicados se omitieron.</p></div></li>
    </ol>
    <nav class="help-section-links" aria-label="Columnas por catálogo">
        <a href="#devices">Dispositivos</a><a href="#buildings">Edificios</a><a href="#classrooms">Aulas</a>
    </nav>
    <section class="help-panel" id="devices">
        <h2>Dispositivos</h2>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Columna</th><th>Qué escribir</th></tr></thead>
            <tbody>
                <tr><th scope="row"><code>name</code></th><td>Nombre obligatorio, máximo 100 caracteres.</td></tr>
                <tr><th scope="row"><code>serial_number</code></th><td>Número de serie obligatorio, máximo 50 caracteres. Identifica los duplicados.</td></tr>
                <tr><th scope="row"><code>quantity</code></th><td>Opcional: un entero de 1 a 9999. Si se deja vacío, se usa 1.</td></tr>
                <tr><th scope="row"><code>status</code></th><td>Opcional: <code>available</code> o <code>maintenance</code>. Por defecto, <code>available</code>.</td></tr>
            </tbody>
        </table></div>
    </section>
    <section class="help-panel" id="buildings">
        <h2>Edificios</h2>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Columna</th><th>Qué escribir</th></tr></thead>
            <tbody>
                <tr><th scope="row"><code>name</code></th><td>Nombre obligatorio, máximo 100 caracteres. Letras, números, espacios, puntos y guiones. Identifica los duplicados.</td></tr>
                <tr><th scope="row"><code>floors</code></th><td>Cantidad obligatoria de pisos: un entero de 1 a 100.</td></tr>
                <tr><th scope="row"><code>status</code></th><td>Opcional: <code>available</code> o <code>maintenance</code>. Por defecto, <code>available</code>.</td></tr>
            </tbody>
        </table></div>
    </section>
    <section class="help-panel" id="classrooms">
        <h2>Aulas</h2>
        <p>Importa primero los edificios. Para identificar el edificio, completa <code>building_id</code> o <code>building_name</code>; si llenas ambos, se usa el ID.</p>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Columna</th><th>Qué escribir</th></tr></thead>
            <tbody>
                <tr><th scope="row"><code>classroom</code></th><td>Nombre obligatorio, máximo 100 caracteres. Letras, números, espacios, puntos y guiones.</td></tr>
                <tr><th scope="row"><code>building_id</code></th><td>ID de un edificio existente. Puedes consultarlo en el catálogo de edificios.</td></tr>
                <tr><th scope="row"><code>building_name</code></th><td>Alternativa al ID: el nombre de un edificio existente.</td></tr>
                <tr><th scope="row"><code>floor</code></th><td>Piso obligatorio: entero de 1 a 100, sin superar los pisos del edificio.</td></tr>
                <tr><th scope="row"><code>status</code></th><td>Opcional: <code>available</code> o <code>maintenance</code>. Por defecto toma el estado del edificio. Si el edificio está en mantenimiento, el aula también debe estarlo.</td></tr>
            </tbody>
        </table></div>
    </section>
    <section class="help-panel">
        <h2>Duplicados y errores</h2>
        <p>Se omite la fila si ya existe su número de serie (dispositivos), nombre (edificios), o la combinación de nombre, edificio y piso (aulas). También se omiten repeticiones dentro del mismo archivo: se conserva la primera fila válida.</p>
        <p>Los duplicados no actualizan ni sobrescriben los registros existentes. Si todas las filas ya existen, el resultado indica 0 importados y el total de duplicados omitidos.</p>
        <p>Si una fila nueva contiene datos inválidos, no se guarda ningún registro nuevo del archivo. El mensaje indica la fila y la columna que debes corregir. Un archivo vacío o con solo encabezados necesita filas de datos para importarse.</p>
    </section>
    <section class="help-panel">
        <h2>Exportar registros</h2>
        <p>Exportar descarga el catálogo actual en Excel, con sus datos. Plantilla vacía descarga únicamente las columnas que puedes completar. Las columnas de identificación y fechas que aparecen en una exportación no se usan para actualizar registros al importarla.</p>
    </section>
</div>
@endsection
