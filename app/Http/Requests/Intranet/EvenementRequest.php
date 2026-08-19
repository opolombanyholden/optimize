<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class EvenementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'titre'             => ['required', 'string', 'max:255'],
            'extrait'           => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string'],
            'type_evenement_id' => ['required', 'exists:intranet_type_evenements,id'],
            'date_debut'        => ['required', 'date'],
            'date_fin'          => ['required', 'date', 'after_or_equal:date_debut'],
            'journee_entiere'   => ['nullable', 'boolean'],
            'lieu'              => ['nullable', 'string', 'max:255'],
            'lieu_url'          => ['nullable', 'string', 'max:500'],
            'est_visio'         => ['nullable', 'boolean'],
            'lien_visio'        => ['nullable', 'string', 'max:500'],
            'capacite_max'      => ['nullable', 'integer', 'min:1', 'max:9999'],
            'inscription_requise' => ['nullable', 'boolean'],
            'rappel_minutes'    => ['nullable', 'integer', 'min:0'],
            'couleur'           => ['nullable', 'string', 'max:20'],
            'statut'            => ['nullable', 'in:prevu,en_cours,termine,annule'],
            'recurrence'        => ['nullable', 'in:aucune,quotidienne,hebdomadaire,mensuelle,annuelle'],
            'recurrence_jusqu_au' => ['nullable', 'date', 'after:date_debut'],

            'media_principal'   => ['nullable', 'file', 'max:51200', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf'],
            'pieces_jointes'    => ['nullable', 'array'],
            'pieces_jointes.*'  => ['file', 'max:51200'],

            'visibilite'         => ['nullable', 'in:public,prive,brouillon'],
            'likes_actifs'       => ['nullable', 'boolean'],
            'commentaires_actifs'=> ['nullable', 'boolean'],
            'publie_le'          => ['nullable', 'date'],
            'expire_le'          => ['nullable', 'date'],

            'cibles_users'    => ['nullable', 'array'],
            'cibles_users.*'  => ['integer', 'exists:users,id'],
            'cibles_groupes'  => ['nullable', 'array'],
            'cibles_groupes.*'=> ['integer', 'exists:intranet_groupes,id'],

            'participants'    => ['nullable', 'array'],
            'participants.*'  => ['integer', 'exists:users,id'],

            'invites_externes'   => ['nullable', 'array'],
            'invites_externes.*' => ['email:rfc'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required'             => 'Le titre est obligatoire.',
            'type_evenement_id.required' => 'Choisissez un type d\'événement.',
            'date_debut.required'        => 'La date de début est obligatoire.',
            'date_fin.required'          => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal'    => 'La date de fin doit être ≥ à la date de début.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'journee_entiere'     => $this->boolean('journee_entiere'),
            'est_visio'           => $this->boolean('est_visio'),
            'inscription_requise' => $this->boolean('inscription_requise'),
            'likes_actifs'        => $this->boolean('likes_actifs'),
            'commentaires_actifs' => $this->boolean('commentaires_actifs'),
        ]);
    }
}
