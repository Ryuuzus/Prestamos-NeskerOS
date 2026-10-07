{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista general de importación y exportación de archivos Excel/CSV (Excel Index View).
    Proporciona formularios individuales de carga masiva de datos (import) y enlaces de descarga
    directa (export) para los catálogos del sistema: Dispositivos, Edificios y Aulas/Salones.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista --}}
@section('content')
<div class="excel-main-container">

    {{-- Notificaciones globales de la sesión (Éxito / Error / Validaciones) --}}
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

    {{-- =================================================================-----------------------------
        SECCIÓN 1: IMPORTACIÓN Y EXPORTACIÓN DE DISPOSITIVOS
    --------------------------------------------------------------------------------------------------- --}}
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Dispositivos</h2>

        {{-- Formulario para la subida de archivos de Dispositivos --}}
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

        {{-- Enlace para exportar la lista de Dispositivos --}}
        <div class="excel-export-wrapper">
            <a href="{{ route('devices.export') }}" class="excel-export-link">Exportar Dispositivos</a>
        </div>
    </div>

    {{-- =================================================================-----------------------------
        SECCIÓN 2: IMPORTACIÓN Y EXPORTACIÓN DE EDIFICIOS
    --------------------------------------------------------------------------------------------------- --}}
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Edificios</h2>

        {{-- Formulario para la subida de archivos de Edificios --}}
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

        {{-- Enlace para exportar la lista de Edificios --}}
        <div class="excel-export-wrapper">
            <a href="{{ route('buildings.export') }}" class="excel-export-link">Exportar Edificios</a>
        </div>
    </div>

    {{-- =================================================================-----------------------------
        SECCIÓN 3: IMPORTACIÓN Y EXPORTACIÓN DE AULAS / SALONES
    --------------------------------------------------------------------------------------------------- --}}
    <div class="excel-container">
        <h2 class="excel-title">Importar y Exportar Aulas</h2>

        {{-- Formulario para la subida de archivos de Aulas --}}
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

        {{-- Enlace para exportar la lista de Aulas --}}
        <div class="excel-export-wrapper">
            <a href="{{ route('classrooms.export') }}" class="excel-export-link">Exportar Aulas</a>
        </div>
    </div>

</div>
@endsection