<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'type_abscence' => 'nullable|string|max:50',
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string',
        ];
    }
}
