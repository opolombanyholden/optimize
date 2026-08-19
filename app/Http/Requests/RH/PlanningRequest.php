<?php

namespace App\Http\Requests\RH;

use Illuminate\Foundation\Http\FormRequest;

class PlanningRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'employee_id'        => ['required', 'exists:employees,id'],
            'date_jour'          => ['required', 'date'],
            'heure_debut'        => ['nullable', 'date_format:H:i'],
            'heure_fin'          => ['nullable', 'date_format:H:i', 'after:heure_debut'],
            'heure_debut_pause'  => ['nullable', 'date_format:H:i'],
            'heure_fin_pause'    => ['nullable', 'date_format:H:i', 'after_or_equal:heure_debut_pause'],
            'heures_prevues'     => ['nullable', 'numeric', 'min:0', 'max:24'],
            'heures_reelles'     => ['nullable', 'numeric', 'min:0', 'max:24'],
            'type_journee'       => ['required', 'in:travail,repos,ferie,conge,absence'],
            'lieu'               => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
            'statut'             => ['nullable', 'integer', 'in:0,1,2'],
        ];
    }
}
