<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreGrandLivreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_ecriture' => 'required|date',
            'id_entite' => 'required|exists:entites,id',
            'compte_id' => 'required|exists:comptes,id',
            'montant_tc' => 'required|numeric',
            'sens' => 'required|string|in:debit,credit',
            'libelle' => 'required|string|max:255',
            'mode_reglement' => 'nullable|string|max:255',
            'id_exercicebudgetaire' => 'nullable|exists:exercices,id',
            'description' => 'nullable|string',
        ];
    }
}
