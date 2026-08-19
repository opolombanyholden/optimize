<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class EvenementCarriereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employee_id'                  => ['required', 'exists:employees,id'],
            'typesevenementscarriere_id'   => ['required', 'exists:typesevenementscarrieres,id'],
            'libelle'                      => ['required', 'string', 'max:255'],
            'description'                  => ['nullable', 'string'],
            'date_effet'                   => ['required', 'date'],
            'ancien_poste'                 => ['nullable', 'string', 'max:255'],
            'nouveau_poste'                => ['nullable', 'string', 'max:255'],
        ];
    }
}
