<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_budgetligne' => 'required|string|max:255',
            'id_exercicebudgetaire' => 'required|exists:exercices,id',
            'id_famillecodeanalytique' => 'nullable|exists:titres,id',
            'id_codeanalytique' => 'nullable|exists:lignes,id',
            'codecompte' => 'nullable|string|max:255',
            'budgetligne' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string',
            'dotation_etat' => 'nullable|numeric|min:0',
            'fonds_propres' => 'nullable|numeric|min:0',
        ];
    }
}
