<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        $fichierRequired = $this->isMethod('POST') && ! $this->filled('url_externe');

        return [
            'titre'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'album'       => ['nullable', 'string', 'max:255'],
            'album_id'    => ['nullable', 'exists:intranet_media_albums,id'],
            'tags'        => ['nullable', 'string'],
            'is_public'   => ['nullable', 'boolean'],

            'fichier'      => [$fichierRequired ? 'required' : 'nullable', 'file', 'max:102400'],
            'url_externe'  => ['nullable', 'url', 'max:500'],

            'visibilite'         => ['nullable', 'in:public,prive,brouillon'],
            'likes_actifs'       => ['nullable', 'boolean'],
            'commentaires_actifs'=> ['nullable', 'boolean'],
            'cibles_users'       => ['nullable', 'array'],
            'cibles_users.*'     => ['integer', 'exists:users,id'],
            'cibles_groupes'     => ['nullable', 'array'],
            'cibles_groupes.*'   => ['integer', 'exists:intranet_groupes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'fichier.required' => 'Veuillez sélectionner un fichier ou saisir une URL vidéo externe.',
            'url_externe.url'  => "L'URL vidéo n'est pas valide.",
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public'           => $this->boolean('is_public'),
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
