<?php

namespace App\Http\Requests\ExperienciaLaboral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExperienciaLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa' => ['required', 'string', 'max:150'],
            'puesto' => ['required', 'string', 'max:150'],

            'tipo_empleo' => ['nullable', 'string', 'max:100'],
            'ubicacion' => ['nullable', 'string', 'max:255'],

            'fecha_inicio' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],

            'fecha_fin' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:fecha_inicio',
                Rule::prohibitedIf(
                    fn () => $this->boolean('trabajo_actual')
                ),
            ],

            'trabajo_actual' => ['required', 'boolean'],

            'descripcion' => ['nullable', 'string'],
            'logros' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.before_or_equal' =>
                'La fecha de inicio no puede ser futura.',

            'fecha_fin.after_or_equal' =>
                'La fecha de finalización no puede ser anterior al inicio.',

            'fecha_fin.prohibited' =>
                'Un trabajo actual no puede tener fecha de finalización.',
        ];
    }
}
