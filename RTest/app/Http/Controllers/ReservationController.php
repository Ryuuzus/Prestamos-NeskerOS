<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationException;
use App\Http\Requests\ReservationRequest;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Reservaciones para el USUARIO (ReservationController).
 *
 * El rol user gestiona únicamente sus propias reservaciones: ver su lista, crear, editar y
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
     * Solo el rol user puede consultar y gestionar sus solicitudes propias.
     */
    public function index(Request $request): View|RedirectResponse
    {
        Gate::authorize('manage-own-reservations');

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
        Gate::authorize('manage-own-reservations');

        $reservation = new Reservation();
        [$classrooms, $devices] = $this->reservableResources();

        return view('reservation.create', compact('reservation', 'classrooms', 'devices'));
    }

    /**
     * Valida y registra una nueva solicitud (queda en estado pendiente).
     */
    public function store(ReservationRequest $request): RedirectResponse
    {
        Gate::authorize('manage-own-reservations');

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
        Gate::authorize('manage-own-reservations');

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
        Gate::authorize('manage-own-reservations');

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
        Gate::authorize('manage-own-reservations');

        $this->authorizeOwner($reservation);

        try {
            $this->reservations->delete($reservation, false);
        } catch (ReservationException $e) {
            return redirect()->route('reservations.index')->with('error', $e->getMessage());
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación eliminada correctamente.');
    }

    /**
     * Horas no disponibles (JSON) para el aula / dispositivo / cantidad elegidos, a partir de la
     * fecha de inicio indicada. El formulario las usa para deshabilitar las horas ocupadas.
     */
    public function availability(Request $request, AvailabilityService $availability): JsonResponse
    {
        Gate::authorize('manage-own-reservations');

        $data = $request->validate([
            'classroom_id'    => ['nullable', 'integer', 'exists:classrooms,id'],
            'device_id'       => ['nullable', 'integer', 'exists:devices,id'],
            'device_quantity' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'date'            => ['required', 'date_format:Y-m-d'],
        ]);

        // Desde el inicio de la fecha elegida hasta el último momento en que podría terminar la reservación.
        $from = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $days = (int) ceil((int) config('reservations.max_duration_hours', 48) / 24) + 1;
        $to   = $from->copy()->addDays($days);

        $intervals = $availability->blockedIntervals(
            ! empty($data['classroom_id']) ? Classroom::find($data['classroom_id']) : null,
            ! empty($data['device_id']) ? Device::find($data['device_id']) : null,
            (int) ($data['device_quantity'] ?? 1),
            $from,
            $to,
        );

        return response()->json([
            'blocked' => array_map(
                fn (array $interval) => [$interval[0]->format('Y-m-d H:i:s'), $interval[1]->format('Y-m-d H:i:s')],
                $intervals,
            ),
        ]);
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
