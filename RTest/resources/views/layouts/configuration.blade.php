@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-2">Ajustes de cuenta</h1>
    <p class="text-muted mb-4">Consulta la información de tu cuenta y el nivel de acceso asignado.</p>

    @php
        $roleLabels = [
            \App\Models\User::ROLE_USER => 'Usuario',
            \App\Models\User::ROLE_MODERATOR => 'Moderador',
            \App\Models\User::ROLE_ADMIN => 'Administrador',
        ];
    @endphp

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nombre</dt>
                <dd class="col-sm-9">{{ auth()->user()->name }}</dd>

                <dt class="col-sm-3">Correo electrónico</dt>
                <dd class="col-sm-9">{{ auth()->user()->email }}</dd>

                <dt class="col-sm-3">Rol</dt>
                <dd class="col-sm-9">{{ $roleLabels[auth()->user()->role] ?? 'Usuario' }}</dd>

                <dt class="col-sm-3">Correo verificado</dt>
                <dd class="col-sm-9">{{ auth()->user()->hasVerifiedEmail() ? 'Sí' : 'No' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
