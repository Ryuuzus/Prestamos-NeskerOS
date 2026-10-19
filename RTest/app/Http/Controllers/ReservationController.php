<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationException;
use App\Http\Requests\ReservationRequest;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Reservaciones para el USUARIO (ReservationController).
 *
 * Cada usuario gestiona únicamente sus propias reservaciones: ver su lista, crear, editar y
 * cancelar solicitudes pendientes. La validación vive en ReservationRequest y las reglas de
 * estado en ReservationService.
 *
 * Las tareas del administrador (panel de activas, historial, aprobar, rechazar y completar) están
 * en App\Http\Controllers\Admin\ReservationController.
 * ==================================================================================================
 */
class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    /**
     * Lista las reservaciones del usuario autenticado.
     * Los administradores trabajan desde su propio panel, así que se les redirige allí.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ((int) auth()->user()->is_admin === 1) {
            return redirect()->route('admin.reservations.index');
        }

        $query = Reservation::with(['classroom', 'device', 'user'])
            ->where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('status', 'LIKE', "%{$search}%")
                  ->orWhereHas('classroom', fn ($c) => $c->where('classroom', 'LIKE', "%{$search}%"))
                  ->orWhereHas('device', fn ($d) => $d->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('reservation.index', compact('reservations'));
    }

    /**
     * Muestra el formulario para registrar una nueva reservación.
     * Solo se ofrecen aulas y dispositivos que no estén en mantenimiento.
     */
    public function create(): View
    {
        $reservation = new Reservation();
        [$classrooms, $devices] = $this->reservableResources();

        return view('reservation.create', compact('reservation', 'classrooms', 'devices'));
    }

    /**
     * Valida y registra una nueva solicitud (queda en estado pendiente).
     */
    public function store(ReservationRequest $request): RedirectResponse
    {
        Reservation::create($request->validated() + [
            'user_id' => auth()->id(),
            'status'  => Reservation::STATUS_PENDING,
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'Tu solicitud ha sido enviada y está en espera de aprobación.');
    }

    /**
     * Muestra el formulario de edición (solo reservaciones propias y pendientes).
     */
    public function edit(Reservation $reservation): View|RedirectResponse
    {
        $this->authorizeOwner($reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->route('reservations.index')
                ->with('error', 'No puedes editar una reservación que ya fue procesada.');
        }

        [$classrooms, $devices] = $this->reservableResources();

        return view('reservation.edit', compact('reservation', 'classrooms', 'devices'));
    }

    /**
     * Valida y actualiza una reservación propia y pendiente.
     */
    public function update(ReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $this->authorizeOwner($reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->route('reservations.index')
                ->with('error', 'No puedes editar una reservación que ya fue procesada.');
        }

        $reservation->update($request->validated());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación actualizada correctamente.');
    }

    /**
     * Cancela una reservación propia. Un usuario solo puede cancelar solicitudes pendientes.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->authorizeOwner($reservation);

        try {
            $this->reservations->delete($reservation, false);
        } catch (ReservationException $e) {
            return redirect()->route('reservations.index')->with('error', $e->getMessage());
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación eliminada correctamente.');
    }

    // ----------------------------------------------------------------------
    // AYUDAS PRIVADAS
    // ----------------------------------------------------------------------

    /**
     * Un usuario solo puede tocar sus propias reservaciones.
     */
    private function authorizeOwner(Reservation $reservation): void
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
    }

    /**
     * Aulas y dispositivos que se pueden reservar (todos los que no están en mantenimiento).
     *
     * @return array{0: \Illuminate\Database\Eloquent\Collection, 1: \Illuminate\Database\Eloquent\Collection}
     */
    private function reservableResources(): array
    {
        return [
            Classroom::with('building')->where('status', '!=', 'maintenance')->orderBy('classroom')->get(),
            Device::where('status', '!=', 'maintenance')->orderBy('name')->get(),
        ];
    }
}
