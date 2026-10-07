<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Generación y Descarga de PDF (PdfDownload).
 * 
 * Gestiona la renderización en PDF de las solicitudes de reservación individuales
 * y la exportación completa en lote de todas las solicitudes mediante Spatie Laravel PDF.
 * ==================================================================================================
 */
class PdfDownload extends Controller
{
    /**
     * Genera y descarga el PDF individual para una reservación específica.
     */
    public function __invoke(Reservation $reservation): PdfBuilder
    {
        // Carga de relaciones necesarias para la plantilla del PDF
        $reservation->load(['user', 'classroom.building', 'device']);

        return Pdf::view('PDF.pdfPlantilla', ['reservation' => $reservation])
            ->format('a4')
            ->download("solicitud-{$reservation->getKey()}.pdf");
    }

    /**
     * Genera y descarga un PDF consolidado con todas las reservaciones registradas.
     */
    public function exportarTodas(): PdfBuilder
    {
        // Obtención del listado completo con sus relaciones asociadas
        $reservations = Reservation::with(['user', 'classroom.building', 'device'])
            ->latest()
            ->get();

        return Pdf::view('pdf.index', ['reservations' => $reservations])
            ->format('a4')
            ->download('solicitudes-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}