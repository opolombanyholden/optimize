<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'extrait'          => ['nullable', 'string', 'max:500'],
            'rubrique'         => ['nullable', 'string', 'max:80'],
            'tags'             => ['nullable', 'string'], // chaîne séparée par virgules
            'content'          => ['required', 'string'],
            'auteur_signature' => ['nullable', 'string', 'max:255'],
            'source'           => ['nullable', 'string', 'max:500'],
            'date_debut'       => ['nullable', 'date'],
            'date_fin'         => ['nullable', 'date', 'after_or_equal:date_debut'],
            'a_la_une'         => ['nullable', 'boolean'],
            'couleur'          => ['nullable', 'string', 'max:20'],

            'media_principal'  => [
                'nullable', 'file', 'max:51200',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf,doc,docx,xls,xlsx,ppt,pptx',
            ],
            'pieces_jointes'   => ['nullable', 'array'],
            'pieces_jointes.*' => ['file', 'max:51200'],

            'visibilite'         => ['nullable', 'in:public,prive,brouillon'],
            'likes_actifs'       => ['nullable', 'boolean'],
            'commentaires_actifs'=> ['nullable', 'boolean'],
            'publie_le'          => ['nullable', 'date'],
            'expire_le'          => ['nullable', 'date', 'after_or_equal:publie_le'],

            'cibles_users'    => ['nullable', 'array'],
            'cibles_users.*'  => ['integer', 'exists:users,id'],
            'cibles_groupes'  => ['nullable', 'array'],
            'cibles_groupes.*'=> ['integer', 'exists:intranet_groupes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Le titre est obligatoire.',
            'content.required' => 'Le contenu est obligatoire.',
            'date_fin.after_or_equal'  => 'La date de fin doit être ≥ à la date de début.',
            'media_principal.max'      => 'Le fichier ne doit pas dépasser 50 Mo.',
            'media_principal.mimes'    => 'Format non supporté.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'a_la_une'            => $this->boolean('a_la_une'),
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }

    /**
     * Convertit la chaîne tags en tableau (côté contrôleur).
     */
    public function tagsArray(): array
    {
        $raw = $this->input('tags');
        if (! $raw) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
