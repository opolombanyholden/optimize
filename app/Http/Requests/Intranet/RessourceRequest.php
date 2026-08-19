<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class RessourceRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type'        => ['required', 'in:dossier,fichier'],
            'parent_id'   => ['nullable', 'exists:intranet_ressources,id'],
            'categorie'   => ['nullable', 'string', 'max:80'],
            'tags'        => ['nullable', 'string'],
            'is_public'   => ['nullable', 'boolean'],
            'acces_restreint' => ['nullable', 'boolean'],

            'fichier'     => ['nullable', 'file', 'max:102400'], // 100 Mo
            'apercu'      => ['nullable', 'image', 'max:5120'], // image aperçu dossier
            'icone'       => ['nullable', 'string', 'max:50'],
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
            'is_public'           => $this->boolean('is_public'),
            'acces_restreint'     => $this->boolean('acces_restreint'),
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
