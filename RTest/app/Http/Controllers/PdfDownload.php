<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

class PdfDownload extends Controller
{
    public function __invoke(Reservation $reservation): PdfBuilder
    {
        // Fixed relation string: 'user' instead of ' user'
        $reservation->load(['user', 'classroom.building', 'device']);

        return Pdf::view('PDF.pdfPlantilla', ['reservation' => $reservation])
            ->format('a4')
            ->download("solicitud-{$reservation->getKey()}.pdf");
    }

    public function exportarTodas(): PdfBuilder
    {
        // Renamed $reservation to $reservations (Collection)
        $reservations = Reservation::with(['user', 'classroom.building', 'device'])
            ->latest()
            ->get();

        return Pdf::view('pdf.index', ['reservations' => $reservations])
            ->format('a4')
            ->download('solicitudes-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}