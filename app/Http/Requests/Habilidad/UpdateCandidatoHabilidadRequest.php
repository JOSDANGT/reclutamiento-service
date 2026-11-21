<?php

namespace App\Http\Requests\Habilidad;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidatoHabilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nivel_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists(
                    'sqlsrv.dbreclutamiento.nivel_habilidad',
                    'id'
                )->where('activo', true),
            ],

            'anios_experiencia' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nivel_id.required' => 'Debe seleccionar un nivel de habilidad.',
            'nivel_id.integer' => 'El nivel seleccionado no es válido.',
            'nivel_id.exists' => 'El nivel seleccionado no existe o está inactivo.',

            'anios_experiencia.integer' => 'Los años de experiencia deben ser un número entero.',
            'anios_experiencia.min' => 'Los años de experiencia no pueden ser negativos.',
            'anios_experiencia.max' => 'Los años de experiencia no pueden superar los 100 años.',
        ];
    }
}
