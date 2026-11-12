<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review-reservations') ?? false;
    }

    public function rules(): array
    {
        $verify = in_array($this->input('action'), ['verify_key', 'verify_devices'], true);

        return [
            'action' => ['required', Rule::in(['issue_key', 'issue_devices', 'return_key', 'return_devices', 'release', 'no_show', 'verify_key', 'verify_devices'])],
            'quantity' => ['required_if:action,return_devices', 'nullable', 'integer', 'min:1', 'max:9999'],
            'reason' => [Rule::requiredIf($verify || in_array($this->input('action'), ['release', 'no_show'], true)), 'nullable', 'string', 'max:500'],
            'custody' => [Rule::requiredIf($verify), 'nullable', Rule::in(['issued', 'not_issued'])],
            'occurred_at' => [Rule::requiredIf($verify && $this->input('custody') === 'issued'), 'nullable', 'date_format:Y-m-d\\TH:i', 'before_or_equal:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.in' => 'El movimiento seleccionado no es válido.',
            'quantity.required_if' => 'Indica cuántos equipos regresaron.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'Debes recibir al menos un equipo.',
            'quantity.max' => 'La cantidad no puede superar 9999.',
            'reason.required' => 'Escribe el motivo de este movimiento.',
            'reason.max' => 'El motivo no puede superar 500 caracteres.',
            'custody.required' => 'Confirma si el recurso se entregó.',
            'occurred_at.required' => 'Indica la fecha y hora de la entrega anterior.',
            'occurred_at.date_format' => 'Indica una fecha y hora válidas para la entrega anterior.',
            'occurred_at.before_or_equal' => 'La entrega anterior no puede tener una fecha futura.',
        ];
    }
}
