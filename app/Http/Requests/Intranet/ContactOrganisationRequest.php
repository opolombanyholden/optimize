<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class ContactOrganisationRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'nom'              => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'secteur_id'       => ['nullable', 'exists:intranet_secteurs_activite,id'],
            'email'            => ['nullable', 'email', 'max:255'],
            'telephone'        => ['nullable', 'string', 'max:30'],
            'site_web'         => ['nullable', 'string', 'max:500'],
            'linkedin'         => ['nullable', 'string', 'max:500'],
            'adresse'          => ['nullable', 'string'],
            'pays_id'          => ['nullable', 'exists:intranet_pays,id'],
            'ville'            => ['nullable', 'string', 'max:255'],
            'code_postal'      => ['nullable', 'string', 'max:20'],
            'taille'           => ['nullable', 'in:TPE,PME,ETI,GE'],
            'chiffre_affaires' => ['nullable', 'numeric', 'min:0'],
            'effectif'         => ['nullable', 'integer', 'min:0'],
            'siret'            => ['nullable', 'string', 'max:30'],
            'numero_tva'       => ['nullable', 'string', 'max:30'],
            'tags'             => ['nullable', 'string'],
            'etiquette'        => ['nullable', 'in:hot,warm,cold'],
            'est_client'       => ['nullable', 'boolean'],
            'est_prospect'     => ['nullable', 'boolean'],
            'notes'            => ['nullable', 'string'],
            'logo'             => ['nullable', 'image', 'max:5120'],

            // ── Fusion : type + champs B2B ─────────────────
            'type'             => ['nullable', 'in:client,fournisseur,investisseur,administration,partenaire,autre'],
            'code'             => ['nullable', 'string', 'max:60'],
            'raison_sociale'   => ['nullable', 'string', 'max:255'],
            'forme_juridique'  => ['nullable', 'string', 'max:50'],
            'nif'              => ['nullable', 'string', 'max:50'],
            'rccm'              => ['nullable', 'string', 'max:50'],
            'rib'              => ['nullable', 'string', 'max:60'],
            'banque'           => ['nullable', 'string', 'max:100'],
            'statut'           => ['nullable', 'in:0,1'],
            'statut_relation'  => ['nullable', 'string', 'max:30'],
            'contact_principal_nom'       => ['nullable', 'string', 'max:255'],
            'contact_principal_telephone' => ['nullable', 'string', 'max:50'],
            'contact_principal_email'     => ['nullable', 'email', 'max:255'],

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
            'est_client'          => $this->boolean('est_client'),
            'est_prospect'        => $this->boolean('est_prospect'),
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
