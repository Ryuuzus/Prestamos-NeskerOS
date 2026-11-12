<?php

namespace App\Http\Requests;

use App\Models\Building;
use App\Rules\CatalogRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $building = $this->route('building');

        return [
            'name' => [...CatalogRules::name(), Rule::unique('buildings', 'name')->ignore($building instanceof Building ? $building->id : null)],
            'floors' => ['bail', 'required', 'integer', 'min:1', 'max:'.CatalogRules::FLOORS_MAX,
                function (string $attribute, mixed $value, \Closure $fail) use ($building): void {
                    if ($building instanceof Building && $building->classrooms()->where('floor', '>', $value)->exists()) {
                        $fail('No puedes quitar pisos que todavía tienen aulas registradas. Reubica primero esas aulas.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['available', 'maintenance'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $building = $this->route('building');
        $this->errorBag = $building instanceof Building ? 'editBuilding'.$building->id : 'createBuilding';
        $this->merge(['name' => CatalogRules::normalize($this->input('name'))]);
    }

    public function messages(): array
    {
        return CatalogRules::messages();
    }

    public function attributes(): array
    {
        return ['name' => 'nombre del edificio', 'floors' => 'número de pisos', 'status' => 'estado'];
    }
}
