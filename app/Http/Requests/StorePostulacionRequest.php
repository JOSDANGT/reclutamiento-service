<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostulacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vacante_id' => [
                'required',
                'integer',
                Rule::exists('sqlsrv.dbreclutamiento.vacantes', 'id')
                    ->where('estado', 'PUBLICADA')
                    ->whereNull('deleted_at'),
            ],
            'carta_presentacion' => [
                'nullable',
                'string',
                'max:4000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'vacante_id.required' => 'Debe seleccionar una vacante.',
            'vacante_id.exists' => 'La vacante no está disponible para postulaciones.',
        ];
    }
}
