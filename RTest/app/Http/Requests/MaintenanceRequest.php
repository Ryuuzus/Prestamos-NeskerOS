<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalogs') ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:500']];
    }

    public function messages(): array
    {
        return ['reason.required' => 'Describe la avería o el motivo del mantenimiento.', 'reason.max' => 'El motivo no puede superar 500 caracteres.'];
    }
}
