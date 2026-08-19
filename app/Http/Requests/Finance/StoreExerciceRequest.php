<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercice' => 'nullable|string|max:255',
            'libelle' => 'required|string|max:255',
            'datedebut' => 'nullable|date',
            'datefin' => 'nullable|date|after_or_equal:datedebut',
            'budgetglobalinitial' => 'nullable|numeric|min:0',
            'commentaire' => 'nullable|string',
            'statut' => 'nullable|integer|in:1,2,3',
        ];
    }
}
