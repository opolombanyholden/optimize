<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class OpportuniteRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'              => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'contact_id'         => ['nullable', 'exists:intranet_contacts,id'],
            'organisation_id'    => ['nullable', 'exists:intranet_contact_organisations,id'],
            'etape_id'           => ['required', 'exists:intranet_crm_etapes,id'],
            'responsable_id'     => ['nullable', 'exists:users,id'],
            'valeur'             => ['nullable', 'numeric', 'min:0'],
            'devise'             => ['nullable', 'string', 'max:10'],
            'probabilite'        => ['nullable', 'integer', 'min:0', 'max:100'],
            'date_echeance'      => ['nullable', 'date'],
            'date_cloture_reelle'=> ['nullable', 'date'],
            'statut'             => ['nullable', 'in:ouvert,gagnee,perdue,abandonnee'],
            'raison_perte'       => ['nullable', 'string'],
            'source'             => ['nullable', 'string', 'max:255'],
            'tags'               => ['nullable', 'string'],
            'notes'              => ['nullable', 'string'],

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
