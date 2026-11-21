<?php

namespace App\Http\Requests\Habilidad;

use App\Models\Habilidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHabilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique(Habilidad::class, 'nombre'),
            ],
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists('sqlsrv.dbreclutamiento.habilidad_categorias', 'id')
                    ->where('estado', true)
                    ->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
