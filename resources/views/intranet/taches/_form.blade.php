@php
    $isEdit = isset($tache) && $tache->exists;
    $publication = $isEdit ? $tache->publication : null;
@endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-list-check"></i> Tâche</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $tache->titre) }}"></div>

                <div class="mb-3"><label class="form-label">Résumé</label>
                    <textarea name="resume" rows="2" class="form-control" maxlength="500" placeholder="Courte description de la tâche">{{ old('resume', $tache->resume) }}</textarea></div>

                <div class="mb-3"><label class="form-label">Description détaillée</label>
                    <div id="tache-editor">{!! old('description', $tache->description) !!}</div>
                    <input type="hidden" name="description" id="tache-description" value="{{ old('description', $tache->description) }}"></div>

                <div class="mb-3"><label class="form-label">Besoins de réalisation</label>
                    <div id="besoins-editor">{!! old('besoins', $tache->besoins) !!}</div>
                    <input type="hidden" name="besoins" id="tache-besoins" value="{{ old('besoins', $tache->besoins) }}">
                    <small class="form-hint">Ressources, compétences, outils nécessaires pour réaliser cette tâche.</small></div>

                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Projet</label>
                        <select name="projet_id" id="select-projet" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($projets as $p)
                                <option value="{{ $p->id }}" @selected(old('projet_id', $tache->projet_id) == $p->id)>{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Phase WBS</label>
                        <select name="phase_id" id="select-phase" class="form-select" @if(! $tache->projet_id) disabled @endif>
                            <option value="">— Aucune —</option>
                            @foreach(($phases ?? []) as $ph)
                                <option value="{{ $ph->id }}" @selected(old('phase_id', $tache->phase_id) == $ph->id)>
                                    @if($ph->code_wbs)[{{ $ph->code_wbs }}] @endif{{ $ph->nom }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">Choisissez d'abord un projet</small>
                    </div>
                    <div class="col-md-4"><label class="form-label">Jalon associé</label>
                        <select name="jalon_id" id="select-jalon" class="form-select" @if(! $tache->projet_id) disabled @endif>
                            <option value="">— Aucun —</option>
                            @foreach(($jalons ?? []) as $j)
                                <option value="{{ $j->id }}" @selected(old('jalon_id', $tache->jalon_id) == $j->id)>{{ $j->titre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="select-responsable" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($utilisateurs as $u)
                                <option value="{{ $u->id }}" @selected(old('responsable_id', $tache->responsable_id) == $u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select></div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-users"></i> Assignés</div>
            <div class="form-card-body">
                <select name="assignes[]" id="select-assignes" multiple placeholder="Assigner des collaborateurs…">
                    @foreach($utilisateurs as $u)
                        <option value="{{ $u->id }}"
                            data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                            data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                            @selected(in_array($u->id, old('assignes', $assignesIds ?? [])))>
                            {{ $u->prenoms }} {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-calendar-days"></i> Planification</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Date début estimée</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $tache->date_debut?->format('Y-m-d')) }}"></div>
                    <div class="col-md-3"><label class="form-label">Date fin estimée</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $tache->date_fin?->format('Y-m-d')) }}"></div>
                    <div class="col-md-3"><label class="form-label">Date début réelle</label>
                        <input type="date" name="date_debut_reelle" class="form-control" value="{{ old('date_debut_reelle', $tache->date_debut_reelle?->format('Y-m-d')) }}"></div>
                    <div class="col-md-3"><label class="form-label">Date fin réelle</label>
                        <input type="date" name="date_fin_reelle" class="form-control" value="{{ old('date_fin_reelle', $tache->date_fin_reelle?->format('Y-m-d')) }}"></div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-4"><label class="form-label">Temps estimé (heures)</label>
                        <input type="number" step="0.5" name="heures_estimees" class="form-control" value="{{ old('heures_estimees', $tache->heures_estimees) }}"></div>
                    <div class="col-md-4"><label class="form-label">Temps réel (heures)</label>
                        <input type="number" step="0.5" name="temps_reel_heures" class="form-control" value="{{ old('temps_reel_heures', $tache->temps_reel_heures) }}"></div>
                    <div class="col-md-4"><label class="form-label">Avancement (%)</label>
                        <input type="range" name="avancement" class="form-range" min="0" max="100" step="5"
                               value="{{ old('avancement', $tache->avancement ?? 0) }}"
                               oninput="document.getElementById('avancementVal').textContent = this.value + '%'">
                        <span id="avancementVal" class="form-hint fw-bold" style="color:#7C3AED;">{{ old('avancement', $tache->avancement ?? 0) }}%</span></div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-coins"></i> Coût & pondération</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Coût d'exécution</label>
                        <input type="number" step="0.01" name="cout_execution" class="form-control" value="{{ old('cout_execution', $tache->cout_execution) }}"></div>
                    <div class="col-md-4"><label class="form-label">Devise</label>
                        <select name="devise_cout" class="form-select">
                            @foreach(['XAF','EUR','USD','XOF'] as $d)
                                <option value="{{ $d }}" @selected(old('devise_cout', $tache->devise_cout ?? 'XAF') === $d)>{{ $d }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Pondération dans le projet (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="ponderation" class="form-control"
                               value="{{ old('ponderation', $tache->ponderation) }}"
                               placeholder="ex: 15.5"></div>
                </div>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $tache, 'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.taches.pieces-jointes.destroy',
            'deleteRouteParam' => 'tache',
        ])
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-sliders"></i> Statut & priorité</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Statut</label>
                    <select name="statut_id" class="form-select">
                        <option value="">—</option>
                        @foreach($statuts as $s)
                            <option value="{{ $s->id }}" @selected(old('statut_id', $tache->statut_id) == $s->id) style="color:{{ $s->couleur }};">{{ $s->libelle }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Priorité</label>
                    <select name="priorite_id" class="form-select">
                        <option value="">—</option>
                        @foreach($priorites as $p)
                            <option value="{{ $p->id }}" @selected(old('priorite_id', $tache->priorite_id) == $p->id) style="color:{{ $p->couleur }};">{{ $p->libelle }}</option>
                        @endforeach
                    </select></div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="est_jalon" value="1" @checked(old('est_jalon', $tache->est_jalon))>
                    <label class="form-check-label">🏁 Tâche jalon</label>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-user-check"></i> Valideurs</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Valideurs (personnes)</label>
                    <select name="valideurs_users[]" id="select-valideurs-users" multiple placeholder="Sélectionner les valideurs…">
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}"
                                data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                                data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                                @selected(in_array($u->id, old('valideurs_users', $validateursUsersIds ?? [])))>
                                {{ $u->prenoms }} {{ $u->name }}
                            </option>
                        @endforeach
                    </select></div>
                <div class="mb-0"><label class="form-label">Groupes valideurs</label>
                    <select name="valideurs_groupes[]" id="select-valideurs-groupes" multiple placeholder="Sélectionner les groupes…">
                        @foreach($groupes as $g)
                            <option value="{{ $g->id }}"
                                data-color="{{ $g->couleur ?? '#7C3AED' }}"
                                @selected(in_array($g->id, old('valideurs_groupes', $validateursGroupesIds ?? [])))>
                                {{ $g->nom }}
                            </option>
                        @endforeach
                    </select></div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer la tâche' }}
            </button>
            <a href="{{ route('intranet.taches.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    ['select-projet', 'select-responsable', 'select-phase', 'select-jalon'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    // Recharger phases & jalons quand on change de projet
    const projEl = document.getElementById('select-projet');
    const phaseEl = document.getElementById('select-phase');
    const jalonEl = document.getElementById('select-jalon');
    if (projEl && phaseEl && jalonEl) {
        projEl.addEventListener('change', async function () {
            const projId = this.value;
            const tsPhase = phaseEl.tomselect;
            const tsJalon = jalonEl.tomselect;

            tsPhase.clear(); tsPhase.clearOptions(); tsPhase.addOption({value:'', text:'— Aucune —'});
            tsJalon.clear(); tsJalon.clearOptions(); tsJalon.addOption({value:'', text:'— Aucun —'});

            if (! projId) {
                tsPhase.disable(); tsJalon.disable();
                tsPhase.refreshOptions(false); tsJalon.refreshOptions(false);
                return;
            }
            tsPhase.enable(); tsJalon.enable();

            try {
                const res = await fetch(`/intranet/taches/projets/${projId}/phases-jalons`);
                const data = await res.json();
                (data.phases || []).forEach(p => tsPhase.addOption({
                    value: p.id, text: (p.code_wbs ? `[${p.code_wbs}] ` : '') + p.nom,
                }));
                (data.jalons || []).forEach(j => tsJalon.addOption({value: j.id, text: j.titre}));
                tsPhase.refreshOptions(false);
                tsJalon.refreshOptions(false);
            } catch (e) {
                console.error('Erreur chargement phases/jalons:', e);
            }
        });
    }

    const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    const colorOf = (s) => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff; return palette[Math.abs(h) % palette.length]; };
    const userOpts = {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: 500, dropdownParent: 'body',
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
            Array.from(this.input.options).forEach(opt => {
                if (this.options[opt.value]) { this.options[opt.value].initials = opt.dataset.initials || ''; this.options[opt.value].subtitle = opt.dataset.subtitle || ''; }
            });
        },
    };

    ['select-assignes', 'select-valideurs-users'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, userOpts);
    });

    const grpEl = document.getElementById('select-valideurs-groupes');
    if (grpEl) new TomSelect(grpEl, {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: 500, dropdownParent: 'body',
        render: {
            option: (d, e) => `<div class="ts-opt"><span class="ts-opt-color" style="background:${e(d.color || '#7C3AED')};"></span><div class="ts-opt-body"><div class="ts-opt-name">${e(d.text)}</div></div></div>`,
            item: (d, e) => `<div class="ts-item-group"><span class="ts-item-dot" style="background:${e(d.color || '#7C3AED')};"></span>${e(d.text)}</div>`,
        },
        onInitialize: function () {
            Array.from(grpEl.options).forEach(opt => { if (this.options[opt.value]) this.options[opt.value].color = opt.dataset.color || '#7C3AED'; });
        },
    });

    // Quill pour description et besoins
    [{ el: 'tache-editor', hidden: 'tache-description', ph: 'Description détaillée…' },
     { el: 'besoins-editor', hidden: 'tache-besoins', ph: 'Ressources, compétences, outils nécessaires…' }].forEach(cfg => {
        const editorEl = document.getElementById(cfg.el);
        const hidden   = document.getElementById(cfg.hidden);
        if (editorEl) {
            const quill = new Quill('#' + cfg.el, {
                theme: 'snow', placeholder: cfg.ph,
                modules: { toolbar: [[{header:[1,2,3,false]}],['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link','blockquote'],['clean']] },
            });
            quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
            const form = editorEl.closest('form');
            if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
        }
    });
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
