@extends('layouts.app')

@section('content')
@php
    $catalogs = [
        ['key' => 'devices', 'label' => 'Dispositivos', 'icon' => 'laptop'],
        ['key' => 'buildings', 'label' => 'Edificios', 'icon' => 'building'],
        ['key' => 'classrooms', 'label' => 'Aulas', 'icon' => 'door-open'],
    ];
@endphp
<div class="excel-page">
    <div class="app-section-heading">
        <div>
            <h1 class="app-page-title">Importar y exportar</h1>
            <p class="app-page-subtitle">Excel o CSV · Máximo 2 MB · Los duplicados se omiten</p>
        </div>
        <x-guide-button :href="route('reports.excel.guide')" />
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    <div class="excel-main-container">
        @foreach($catalogs as $catalog)
            <section class="excel-container" aria-labelledby="title-{{ $catalog['key'] }}">
                <div class="excel-card-heading">
                    <span class="excel-card-icon"><i class="fa-solid fa-{{ $catalog['icon'] }}" aria-hidden="true"></i></span>
                    <h2 class="excel-title" id="title-{{ $catalog['key'] }}">{{ $catalog['label'] }}</h2>
                </div>

                @foreach(['import', 'export', 'template'] as $operation)
                    @if($errors->getBag($operation.$catalog['label'])->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->getBag($operation.$catalog['label'])->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach

                @can('import-data')
                    <a href="{{ route('catalogs.template', $catalog['key']) }}" class="excel-template-link"
                       aria-label="Descargar plantilla vacía de {{ mb_strtolower($catalog['label']) }}">
                        <i class="fa-regular fa-file-excel" aria-hidden="true"></i>Plantilla vacía
                        <i class="fa-solid fa-download" aria-hidden="true"></i>
                    </a>
                    <form action="{{ route($catalog['key'].'.import') }}" method="POST" enctype="multipart/form-data" class="excel-form">
                        @csrf
                        <label for="file-{{ $catalog['key'] }}">
                            <span class="excel-label-text">Archivo de {{ mb_strtolower($catalog['label']) }}</span>
                            <input type="file" id="file-{{ $catalog['key'] }}" name="file" required accept=".xlsx,.xls,.csv"
                                   data-max-bytes="2097152" aria-describedby="feedback-{{ $catalog['key'] }}" class="excel-input-file">
                        </label>
                        <p id="feedback-{{ $catalog['key'] }}" data-file-feedback class="text-danger small mb-0" aria-live="polite"></p>
                        <button type="submit" class="excel-btn-submit" aria-label="Importar {{ mb_strtolower($catalog['label']) }}">
                            <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i>Importar
                        </button>
                    </form>
                @endcan

                <a href="{{ route($catalog['key'].'.export') }}" class="excel-export-link"
                   aria-label="Exportar {{ mb_strtolower($catalog['label']) }}">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>Exportar registros
                </a>
            </section>
        @endforeach
    </div>
</div>
@endsection
