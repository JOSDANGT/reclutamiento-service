<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequisitoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_requisito_id' => [
                'required',
                'integer',
                Rule::exists(
                    'sqlsrv.dbreclutamiento.tipos_requisitos',
                    'id'
                )->where('estado', 1)->whereNull('deleted_at'),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
