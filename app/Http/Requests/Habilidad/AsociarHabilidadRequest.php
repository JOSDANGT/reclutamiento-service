<?php

namespace App\Http\Requests\Habilidad;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AsociarHabilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'habilidad_id' => [
                'required',
                'integer',
                Rule::exists(
                    'sqlsrv.dbreclutamiento.habilidades',
                    'id'
                )->where('estado', true)
                    ->whereNull('deleted_at'),
            ],
            'nivel_habilidad_id' => [
                'required',
                'integer',
                Rule::exists(
                    'sqlsrv.dbreclutamiento.nivel_habilidad',
                    'id'
                )->where('activo', true),
            ],
            'anios_experiencia' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
    }
}
