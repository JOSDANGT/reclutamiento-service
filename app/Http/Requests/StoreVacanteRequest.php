<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
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

            'puesto_id' => [
                'required',
                'integer',
                Rule::exists('sqlsrv.dbreclutamiento.puestos', 'id')
                    ->where('estado', true)
                    ->whereNull('deleted_at'),
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
                Rule::exists('sqlsrv.dbreclutamiento.requisitos', 'id'),
            ],
            'requisitos.*.obligatorio' => ['required', 'boolean'],
            'requisitos.*.orden' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => 'BORRADOR',
        ]);
    }
}
