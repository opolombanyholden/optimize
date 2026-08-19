<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class QualificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'employee_id'  => ['required', 'exists:employees,id'],
            'label'        => ['required', 'string', 'max:255'],
            'organisme'    => ['nullable', 'string', 'max:255'],
            'niveau'       => ['nullable', 'string', 'max:50'],
            'introduction' => ['nullable', 'string'],
            'description'  => ['nullable', 'string'],
            'debut'        => ['nullable', 'date'],
            'fin'          => ['nullable', 'date', 'after_or_equal:debut'],
            'statut'       => ['nullable', 'integer', 'in:0,1'],
        ];
    }
}
