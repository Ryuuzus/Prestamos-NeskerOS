<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Generación y Descarga de PDF (PdfDownloadController).
 * ==================================================================================================
 */
class PdfDownloadController extends Controller
{
    /**
     * Genera y descarga el PDF individual para una reservación específica.
     */
    public function index(Reservation $reservation): ?View
    {
        $reservation->load(['user', 'classroom.building', 'device']);

        return view('pdf.index', compact('reservation'));

    }

    public function __invoke(Reservation $reservation): PdfBuilder
    {
        $reservation->load(['user', 'classroom.building', 'device']);

        return Pdf::view('pdf.index', ['reservation' => $reservation])
            ->format('a4')
            ->download("solicitud-{$reservation->getKey()}.pdf");
    }

    /**
     * Genera y descarga un PDF consolidado con todas las reservaciones registradas.
     */
    public function exportarTodas(): PdfBuilder
    {
        $reservations = Reservation::with(['user', 'classroom.building', 'device'])
            ->latest()
            ->get();

        return Pdf::view('pdf.index', ['reservations' => $reservations])
            ->format('a4')
            ->download('solicitudes-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}