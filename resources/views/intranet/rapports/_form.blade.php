@php
    $isEdit = isset($rapport) && $rapport->exists;
    $publication = $isEdit ? $rapport->publication : null;
    $tagsString = is_array($rapport->tags ?? null) ? implode(', ', $rapport->tags) : '';
@endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-file-lines"></i> Rapport</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="form-control" required value="{{ old('titre', $rapport->titre) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach(['cr'=>'Compte rendu','pv'=>'Procès-verbal','rapport'=>'Rapport','note'=>'Note','memo'=>'Mémo'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('type', $rapport->type) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 mb-3">
                    <label class="form-label">Résumé / Chapeau</label>
                    <textarea name="extrait" rows="2" class="form-control" maxlength="500">{{ old('extrait', $rapport->extrait) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <div id="rapport-editor">{!! old('contenu', $rapport->contenu) !!}</div>
                    <input type="hidden" name="contenu" id="rapport-contenu" value="{{ old('contenu', $rapport->contenu) }}">
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-link"></i> Liens</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Lié à un événement</label>
                        <select name="evenement_id" id="select-evenement" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($evenements as $e)
                                <option value="{{ $e->id }}" @selected(old('evenement_id', $rapport->evenement_id) == $e->id)>
                                    {{ $e->titre }} ({{ $e->date_debut->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lié à un projet</label>
                        <select name="projet_id" id="select-projet" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($projets as $p)
                                <option value="{{ $p->id }}" @selected(old('projet_id', $rapport->projet_id) == $p->id)>{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phase du projet</label>
                        <select name="phase_id" id="select-phase" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($phases as $ph)
                                <option value="{{ $ph->id }}" @selected(old('phase_id', $rapport->phase_id) == $ph->id)>{{ $ph->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tâche</label>
                        <select name="tache_id" id="select-tache" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($taches as $t)
                                <option value="{{ $t->id }}" @selected(old('tache_id', $rapport->tache_id) == $t->id)>{{ $t->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Activité</label>
                        <select name="activite_id" id="select-activite" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($activites as $a)
                                <option value="{{ $a->id }}" @selected(old('activite_id', $rapport->activite_id) == $a->id)>{{ $a->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-users"></i> Participants</div>
            <div class="form-card-body">
                <select name="participants[]" id="select-participants" multiple placeholder="Participants à ce rapport…">
                    @foreach($utilisateurs as $u)
                        <option value="{{ $u->id }}"
                            data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                            data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                            @selected(in_array($u->id, old('participants', $rapport->participants ?? [])))>
                            {{ $u->prenoms }} {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $rapport, 'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.rapports.pieces-jointes.destroy',
            'deleteRouteParam' => 'rapport',
        ])
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-sliders"></i> Paramètres</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        @foreach(['brouillon'=>'Brouillon','en_revision'=>'En révision','valide'=>'Validé','publie'=>'Publié'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('statut', $rapport->statut ?? 'brouillon') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Référence</label>
                    <input type="text" name="reference" class="form-control" placeholder="Auto" value="{{ old('reference', $rapport->reference) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date du document</label>
                    <input type="date" name="date_document" class="form-control" value="{{ old('date_document', $rapport->date_document?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="réunion, stratégie…" value="{{ old('tags', $tagsString) }}">
                </div>
                <div class="mb-0">
                    <label class="form-label">Soumettre à (N+1)</label>
                    <select name="valideur_id" id="select-valideur" class="form-select">
                        <option value="">— Ne pas soumettre —</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected(old('valideur_id', $rapport->valideur_id) == $u->id)>
                                {{ $u->prenoms }} {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-hint">Pour les CR/PV : sélectionner le N+1 pour validation.</small>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer le rapport' }}
            </button>
            <a href="{{ route('intranet.rapports.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ['select-evenement', 'select-projet', 'select-phase', 'select-tache', 'select-activite', 'select-valideur'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    // Participants
    const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    const colorOf = (s) => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff; return palette[Math.abs(h) % palette.length]; };
    const partEl = document.getElementById('select-participants');
    if (partEl) new TomSelect(partEl, {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: 500,
        dropdownParent: 'body',
        render: {
            option: (data, escape) => {
                const ini = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                const sub = data.subtitle ? `<div class="ts-opt-sub">${escape(data.subtitle)}</div>` : '';
                return `<div class="ts-opt"><span class="ts-opt-avatar" style="background:${colorOf(data.text)};">${escape(ini)}</span><div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div>${sub}</div></div>`;
            },
            item: (data, escape) => {
                const ini = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                return `<div class="ts-item-user"><span class="ts-item-avatar" style="background:${colorOf(data.text)};">${escape(ini)}</span>${escape(data.text)}</div>`;
            },
            no_results: () => '<div class="no-results">Aucun résultat</div>',
        },
        onInitialize: function () {
            Array.from(partEl.options).forEach(opt => {
                if (this.options[opt.value]) {
                    this.options[opt.value].initials = opt.dataset.initials || '';
                    this.options[opt.value].subtitle = opt.dataset.subtitle || '';
                }
            });
        },
    });

    const editorEl = document.getElementById('rapport-editor');
    const hidden   = document.getElementById('rapport-contenu');
    if (editorEl) {
        const quill = new Quill('#rapport-editor', {
            theme: 'snow',
            placeholder: 'Rédigez votre rapport…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ color: [] }, { background: [] }],
                    [{ align: [] }],
                    ['link', 'image', 'blockquote', 'code-block'],
                    ['clean'],
                ],
            },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
