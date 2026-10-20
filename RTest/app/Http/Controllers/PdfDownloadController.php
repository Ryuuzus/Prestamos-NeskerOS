<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Lista y genera comprobantes individuales de préstamos.
 * Nunca se confía en el ID de la URL sin comprobar el permiso del usuario.
 */
class PdfDownloadController extends Controller
{
    /** Recibos disponibles de las reservaciones propias (aprobadas o completadas). */
    public function receipts(): View
    {
        Gate::authorize('manage-own-reservations');

        $reservations = Reservation::query()
            ->with(['classroom.building', 'device'])
            ->where('user_id', auth()->id())
            ->whereIn('status', [Reservation::STATUS_APPROVED, Reservation::STATUS_COMPLETED])
            ->orderByDesc('start_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('receipt.index', compact('reservations'));
    }

    /** Vista HTML del comprobante para el titular, moderador o administrador. */
    public function index(Reservation $reservation): View
    {
        $this->ensureCanView($reservation);
        $reservation->load(['user', 'classroom.building', 'device']);

        return view('pdf.receipt', compact('reservation'));
    }

    /** Descarga directa de comprobante individual. */
    public function __invoke(Reservation $reservation): PdfBuilder
    {
        $this->ensureCanView($reservation);
        $reservation->load(['user', 'classroom.building', 'device']);

        return Pdf::view('pdf.receipt', compact('reservation'))
            ->format('a4')
            ->download("recibo-prestamo-{$reservation->getKey()}.pdf");
    }

    /** Conserva el nombre utilizado en rutas existentes. */
    public function exportar(Reservation $reservation): PdfBuilder
    {
        return $this->__invoke($reservation);
    }

    /** Exportación masiva heredada, exclusiva del administrador. */
    public function exportarTodas(): PdfBuilder
    {
        Gate::authorize('export-pdf');

        $reservations = Reservation::with(['user', 'classroom.building', 'device'])
            ->latest()->get();

        return Pdf::view('pdf.index', compact('reservations'))
            ->format('a4')
            ->download('solicitudes-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    private function ensureCanView(Reservation $reservation): void
    {
        Gate::authorize('view-receipt', $reservation);
    }
}
