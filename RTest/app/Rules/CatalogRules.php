<?php

namespace App\Rules;

class CatalogRules
{
    public const NAME_MAX = 100;

    public const SEARCH_MAX = 100;

    public const FLOORS_MAX = 100;

    public const NAME_PATTERN = '(?=.*[\p{L}\p{N}])[\p{L}\p{M}\p{N} .\-]+';

    public const NAME_HELP = 'Máximo 100 caracteres. Usa letras, números, espacios, puntos y guiones; incluye al menos una letra o número.';

    public static function name(): array
    {
        return ['bail', 'required', 'string', 'max:'.self::NAME_MAX, 'regex:/\A'.self::NAME_PATTERN.'\z/u'];
    }

    public static function normalize(mixed $value): mixed
    {
        return is_string($value) ? preg_replace('/\s+/u', ' ', trim($value)) : $value;
    }

    public static function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max' => 'El campo :attribute supera el límite de :max.',
            'in' => 'El estado debe ser available o maintenance.',
            'exists' => 'El edificio indicado no existe.',
            'unique' => 'Ya existe un registro con ese :attribute.',
            'regex' => self::NAME_HELP,
            'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'classroom.max' => 'El nombre del aula no puede superar los 100 caracteres.',
        ];
    }

    public static function importMessages(array $fields): array
    {
        $messages = [];
        foreach ($fields as $field) {
            foreach (self::messages() as $rule => $message) {
                if (! str_contains($rule, '.')) {
                    $messages[$field.'.'.$rule] = $message;
                }
            }
        }

        return [...$messages, 'name.max' => 'El nombre no puede superar los 100 caracteres.',
            'classroom.max' => 'El nombre del aula no puede superar los 100 caracteres.',
            'serial_number.max' => 'El número de serie no puede superar los 50 caracteres.'];
    }
}
