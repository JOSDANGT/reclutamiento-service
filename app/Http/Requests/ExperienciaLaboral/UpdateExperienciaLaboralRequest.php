<?php

namespace App\Http\Requests\ExperienciaLaboral;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienciaLaboralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa' => [
                'sometimes', 'required', 'string', 'max:150',
            ],
            'puesto' => [
                'sometimes', 'required', 'string', 'max:150',
            ],
            'tipo_empleo' => [
                'sometimes', 'nullable', 'string', 'max:100',
            ],
            'ubicacion' => [
                'sometimes', 'nullable', 'string', 'max:255',
            ],
            'fecha_inicio' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'fecha_fin' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d',
            ],
            'trabajo_actual' => [
                'sometimes', 'required', 'boolean',
            ],
            'descripcion' => [
                'sometimes', 'nullable', 'string',
            ],
            'logros' => [
                'sometimes', 'nullable', 'string',
            ],
        ];
    }
}
