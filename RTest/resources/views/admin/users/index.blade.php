@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3">Usuarios y roles</h1>
    <p class="text-muted">Solo los administradores pueden cambiar roles. Los nuevos registros comienzan como Usuario.</p>
    <div class="table-responsive mb-4">
        <table class="table table-sm table-bordered">
            <thead><tr><th>Rol</th><th>Permisos principales</th></tr></thead>
            <tbody>
                <tr><td>Usuario</td><td>Reservaciones propias.</td></tr>
                <tr><td>Moderador</td><td>Reservaciones generales (aprobar, rechazar y completar), consulta de catálogos, historial y exportación Excel/PDF.</td></tr>
                <tr><td>Administrador</td><td>Todos los permisos, modificación de catálogos, importación Excel y asignación de roles.</td></tr>
            </tbody>
        </table>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-sm-8"><input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Buscar por nombre o correo"></div>
        <div class="col-sm-4"><button type="submit" class="btn btn-primary">Buscar</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol actual</th><th>Asignar rol</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }} @if($user->is(auth()->user()))<span class="badge bg-secondary">Tú</span>@endif</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="d-flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="form-select form-select-sm" aria-label="Rol de {{ $user->name }}">
                                <option value="user" @selected($user->role === 'user')>Usuario</option>
                                <option value="moderator" @selected($user->role === 'moderator')>Moderador</option>
                                <option value="admin" @selected($user->role === 'admin')>Administrador</option>
                            </select>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Guardar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No se encontraron usuarios.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
