<?php

namespace App\Http\Requests\HabilidadCategoria;

use App\Models\HabilidadCategoria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHabilidadCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoria = $this->route('categoria');

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique(HabilidadCategoria::class, 'nombre')
                    ->ignore($categoria->id),
            ],
            'descripcion' => ['sometimes', 'nullable', 'string'],
            'estado' => ['sometimes', 'boolean'],
        ];
    }
}
