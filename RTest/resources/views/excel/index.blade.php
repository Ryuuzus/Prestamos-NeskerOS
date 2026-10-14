@extends('layouts.app')

@section('content')
<div class="excel-main-container">

    {{-- Mensajes Globales de Éxito / Error --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Secciones de Importación / Exportación -->
    
    <!-- Dispositivos -->
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Dispositivos</h2>

        <form action="{{ route('devices.import') }}" method="POST" enctype="multipart/form-data" class="excel-form">
            @csrf
            <label>
                <span class="excel-label-text">Subir Archivo (.xlsx, .csv)</span>
                <input type="file" name="file" required accept=".xlsx,.csv,.xls" class="excel-input-file">
            </label>

            <button type="submit" class="excel-btn-submit">
                Importar Dispositivos
            </button>
        </form>

        <div class="excel-export-wrapper">
            <a href="{{ route('devices.export') }}" class="excel-export-link">Exportar Dispositivos</a>
        </div>
    </div>

    <!-- Edificios -->
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Edificios</h2>

        <form action="{{ route('buildings.import') }}" method="POST" enctype="multipart/form-data" class="excel-form">
            @csrf
            <label>
                <span class="excel-label-text">Subir Archivo (.xlsx, .csv)</span>
                <input type="file" name="file" required accept=".xlsx,.csv,.xls" class="excel-input-file">
            </label>

            <button type="submit" class="excel-btn-submit">
                Importar Edificios
            </button>
        </form>

        <div class="excel-export-wrapper">
            <a href="{{ route('buildings.export') }}" class="excel-export-link">Exportar Edificios</a>
        </div>
    </div>

    <!-- Aulas -->
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Aulas</h2>

        <form action="{{ route('classrooms.import') }}" method="POST" enctype="multipart/form-data" class="excel-form">
            @csrf
            <label>
                <span class="excel-label-text">Subir Archivo (.xlsx, .csv)</span>
                <input type="file" name="file" required accept=".xlsx,.csv,.xls" class="excel-input-file">
            </label>

            <button type="submit" class="excel-btn-submit">
                Importar Aulas
            </button>
        </form>

        <div class="excel-export-wrapper">
            <a href="{{ route('classrooms.export') }}" class="excel-export-link">Exportar Aulas</a>
        </div>
    </div>

</div>
@endsection