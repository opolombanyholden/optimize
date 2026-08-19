<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id ?? $this->route('employee');
        $uniqueNip = 'unique:employees,nip' . ($employeeId ? ",{$employeeId}" : '');
        return [
            'noms' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:255|unique:employees,matricule' . ($employeeId ? ",{$employeeId}" : ''),
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|string|max:1',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:255',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:255',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'salaire_base' => 'nullable|numeric|min:0',
            // NIP — Numéro d'Identification Personnel (Gabon)
            // Format : XX-XXXX-AAAAMMJJ (préfixe 2 alphanum, série 4 alphanum, date naissance 8 chiffres)
            'nip' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]{2}-[A-Z0-9]{4}-\d{8}$/', $uniqueNip],
            'numero_secu' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.regex' => 'Le NIP doit respecter le format XX-XXXX-AAAAMMJJ (ex : A1-2345-19901225).',
            'nip.unique' => 'Ce NIP est déjà attribué à un autre employé.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('nip')) {
            $this->merge(['nip' => strtoupper(trim($this->input('nip')))]);
        }
    }
}
