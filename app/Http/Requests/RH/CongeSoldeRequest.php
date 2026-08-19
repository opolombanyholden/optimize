<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class CongeSoldeRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'employee_id'             => ['required', 'exists:employees,id'],
            'annee'                   => ['required', 'integer', 'min:2020', 'max:2100'],
            'type_conge'              => ['required', 'in:annuel,rtt,anciennete,exceptionnel'],
            'droit_annuel'            => ['required', 'numeric', 'min:0'],
            'report_n_moins_1'        => ['nullable', 'numeric', 'min:0'],
            'acquis_periode'          => ['nullable', 'numeric', 'min:0'],
            'pris_periode'            => ['nullable', 'numeric', 'min:0'],
            'en_attente'              => ['nullable', 'numeric', 'min:0'],
            'date_debut_acquisition'  => ['nullable', 'date'],
            'date_fin_acquisition'    => ['nullable', 'date', 'after_or_equal:date_debut_acquisition'],
        ];
    }
}
