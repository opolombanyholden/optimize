<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'civilite'        => ['nullable', 'string', 'max:10'],
            'nom'             => ['required', 'string', 'max:255'],
            'prenoms'         => ['nullable', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'telephone'       => ['nullable', 'string', 'max:30'],
            'mobile'          => ['nullable', 'string', 'max:30'],
            'poste'           => ['nullable', 'string', 'max:255'],
            'organisation_id' => ['nullable', 'exists:intranet_contact_organisations,id'],
            'pays_id'         => ['nullable', 'exists:intranet_pays,id'],
            'ville'           => ['nullable', 'string', 'max:255'],
            'adresse'         => ['nullable', 'string'],
            'linkedin'        => ['nullable', 'string', 'max:500'],
            'twitter'         => ['nullable', 'string', 'max:255'],
            'site_web'        => ['nullable', 'string', 'max:500'],
            'langue'          => ['nullable', 'string', 'max:10'],
            'source'          => ['nullable', 'string', 'max:255'],
            'tags'            => ['nullable', 'string'], // virgules
            'etiquette'       => ['nullable', 'in:hot,warm,cold'],
            'categorie'       => ['nullable', 'in:client,fournisseur,partenaire,sponsor,prospect,autre'],
            'date_naissance'  => ['nullable', 'date'],
            'est_favori'      => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string'],
            'photo'           => ['nullable', 'image', 'max:5120'],

            // Média principal + pièces jointes
            'media_principal' => [
                'nullable', 'file', 'max:51200',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf,doc,docx,xls,xlsx,ppt,pptx',
            ],
            'pieces_jointes'   => ['nullable', 'array'],
            'pieces_jointes.*' => ['file', 'max:51200'],

            // Publication
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
            'est_favori'          => $this->boolean('est_favori'),
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
