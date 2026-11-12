@extends('layouts.app')

@section('content')
<div class="app-content help-page">
    <a href="{{ route('configuration') }}" class="reservation-text-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Ir a ajustes de cuenta</a>
    <div class="app-section-heading mt-4"><div><h1 class="app-page-title">Guía de cuenta y roles</h1><p class="app-page-subtitle">Consulta qué puedes hacer con tu cuenta.</p></div></div>
    <section class="help-panel"><h2>Tu cuenta</h2><p>En Ajustes de cuenta puedes consultar tu nombre, correo, rol y verificación del correo. Los nuevos registros comienzan con el rol Usuario. Solo un administrador puede cambiar los roles.</p></section>
    <section class="help-panel" id="roles">
        <h2>Permisos por rol</h2>
        <dl class="help-role-list">
            <div><dt>Usuario</dt><dd>Consulta la agenda, solicita reservaciones, edita las pendientes, cancela las propias antes de recoger recursos y consulta sus recibos.</dd></div>
            <div><dt>Moderador</dt><dd>Consulta la agenda y los catálogos. Revisa solicitudes, aprueba o rechaza, registra entregas y devoluciones, y consulta el historial y los recibos individuales.</dd></div>
            <div><dt>Administrador</dt><dd>Tiene los permisos de moderación y además administra catálogos, reporta averías, importa y exporta Excel, realiza la exportación masiva de recibos y asigna roles.</dd></div>
        </dl>
        <p>Los roles Moderador y Administrador atienden préstamos; las solicitudes propias corresponden al rol Usuario.</p>
    </section>
    @can('manage-users')
        <section class="help-panel"><h2>Cambiar un rol</h2><p>En Usuarios y roles, busca la cuenta, elige su nuevo rol y pulsa Guardar. No puedes quitarte tu propio rol de administrador ni dejar al sistema sin administradores.</p><a href="{{ route('admin.users.index') }}">Ir a usuarios y roles</a></section>
    @endcan
</div>
@endsection
