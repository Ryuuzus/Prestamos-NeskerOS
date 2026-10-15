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
 * Controlador de Gestión de Reservaciones (ReservationController).
 *
 * Administra el ciclo de vida de las solicitudes de reserva de aulas y dispositivos.
 * La validación vive en ReservationRequest y las reglas de estado (aprobar, completar, liberar
 * recursos) en ReservationService.
 * ==================================================================================================
 */
class ReservationController extends Controller
{
    public function __construct(private ReservationService $reservations)
    {
    }

    /**
     * Muestra el listado de reservaciones filtrado según el rol del usuario autenticado.
     */
    public function index(Request $request): View
    {
        $query = Reservation::with(['classroom', 'device', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('status', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $query->orderBy('created_at', 'desc');

        if ($this->isAdmin()) {
            $reservations = $query->paginate(10)->withQueryString();
            return view('admin.index', compact('reservations'));
        }

        $reservations = $query->where('user_id', auth()->id())->paginate(10)->withQueryString();
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
     * Muestra el formulario de edición (solo reservaciones pendientes).
     */
    public function edit(Reservation $reservation): View|RedirectResponse
    {
        $this->authorizeOwnerOrAdmin($reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->route('reservations.index')
                ->with('error', 'No puedes editar una reservación que ya fue procesada.');
        }

        [$classrooms, $devices] = $this->reservableResources();

        return view('reservation.edit', compact('reservation', 'classrooms', 'devices'));
    }

    /**
     * Valida y actualiza una reservación pendiente.
     */
    public function update(ReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $this->authorizeOwnerOrAdmin($reservation);

        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->route('reservations.index')
                ->with('error', 'No puedes editar una reservación que ya fue procesada.');
        }

        $reservation->update($request->validated());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación actualizada correctamente.');
    }

    /**
     * Elimina / cancela una reservación. Si estaba aprobada, libera el recurso.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $this->authorizeOwnerOrAdmin($reservation);

        try {
            $this->reservations->delete($reservation, $this->isAdmin());
        } catch (ReservationException $e) {
            return redirect()->route('reservations.index')->with('error', $e->getMessage());
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación eliminada correctamente.');
    }

    // ----------------------------------------------------------------------
    // MÉTODOS EXCLUSIVOS PARA EL ADMINISTRADOR
    // ----------------------------------------------------------------------

    /**
     * Aprueba la reservación y marca el aula/dispositivo como ocupado.
     */
    public function approve(Reservation $reservation): RedirectResponse
    {
        $this->authorizeAdmin();

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
        $this->authorizeAdmin();

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
        $this->authorizeAdmin();

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

    private function isAdmin(): bool
    {
        return (int) auth()->user()->is_admin === 1;
    }

    private function authorizeAdmin(): void
    {
        abort_unless($this->isAdmin(), 403);
    }

    /**
     * Un usuario normal solo puede tocar sus propias reservaciones.
     */
    private function authorizeOwnerOrAdmin(Reservation $reservation): void
    {
        abort_unless($this->isAdmin() || $reservation->user_id === auth()->id(), 403);
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