<?php

namespace App\Http\Requests\FormacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormacionAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institucion' => [
                'sometimes', 'required', 'string', 'max:150',
            ],
            'titulo' => [
                'sometimes', 'required', 'string', 'max:150',
            ],
            'nivel_academico' => [
                'sometimes', 'required', 'string', 'max:100',
            ],
            'area_estudio' => [
                'sometimes', 'nullable', 'string', 'max:150',
            ],
            'fecha_inicio' => [
                'sometimes', 'nullable', 'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'fecha_fin' => [
                'sometimes', 'nullable', 'date_format:Y-m-d',
            ],
            'en_curso' => [
                'sometimes', 'required', 'boolean',
            ],
            'finalizado' => [
                'sometimes', 'required', 'boolean',
            ],
            'descripcion' => [
                'sometimes', 'nullable', 'string',
            ],
        ];
    }
}
