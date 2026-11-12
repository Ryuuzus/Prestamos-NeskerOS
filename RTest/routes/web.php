<?php

use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CatalogTemplateController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PdfDownloadController;
use App\Http\Controllers\ReservationAgendaController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationOperationController;
use App\Http\Middleware\ValidateSearch;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('agenda.index'));
Route::get('/google-auth/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/google-auth/callback', [GoogleAuthController::class, 'callback']);

Route::middleware(['auth', 'verified', ValidateSearch::class])->group(function () {
    // Agenda de préstamos visible para cualquier cuenta autenticada y verificada.
    // Sin gate de rol: user, moderator y admin pueden consultarla.
    Route::get('/agenda', [ReservationAgendaController::class, 'index'])->name('agenda.index');
    Route::view('/reservations/guide', 'reservation.process-guide')->name('reservations.guide');

    // SOLO el rol 'user' puede solicitar, editar y cancelar reservaciones propias.
    // Se protege tanto el formulario GET como el POST y el endpoint de disponibilidad.
    Route::middleware('can:manage-own-reservations')->group(function () {
        Route::get('/reservations/availability', [ReservationController::class, 'availability'])
            ->name('reservations.availability');
        Route::resource('reservations', ReservationController::class)->except('show');
    });
    Route::view('/ajustes', 'layouts.configuration')->name('configuration');
    Route::view('/help/account', 'help.account')->name('help.account');
    Route::view('/help/catalogs', 'help.catalogs')->middleware('can:view-catalogs')->name('help.catalogs');

    // Supervisión operativa: moderador y admin.
    Route::middleware('can:review-reservations')->group(function () {
        Route::get('/admin/reservations', [AdminReservationController::class, 'index'])
            ->name('admin.reservations.index');
        Route::patch('/reservations/{reservation}/approve', [AdminReservationController::class, 'approve'])
            ->name('reservations.approve');
        Route::patch('/reservations/{reservation}/reject', [AdminReservationController::class, 'reject'])
            ->name('reservations.reject');
        Route::get('/reservations/{reservation}/operations', [ReservationOperationController::class, 'show'])->name('reservations.operations');
        Route::patch('/reservations/{reservation}/operations', [ReservationOperationController::class, 'update'])->name('reservations.operations.update');
        Route::patch('/reservations/{reservation}/complete', [AdminReservationController::class, 'complete'])
            ->name('reservations.complete');
    });

    Route::get('/reservations/history', [AdminReservationController::class, 'history'])
        ->middleware('can:view-history')->name('reservations.history');

    // Exportación y guía de Excel, reservadas al administrador.
    Route::middleware('can:export-data')->group(function () {
        Route::view('/reports/excel', 'excel.index')->name('reports.excel');
        Route::view('/reports/excel/guide', 'excel.guide')->name('reports.excel.guide');
        Route::get('/buildings/export', [BuildingController::class, 'export'])->name('buildings.export');
        Route::get('/classrooms/export', [ClassroomController::class, 'export'])->name('classrooms.export');
        Route::get('/devices/export', [DeviceController::class, 'export'])->name('devices.export');
    });

    // Centro de Excel original, con importación, reservado al admin.
    Route::middleware('can:import-data')->group(function () {
        Route::get('/reports/excel/template/{catalog}', CatalogTemplateController::class)
            ->whereIn('catalog', ['devices', 'buildings', 'classrooms'])->name('catalogs.template');
        Route::get('/devices/excel', [DeviceController::class, 'home'])->name('devices.excel');
        // Se conservan las URL de los otros dos centros; comparten la vista excel.index.
        Route::get('/buildings/excel', [DeviceController::class, 'home'])->name('buildings.excel');
        Route::get('/classrooms/excel', [DeviceController::class, 'home'])->name('classrooms.excel');
        Route::post('/buildings/import', [BuildingController::class, 'import'])->name('buildings.import');
        Route::post('/classrooms/import', [ClassroomController::class, 'import'])->name('classrooms.import');
        Route::post('/devices/import', [DeviceController::class, 'import'])->name('devices.import');
    });

    // IMPORTANTE: registrar /buildings/create ANTES de /buildings/{building}.
    // Lo mismo para classrooms y devices; evita que `create` se interprete como ID.
    // Alta, edición, borrado y mantenimiento (solo admin).
    Route::middleware('can:manage-catalogs')->group(function () {
        Route::post('/maintenance/{type}/{id}', [MaintenanceController::class, 'store'])
            ->whereIn('type', ['building', 'classroom', 'device'])->whereNumber('id')->name('maintenance.store');
        Route::resource('buildings', BuildingController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('classrooms', ClassroomController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('devices', DeviceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    // Lectura de catálogos después de rutas específicas y /create.
    // Así /devices/export NO se interpreta como /devices/{device}.
    // Lectura de catálogos (moderador y admin).
    Route::middleware('can:view-catalogs')->group(function () {
        Route::resource('buildings', BuildingController::class)->only(['index', 'show']);
        Route::resource('classrooms', ClassroomController::class)->only(['index', 'show']);
        Route::resource('devices', DeviceController::class)->only(['index', 'show']);
    });

    // La lista 'Mis recibos' pertenece únicamente al rol user.
    // El personal puede VER recibos individuales desde la gestión, no solicitar reservas.
    Route::get('/recibos', [PdfDownloadController::class, 'receipts'])
        ->middleware('can:manage-own-reservations')->name('receipts.index');

    // Recibo por préstamo aprobado o completado, disponible para su dueño.
    // Moderadores y admins pueden consultar recibos para atender las solicitudes.
    Route::get('/pdf/exportar/{reservation}', [PdfDownloadController::class, 'exportar'])
        ->middleware('can:view-receipt,reservation')->name('pdf.exportar');
    Route::get('/pdf/ver/{reservation}', [PdfDownloadController::class, 'index'])
        ->middleware('can:view-receipt,reservation')->name('pdf.ver');

    // Exportación masiva heredada: solo administrador y fuera del menú de recibos.
    Route::get('/pdf/exportar-todas', [PdfDownloadController::class, 'exportarTodas'])
        ->middleware('can:export-pdf')->name('pdf.exportar-todas');

    // Solo administrador: asignar roles.
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/admin/users', [UserRoleController::class, 'index'])->name('admin.users.index');
        Route::patch('/admin/users/{user}/role', [UserRoleController::class, 'update'])
            ->name('admin.users.role.update');
    });
});
