<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReservationException;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Reservaciones para el ADMINISTRADOR (Admin\ReservationController).
 *
 * - index():    panel con SOLO las reservaciones que requieren atención (pendientes y aprobadas).
 * - history():  historial de reservaciones finalizadas (completadas y rechazadas).
 * - approve(), reject(), complete(): ciclo de vida de una reservación.
 *
 * El acceso está protegido por el middleware 'admin' en routes/web.php, por lo que aquí no se
 * repite la verificación de rol. Las reglas de negocio viven en ReservationService.
 * ==================================================================================================
 */
class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    /**
     * Panel de reservaciones activas: pendientes y aprobadas.
     */
    public function index(Request $request): View
    {
        $reservations = $this->baseQuery($request, [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_APPROVED,
            ])
            // Primero las pendientes; dentro de cada grupo, las que empiezan antes.
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderByRaw('TIMESTAMP(start_date, start_time) ASC')
            ->paginate(10)
            ->withQueryString();

        $counts = Reservation::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('reservation.admin.index', [
            'reservations'  => $reservations,
            'pendingCount'  => (int) $counts->get(Reservation::STATUS_PENDING, 0),
            'approvedCount' => (int) $counts->get(Reservation::STATUS_APPROVED, 0),
            'historyCount'  => (int) $counts->get(Reservation::STATUS_COMPLETED, 0)
                             + (int) $counts->get(Reservation::STATUS_REJECTED, 0),
        ]);
    }

    /**
     * Historial de reservaciones (completadas y rechazadas).
     * Se puede filtrar por estado con ?status=completed o ?status=rejected.
     */
    public function history(Request $request): View
    {
        $statuses = [Reservation::STATUS_COMPLETED, Reservation::STATUS_REJECTED];
        $filter   = $request->input('status');

        if (in_array($filter, $statuses, true)) {
            $statuses = [$filter];
        } else {
            $filter = null;
        }

        $reservations = $this->baseQuery($request, $statuses)
            ->orderByRaw('COALESCE(actual_return_time, updated_at) DESC')
            ->paginate(15)
            ->withQueryString();

        return view('reservation.admin.history', compact('reservations', 'filter'));
    }

    /**
     * Aprueba la reservación (valida mantenimiento y cruces de horario).
     */
    public function approve(Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->approve($reservation);
        } catch (ReservationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'La reservación ha sido aprobada.');
    }

    /**
     * Rechaza la reservación registrando el motivo.
     */
    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        try {
            $this->reservations->reject($reservation, $request->rejection_reason);
        } catch (ReservationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'La reservación fue rechazada.');
    }

    /**
     * Registra la entrega/devolución y libera el aula/dispositivo.
     */
    public function complete(Reservation $reservation): RedirectResponse
    {
        try {
            $this->reservations->complete($reservation);
        } catch (ReservationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'El equipo o aula ha sido marcado como entregado/devuelto.');
    }

    // ----------------------------------------------------------------------
    // AYUDAS PRIVADAS
    // ----------------------------------------------------------------------

    /**
     * Consulta base: filtra por estados y aplica la búsqueda por nombre de usuario,
     * aula, dispositivo o número de serie.
     *
     * @param  array<int, string>  $statuses
     */
    private function baseQuery(Request $request, array $statuses): Builder
    {
        $query = Reservation::with(['classroom.building', 'device', 'user'])
            ->whereIn('status', $statuses);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('classroom', fn ($c) => $c->where('classroom', 'LIKE', "%{$search}%"))
                  ->orWhereHas('device', fn ($d) => $d
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('serial_number', 'LIKE', "%{$search}%"));
            });
        }

        return $query;
    }
}
