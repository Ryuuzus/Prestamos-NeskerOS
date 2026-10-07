<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Classroom;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Gestión de Reservaciones (ReservationController).
 * 
 * Administra el ciclo de vida de las solicitudes de reserva de aulas y dispositivos.
 * Implementa asignación de datos validados para prevenir inyección masiva
 * y resuelve los modelos mediante Route Model Binding.
 * ==================================================================================================
 */
class ReservationController extends Controller
{
    /**
     * Muestra el listado de reservaciones filtrado según el rol del usuario autenticado.
     */
    public function index(Request $request): View
    {
        $query = Reservation::with(['classroom', 'device', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function($q) use ($search) {
                $q->where('status', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $query->orderBy('created_at', 'desc');

        if (auth()->user()->is_admin == 1) {
            $reservations = $query->paginate(10)->withQueryString();
            return view('admin.index', compact('reservations'));
        }

        $reservations = $query->where('user_id', auth()->id())->paginate(10)->withQueryString();
        return view('reservation.index', compact('reservations'));
    }

    /**
     * Muestra el formulario para registrar una nueva reservación cargando los catálogos disponibles.
     */
    public function create(): View
    {
        $reservation = new Reservation(); 
        $users = User::all();
        $classrooms = Classroom::where('status', 'available')->get();
        $devices = Device::where('status', 'available')->get();

        return view('reservation.create', compact('reservation', 'users', 'classrooms', 'devices'));
    }

    /**
     * Valida y registra una nueva solicitud de reservación evitando asignaciones desprotegidas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => 'required_without:device_id|nullable|exists:classrooms,id',
            'device_id'    => 'required_without:classroom_id|nullable|exists:devices,id',
            'start_time'   => 'required|date|after_or_equal:today',
            'end_time'     => 'required|date|after:start_time',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'pending';

        Reservation::create($validated);

        return redirect()->route('reservations.index')
            ->with('success', 'Tu solicitud ha sido enviada y está en espera de aprobación.');
    }

    /**
     * Muestra el formulario de edición para una reservación existente.
     */
    public function edit(Reservation $reservation): View
    {
        $users = User::all();
        $classrooms = Classroom::all(); 
        $devices = Device::all();

        return view('reservation.edit', compact('reservation', 'users', 'classrooms', 'devices'));
    }

    /**
     * Valida y actualiza los campos permitidos de una reservación en estado pendiente.
     */
    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        if ($reservation->status !== 'pending') {
            return redirect()->route('reservations.index')
                ->with('error', 'No puedes editar una reservación que ya fue procesada.');
        }

        $validated = $request->validate([
            'classroom_id' => 'required_without:device_id|nullable|exists:classrooms,id',
            'device_id'    => 'required_without:classroom_id|nullable|exists:devices,id',
            'start_time'   => 'required|date',
            'end_time'     => 'required|date|after:start_time',
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación actualizada correctamente.');
    }

    /**
     * Elimina una reservación específica de la base de datos.
     */
    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return Redirect::route('reservations.index')
            ->with('success', 'Reservación eliminada correctamente.');
    }

    // ----------------------------------------------------------------------
    // MÉTODOS EXCLUSIVOS PARA EL ADMINISTRADOR
    // ----------------------------------------------------------------------

    /**
     * Cambia el estado de una reservación a aprobado.
     */
    public function approve(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => 'approved']);

        return back()->with('success', 'La reservación ha sido aprobada.');
    }

    /**
     * Cambia el estado de una reservación a rechazado registrando el motivo correspondiente.
     */
    public function reject(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        $reservation->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'La reservación fue rechazada.');
    }

    /**
     * Registra la finalización o devolución del recurso asociado a la reservación.
     */
    public function complete(Reservation $reservation): RedirectResponse
    {
        $reservation->update(['status' => 'completed']);

        return back()->with('success', 'El equipo o aula ha sido marcado como entregado/devuelto.');
    }
}
