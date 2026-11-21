<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoRequisitoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150',
                Rule::unique(
                    'sqlsrv.dbreclutamiento.tipos_requisitos',
                    'nombre'
                )->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
