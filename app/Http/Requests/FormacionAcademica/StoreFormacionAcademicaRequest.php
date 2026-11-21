<?php

namespace App\Http\Requests\FormacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormacionAcademicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institucion' => [
                'required', 'string', 'max:150',
            ],
            'titulo' => [
                'required', 'string', 'max:150',
            ],
            'nivel_academico' => [
                'required', 'string', 'max:100',
            ],
            'area_estudio' => [
                'nullable', 'string', 'max:150',
            ],
            'fecha_inicio' => [
                'nullable', 'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'fecha_fin' => [
                'nullable', 'date_format:Y-m-d',
            ],
            'en_curso' => [
                'required', 'boolean',
            ],
            'finalizado' => [
                'required', 'boolean',
            ],
            'descripcion' => [
                'nullable', 'string',
            ],
        ];
    }
}
