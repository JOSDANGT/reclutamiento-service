<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoRequisitoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoRequisito = $this->route('tipoRequisito');

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique(
                    'sqlsrv.dbreclutamiento.tipos_requisitos',
                    'nombre'
                )
                    ->ignore($tipoRequisito->id)
                    ->whereNull('deleted_at'),
            ],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
