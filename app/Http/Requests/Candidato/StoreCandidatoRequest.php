<?php

namespace App\Http\Requests\Candidato;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'integer', 'exists:dbreclutamiento.users,id'],
            'titulo_profesional' => ['nullable', 'string', 'max:150'],
            'resumen_profesional' => ['nullable', 'string', 'max:5000'],
            'anios_experiencia' => ['nullable', 'integer', 'min:0', 'max:100'],
            'nivel_academico' => ['nullable', 'string', 'max:100'],
            'telefono_contacto' => ['nullable', 'string', 'max:25'],
            'pais' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'municipio' => ['nullable', 'string', 'max:100'],
            'disponibilidad' => ['nullable', 'string', 'max:30'],
            'pretension_salarial' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'github_url' => ['nullable', 'url', 'max:500'],
            'portafolio_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
