<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationPerformanceRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'employee_id'                   => ['required', 'exists:employees,id'],
            'evaluateur_id'                 => ['nullable', 'exists:users,id'],
            'periode'                       => ['required', 'string', 'max:30'],
            'date_evaluation'               => ['required', 'date'],
            'date_entretien'                => ['nullable', 'date'],
            'note_globale'                  => ['nullable', 'integer', 'between:1,5'],
            'points_forts'                  => ['nullable', 'string'],
            'axes_amelioration'             => ['nullable', 'string'],
            'objectifs_periode_suivante'    => ['nullable', 'string'],
            'commentaire_employe'           => ['nullable', 'string'],
            'commentaire_manager'           => ['nullable', 'string'],
            'plan_developpement_individuel' => ['nullable', 'string'],
            'signature_employe'             => ['nullable', 'boolean'],
            'signature_manager'             => ['nullable', 'boolean'],
            'statut'                        => ['nullable', 'integer', 'in:0,1,2,3'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'signature_employe' => $this->boolean('signature_employe'),
            'signature_manager' => $this->boolean('signature_manager'),
        ]);
    }
}
