<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class AnnonceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'extrait'      => ['nullable', 'string', 'max:500'],
            'categorie'    => ['nullable', 'string', 'max:80'],
            'content'      => ['required', 'string'],
            'date_debut'   => ['nullable', 'date'],
            'date_fin'     => ['nullable', 'date', 'after_or_equal:date_debut'],
            'is_urgent'    => ['nullable', 'boolean'],
            'epingle'      => ['nullable', 'boolean'],
            'couleur'      => ['nullable', 'string', 'max:20'],

            // Média principal (image / vidéo / document)
            'media_principal' => [
                'nullable', 'file', 'max:51200', // 50 Mo
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf,doc,docx,xls,xlsx,ppt,pptx',
            ],

            // Pièces jointes additionnelles
            'pieces_jointes'   => ['nullable', 'array'],
            'pieces_jointes.*' => ['file', 'max:51200'],

            // Publication
            'visibilite'         => ['nullable', 'in:public,prive,brouillon'],
            'likes_actifs'       => ['nullable', 'boolean'],
            'commentaires_actifs'=> ['nullable', 'boolean'],
            'publie_le'          => ['nullable', 'date'],
            'expire_le'          => ['nullable', 'date', 'after_or_equal:publie_le'],

            // Cibles (tableau d'IDs)
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
            'date_fin.after_or_equal'    => 'La date de fin doit être ≥ à la date de début.',
            'media_principal.max'        => 'Le fichier ne doit pas dépasser 50 Mo.',
            'media_principal.mimes'      => 'Format non supporté pour le média principal.',
            'pieces_jointes.*.max'       => 'Chaque pièce jointe doit faire au maximum 50 Mo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_urgent' => $this->boolean('is_urgent'),
            'epingle'   => $this->boolean('epingle'),
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }
}
