<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class SanctionRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'employee_id'       => ['required', 'exists:employees,id'],
            'type'              => ['required', 'in:rappel_ordre,blame,avertissement,mise_a_pied,licenciement'],
            'motif'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'date_fait'         => ['nullable', 'date'],
            'date_notification' => ['required', 'date'],
            'date_fin'          => ['nullable', 'date', 'after_or_equal:date_notification'],
            'niveau_gravite'    => ['nullable', 'in:mineure,modere,grave,severe'],
            'reaction_employe'  => ['nullable', 'string'],
            'statut'            => ['nullable', 'integer', 'in:0,1,2'],
        ];
    }
}
