<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class TacheRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'resume'          => ['nullable', 'string', 'max:500'],
            'besoins'         => ['nullable', 'string'],
            'projet_id'       => ['nullable', 'exists:intranet_projets,id'],
            'phase_id'        => ['nullable', 'exists:intranet_projet_phases,id'],
            'jalon_id'        => ['nullable', 'exists:intranet_jalons,id'],
            'priorite_id'     => ['nullable', 'exists:intranet_priorites,id'],
            'statut_id'       => ['nullable', 'exists:intranet_statuts,id'],
            'responsable_id'  => ['nullable', 'exists:users,id'],
            'date_debut'      => ['nullable', 'date'],
            'date_fin'        => ['nullable', 'date', 'after_or_equal:date_debut'],
            'date_debut_reelle'=> ['nullable', 'date'],
            'date_fin_reelle' => ['nullable', 'date'],
            'heures_estimees' => ['nullable', 'numeric', 'min:0'],
            'temps_reel_heures'=> ['nullable', 'numeric', 'min:0'],
            'avancement'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'est_jalon'       => ['nullable', 'boolean'],
            'cout_execution'  => ['nullable', 'numeric', 'min:0'],
            'devise_cout'     => ['nullable', 'string', 'max:10'],
            'ponderation'     => ['nullable', 'numeric', 'min:0', 'max:100'],

            'assignes'           => ['nullable', 'array'],
            'assignes.*'         => ['integer', 'exists:users,id'],
            'valideurs_users'    => ['nullable', 'array'],
            'valideurs_users.*'  => ['integer', 'exists:users,id'],
            'valideurs_groupes'  => ['nullable', 'array'],
            'valideurs_groupes.*'=> ['integer', 'exists:intranet_groupes,id'],

            'media_principal'    => ['nullable', 'file', 'max:51200'],
            'pieces_jointes'     => ['nullable', 'array'],
            'pieces_jointes.*'   => ['file', 'max:51200'],
            'justificatifs'      => ['nullable', 'array'],
            'justificatifs.*'    => ['file', 'max:51200'],

            'visibilite'         => ['nullable', 'in:public,prive,brouillon'],
            'likes_actifs'       => ['nullable', 'boolean'],
            'commentaires_actifs'=> ['nullable', 'boolean'],
            'cibles_users'       => ['nullable', 'array'],
            'cibles_users.*'     => ['integer', 'exists:users,id'],
            'cibles_groupes'     => ['nullable', 'array'],
            'cibles_groupes.*'   => ['integer', 'exists:intranet_groupes,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'est_jalon'           => $this->boolean('est_jalon'),
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }
}
