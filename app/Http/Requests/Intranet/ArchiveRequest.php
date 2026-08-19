<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'                    => ['required', 'string', 'max:255'],
            'description'              => ['nullable', 'string'],
            'nature'                   => ['nullable', 'string', 'max:80'],
            'tags'                     => ['nullable', 'string'],
            'reference'                => ['nullable', 'string', 'max:50'],
            'date_document'            => ['nullable', 'date'],
            'date_archivage'           => ['nullable', 'date'],
            'duree_conservation_mois'  => ['nullable', 'integer', 'min:1'],
            'date_destruction_prevue'  => ['nullable', 'date'],
            'lieu_physique'            => ['nullable', 'string', 'max:255'],
            'code_barre'              => ['nullable', 'string', 'max:50'],
            'statut'                   => ['nullable', 'in:actif,semi_actif,inactif,a_detruire'],
            'dossier_id'               => ['nullable', 'exists:intranet_archive_dossiers,id'],
            'is_confidentiel'          => ['nullable', 'boolean'],

            'fichier'       => [$this->isMethod('POST') ? 'required' : 'nullable', 'file', 'max:102400'],
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
            'is_confidentiel'     => $this->boolean('is_confidentiel'),
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
