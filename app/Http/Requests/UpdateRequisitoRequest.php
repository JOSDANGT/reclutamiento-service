<?php

namespace App\Http\Requests;

class UpdateRequisitoRequest extends StoreRequisitoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['tipo_requisito_id', 'nombre',] as $campo) {
            array_unshift($rules[$campo], 'sometimes');
            $rules[$campo] = array_values(
                array_filter(
                    $rules[$campo],
                    fn($rule) => $rule !== 'required'
                )
            );

            $rules[$campo][] = 'required';
        }

        return $rules;
    }
}
