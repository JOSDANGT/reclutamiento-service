<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Temporal para pruebas locales.
        // Proteger con autenticación antes de entregar.
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:4000'],

            'cantidad_plazas' => [
                'required', 'integer', 'min:1'
            ],

            'salario_minimo' => [
                'nullable', 'numeric', 'min:0'
            ],

            'salario_maximo' => [
                'nullable', 'numeric',
                'min:0', 'gte:salario_minimo'
            ],

            'modalidad' => ['required', 'string', 'max:50'],
            'tipo_contrato' => ['required', 'string', 'max:50'],
            'nivel_experiencia' => ['required', 'string', 'max:50'],

            'estado' => [
                'required',
                Rule::in(['BORRADOR', 'PUBLICADA', 'CERRADA'])
            ],

            'fecha_inicio' => ['nullable', 'date'],

            'fecha_limite' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio'
            ],

            'requisitos' => ['sometimes', 'array'],

            'requisitos.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(
                    'sqlsrv.dbreclutamiento.requisitos',
                    'id'
                )->where('estado', 1)->whereNull('deleted_at'),
            ],

            'requisitos.*.obligatorio' => [
                'required', 'boolean'
            ],

            'requisitos.*.orden' => [
                'required', 'integer', 'min:0'
            ],
        ];
    }
}
