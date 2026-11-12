<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationException;
use App\Http\Requests\ReservationOperationRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReservationOperationController extends Controller
{
    public function show(Reservation $reservation): View
    {
        $reservation->load(['user', 'classroom.building', 'device']);
        $events = $reservation->events()->with('actor')->latest('id')->paginate(20);

        return view('reservation.admin.operations', compact('reservation', 'events'));
    }

    public function update(ReservationOperationRequest $request, Reservation $reservation, ReservationService $service): RedirectResponse
    {
        try {
            $service->operate($reservation, $request->validated());
        } catch (ReservationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('reservations.operations', $reservation)->with('success', 'Movimiento registrado correctamente.');
    }
}
