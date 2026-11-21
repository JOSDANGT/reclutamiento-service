<?php

namespace App\Http\Requests\Candidato;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo_profesional' => ['sometimes', 'nullable', 'string', 'max:150'],
            'resumen_profesional' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'anios_experiencia' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'nivel_academico' => ['sometimes', 'nullable', 'string', 'max:100'],
            'telefono_contacto' => ['sometimes', 'nullable', 'string', 'max:25'],
            'pais' => ['sometimes', 'nullable', 'string', 'max:100'],
            'departamento' => ['sometimes', 'nullable', 'string', 'max:100'],
            'municipio' => ['sometimes', 'nullable', 'string', 'max:100'],
            'disponibilidad' => ['sometimes', 'nullable', 'string', 'max:30'],
            'pretension_salarial' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'github_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'portafolio_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ];
    }
}
