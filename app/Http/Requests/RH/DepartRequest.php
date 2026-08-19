<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class DepartRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'employee_id'                => ['required', 'exists:employees,id'],
            'type_depart'                => ['required', 'in:demission,licenciement,retraite,fin_contrat,deces,rupture_conventionnelle'],
            'motif'                      => ['nullable', 'string', 'max:255'],
            'description'                => ['nullable', 'string'],
            'date_notification'          => ['required', 'date'],
            'date_effet'                 => ['required', 'date', 'after_or_equal:date_notification'],
            'date_solde_tout_compte'     => ['nullable', 'date'],
            'preavis_effectue'           => ['nullable', 'boolean'],
            'indemnite_depart'           => ['nullable', 'numeric', 'min:0'],
            'solde_conges_paye'          => ['nullable', 'numeric', 'min:0'],
            'entretien_sortie_effectue'  => ['nullable', 'boolean'],
            'notes_entretien_sortie'     => ['nullable', 'string'],
            'statut'                     => ['nullable', 'integer', 'in:0,1,2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'preavis_effectue'           => $this->boolean('preavis_effectue'),
            'entretien_sortie_effectue'  => $this->boolean('entretien_sortie_effectue'),
        ]);
    }
}
