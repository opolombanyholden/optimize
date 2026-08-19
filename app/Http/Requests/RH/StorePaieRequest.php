<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class StorePaieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
            'salaire_base' => 'required|numeric|min:0',
            'primes' => 'nullable|numeric|min:0',
            'indemnites' => 'nullable|numeric|min:0',
            'heures_sup' => 'nullable|numeric|min:0',
            'cotisations_salariales' => 'nullable|numeric|min:0',
            'irpp' => 'nullable|numeric|min:0',
        ];
    }
}
