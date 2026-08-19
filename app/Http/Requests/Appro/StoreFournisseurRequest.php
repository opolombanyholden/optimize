<?php

namespace App\Http\Requests\Appro;

use Illuminate\Foundation\Http\FormRequest;

class StoreFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raison_sociale' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'categorie' => 'nullable|string|max:255',
        ];
    }
}
