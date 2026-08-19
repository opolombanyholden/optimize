<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class AffilieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employee_id'    => ['required', 'exists:employees,id'],
            'liens'          => ['required', 'string', 'max:50'],
            'noms'           => ['required', 'string', 'max:255'],
            'prenoms'        => ['required', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'contact1'       => ['nullable', 'string', 'max:30'],
            'contact2'       => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:255'],
            'label'          => ['nullable', 'string', 'max:255'],
            'statut'         => ['nullable', 'integer', 'in:0,1'],
        ];
    }
}
