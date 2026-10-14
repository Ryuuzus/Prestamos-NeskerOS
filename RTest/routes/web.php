<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PdfDownload;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/google-auth/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/google-auth/callback', [GoogleAuthController::class, 'callback']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('reservations', ReservationController::class);
    Route::view('/ajustes', 'layouts.configuration')->name('configuration');
});

Route::middleware(['auth', 'admin', 'verified'])->group(function () {
    // Rutas adicionales del controlador de Dispositivos (Deben definirse ANTES de Route::resource)
    Route::get('/devices/excel', [DeviceController::class, 'home'])->name('devices.excel');
    Route::post('/devices/import', [DeviceController::class, 'import'])->name('devices.import');
    Route::get('/devices/export', [DeviceController::class, 'export'])->name('devices.export');

    // Rutas adicionales del controlador de Edificios (Deben definirse ANTES de Route::resource)
    Route::get('/buildings/excel', [BuildingController::class, 'home'])->name('buildings.excel');
    Route::post('/buildings/import', [BuildingController::class, 'import'])->name('buildings.import');
    Route::get('/buildings/export', [BuildingController::class, 'export'])->name('buildings.export');

    // Rutas adicionales del controlador de Salones (Deben definirse ANTES de Route::resource)
    Route::get('/classrooms/excel', [ClassroomController::class, 'home'])->name('classrooms.excel');
    Route::post('/classrooms/import', [ClassroomController::class, 'import'])->name('classrooms.import');
    Route::get('/classrooms/export', [ClassroomController::class, 'export'])->name('classrooms.export');

    // CRUDs de Catálogos
    Route::resource('buildings', BuildingController::class);
    Route::resource('classrooms', ClassroomController::class);
    Route::resource('devices', DeviceController::class);

    // Acciones administrativas de reservaciones
    Route::patch('/reservations/{reservation}/approve', [ReservationController::class, 'approve'])->name('reservations.approve');
    Route::patch('/reservations/{reservation}/reject', [ReservationController::class, 'reject'])->name('reservations.reject');
    Route::patch('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');

    // Rutas de PDF
    Route::get('/pdf/exportar-todas', [PdfDownload::class, 'exportarTodas'])->name('pdf.exportar-todas');
    Route::get('/pdf/exportar/{reservation}', [PdfDownload::class, 'exportar'])->name('pdf.exportar');
});