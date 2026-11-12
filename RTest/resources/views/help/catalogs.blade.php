@extends('layouts.app')

@section('content')
<div class="app-content help-page">
    <div class="app-section-heading"><div><h1 class="app-page-title">Guía de catálogos</h1><p class="app-page-subtitle">Edificios, aulas y dispositivos.</p></div></div>
    <nav class="help-section-links" aria-label="Temas de catálogos"><a href="#buildings">Edificios</a><a href="#classrooms">Aulas</a><a href="#devices">Dispositivos</a><a href="#maintenance">Mantenimiento</a></nav>
    <section class="help-panel" id="buildings">
        <h2>Edificios</h2>
        <p>El nombre admite hasta 100 caracteres: letras, números, espacios, puntos y guiones. Debe ser único. Puedes registrar entre 1 y 100 pisos; al editar, no se pueden quitar pisos que ya tengan aulas.</p>
        <a href="{{ route('buildings.index') }}">Ir a edificios</a>
    </section>
    <section class="help-panel" id="classrooms">
        <h2>Aulas</h2>
        <p>Selecciona el edificio y uno de sus pisos. El nombre admite hasta 100 caracteres: letras, números, espacios, puntos y guiones. Puede repetirse en otro piso o edificio, pero no en el mismo piso del mismo edificio.</p>
        <p>Devolver un aula significa regresar su llave. El personal registra la entrega y devolución desde Gestionar préstamo en Reservaciones.</p>
        <a href="{{ route('classrooms.index') }}">Ir a aulas</a>
    </section>
    <section class="help-panel" id="devices">
        <h2>Dispositivos</h2>
        <p>El nombre admite hasta 100 caracteres y el número de serie hasta 50. Cada número de serie es único. La cantidad total admite de 1 a 9999 unidades.</p>
        <p>Las unidades disponibles se calculan con las entregas y devoluciones. No reduzcas el inventario por debajo de los compromisos existentes; las devoluciones parciales se registran desde Gestionar préstamo.</p>
        <a href="{{ route('devices.index') }}">Ir a dispositivos</a>
    </section>
    <section class="help-panel" id="maintenance">
        <h2>Disponibilidad y mantenimiento</h2>
        <p>Disponible permite solicitar el recurso según su horario. Mantenimiento impide nuevas solicitudes. La ocupación se calcula con los préstamos, sin cambiar manualmente el estado a ocupado.</p>
        <p>El mantenimiento de un edificio se aplica a todas sus aulas. Al volver a disponible, solo se reabren las aulas suspendidas por el edificio; las averías individuales se conservan.</p>
        @can('manage-catalogs')
            <p>Si un recurso tiene reservaciones, usa Reportar avería y escribe el motivo. Las reservaciones sin recoger se suspenden y las devoluciones pendientes se conservan. Revisa el aviso antes de confirmar.</p>
            <p>Los recursos con historial de préstamos no se eliminan. Usa mantenimiento para retirarlos de disponibilidad.</p>
        @endcan
    </section>
    @can('import-data')
        <section class="help-panel"><h2>Cargas de varios registros</h2><p>Usa Importar y exportar para descargar plantillas vacías, importar filas nuevas o exportar los catálogos.</p><x-guide-button :href="route('reports.excel.guide')" label="Guía de importación y exportación" /></section>
    @endcan
</div>
@endsection
