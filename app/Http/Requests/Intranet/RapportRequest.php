<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class RapportRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'        => ['required', 'string', 'max:255'],
            'extrait'      => ['nullable', 'string', 'max:500'],
            'type'         => ['required', 'in:rapport,cr,pv,note,memo'],
            'reference'    => ['nullable', 'string', 'max:50'],
            'contenu'      => ['nullable', 'string'],
            'statut'       => ['nullable', 'in:brouillon,en_revision,valide,publie'],
            'date_document'=> ['nullable', 'date'],
            'evenement_id' => ['nullable', 'exists:intranet_evenements,id'],
            'projet_id'    => ['nullable', 'exists:intranet_projets,id'],
            'phase_id'     => ['nullable', 'exists:intranet_projet_phases,id'],
            'tache_id'     => ['nullable', 'exists:intranet_taches,id'],
            'activite_id'  => ['nullable', 'exists:intranet_activites,id'],
            'valideur_id'  => ['nullable', 'exists:users,id'],
            'tags'         => ['nullable', 'string'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['integer', 'exists:users,id'],

            'media_principal'  => ['nullable', 'file', 'max:51200', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'pieces_jointes'   => ['nullable', 'array'],
            'pieces_jointes.*' => ['file', 'max:51200'],

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
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }

    public function tagsArray(): array
    {
        $raw = $this->input('tags');
        if (! $raw) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
