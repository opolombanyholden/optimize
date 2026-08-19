<?php

namespace App\Http\Requests\Intranet;

use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'titre'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'contenu'      => ['nullable', 'string'],
            'format'       => ['required', 'in:html,fichier'],
            'categorie_id' => ['nullable', 'exists:intranet_template_categories,id'],
            'tags'         => ['nullable', 'string'],
            'is_public'    => ['nullable', 'boolean'],
            'variables'    => ['nullable', 'string'], // comma-separated

            'fichier_modele' => ['nullable', 'file', 'max:51200',
                'mimes:doc,docx,xls,xlsx,ppt,pptx,pdf,odt,ods,odp'],

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

    public function variablesArray(): array
    {
        $raw = $this->input('variables');
        if (! $raw) return [];
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
