@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $building->name }}</h1>
            <p class="text-muted mb-0">Detalles del edificio y sus aulas.</p>
        </div>
        <a href="{{ route('buildings.index') }}" class="btn btn-outline-secondary">Volver a edificios</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Pisos</dt>
                <dd class="col-sm-9">{{ $building->floors }}</dd>

                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">
                    {{ $building->status === \App\Models\Building::STATUS_MAINTENANCE ? 'Mantenimiento' : 'Disponible' }}
                </dd>
            </dl>
        </div>
    </div>

    <h2 class="h5 mb-3">Aulas ({{ $building->classrooms->count() }})</h2>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Aula</th>
                    <th>Piso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($building->classrooms as $classroom)
                    <tr>
                        <td>{{ $classroom->classroom }}</td>
                        <td>{{ $classroom->floor }}</td>
                        <td>{{ $classroom->status === 'maintenance' ? 'Mantenimiento' : 'Disponible' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-muted">Este edificio todavía no tiene aulas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
