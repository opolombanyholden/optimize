<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class RubriqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $r = $this->route('rubrique');
        $id = is_object($r) ? $r->id : ($r ?: 'NULL');
        return [
            'code'             => ['required', 'string', 'max:20', "unique:rubriques,code,{$id}"],
            'libelle'          => ['required', 'string', 'max:255'],
            'type'             => ['required', 'in:gain,retenue,cotisation'],
            'base_calcul'      => ['required', 'in:fixe,pourcentage,formule'],
            'taux'             => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'montant_fixe'     => ['nullable', 'numeric', 'min:0'],
            'formule'          => ['nullable', 'string'],
            'imposable'        => ['nullable', 'boolean'],
            'cotisable'        => ['nullable', 'boolean'],
            'ordre_affichage'  => ['nullable', 'integer', 'min:0'],
            'statut'           => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'imposable' => $this->boolean('imposable'),
            'cotisable' => $this->boolean('cotisable'),
        ]);
    }
}
