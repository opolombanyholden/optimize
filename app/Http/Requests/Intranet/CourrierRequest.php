<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class CourrierRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'type'                    => ['required', 'in:entrant,sortant,interne'],
            'objet'                   => ['required', 'string', 'max:255'],
            'extrait'                 => ['nullable', 'string', 'max:500'],
            'reference'               => ['nullable', 'string', 'max:50', 'unique:intranet_courriers,reference,' . ($this->route('courrier')?->id ?? 'NULL')],
            'contenu'                 => ['nullable', 'string'],

            'expediteur'              => ['nullable', 'string', 'max:255'],
            'expediteur_email'        => ['nullable', 'email', 'max:255'],
            'expediteur_organisation' => ['nullable', 'string', 'max:255'],
            'destinataire'            => ['nullable', 'string', 'max:255'],
            'destinataire_email'      => ['nullable', 'email', 'max:255'],

            'service_destinataire_id' => ['nullable', 'exists:intranet_services,id'],
            'service_expediteur_id'   => ['nullable', 'exists:intranet_services,id'],

            'date_reception'          => ['nullable', 'date'],
            'date_expedition'         => ['nullable', 'date'],
            'echeance_traitement'     => ['nullable', 'date'],

            'assigne_a'               => ['nullable', 'exists:users,id'],
            'statut'                  => ['nullable', 'string', 'max:30'],
            'priorite'                => ['nullable', 'in:normale,urgente'],
            'urgent'                  => ['nullable', 'boolean'],
            'confidentiel'            => ['nullable', 'boolean'],

            'media_principal'   => ['nullable', 'file', 'max:51200', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx'],
            'pieces_jointes'    => ['nullable', 'array'],
            'pieces_jointes.*'  => ['file', 'max:51200'],

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
            'type.required'     => 'Le type de courrier est obligatoire.',
            'objet.required'    => "L'objet est obligatoire.",
            'reference.unique'  => 'Cette référence est déjà utilisée.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'urgent'              => $this->boolean('urgent'),
            'confidentiel'        => $this->boolean('confidentiel'),
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }
}
