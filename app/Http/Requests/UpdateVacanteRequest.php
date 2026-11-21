<?php

namespace App\Http\Requests;

class UpdateVacanteRequest extends StoreVacanteRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['estado']);

        foreach ($rules as $field => $fieldRules) {
            if (!str_contains($field, '*')) {
                $rules[$field] = array_map(
                    fn ($rule) => $rule === 'required'
                        ? 'sometimes'
                        : $rule,
                    $fieldRules
                );
            }
        }

        return $rules;
    }
}
