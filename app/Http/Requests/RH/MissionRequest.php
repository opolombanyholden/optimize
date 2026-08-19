<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class MissionRequest extends FormRequest
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
            'lieu'         => ['nullable', 'string', 'max:255'],
            'introduction' => ['nullable', 'string'],
            'description'  => ['nullable', 'string'],
            'debut'        => ['required', 'date'],
            'fin'          => ['required', 'date', 'after_or_equal:debut'],
            'budget'       => ['nullable', 'numeric', 'min:0'],
            'frais_reels'  => ['nullable', 'numeric', 'min:0'],
            'statut'       => ['nullable', 'integer', 'in:0,1,2'],
        ];
    }
}
