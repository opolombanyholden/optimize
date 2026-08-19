@php $isEdit = isset($projet) && $projet->exists; @endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-diagram-project"></i> Projet</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nom du projet <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required value="{{ old('nom', $projet->nom) }}"></div>
                    <div class="col-md-4"><label class="form-label">Code projet</label>
                        <input type="text" name="code_projet" class="form-control" placeholder="ex: PRJ-001" value="{{ old('code_projet', $projet->code_projet) }}"></div>
                </div>

                <div class="mt-3 mb-3"><label class="form-label">Description</label>
                    <div id="projet-editor">{!! old('description', $projet->description) !!}</div>
                    <input type="hidden" name="description" id="projet-description" value="{{ old('description', $projet->description) }}"></div>

                <div class="mb-3"><label class="form-label">Objectifs</label>
                    <div id="objectifs-editor">{!! old('objectifs', $projet->objectifs) !!}</div>
                    <input type="hidden" name="objectifs" id="projet-objectifs" value="{{ old('objectifs', $projet->objectifs) }}"></div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-calendar-days"></i> Planification</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Date début</label>
                        <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $projet->date_debut?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label">Date fin</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $projet->date_fin?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label">Budget approuvé</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="budget_approuve" class="form-control" value="{{ old('budget_approuve', $projet->budget_approuve) }}">
                            <select name="devise" class="form-select" style="max-width:80px;">
                                @foreach(['XAF','EUR','USD'] as $d)
                                    <option value="{{ $d }}" @selected(old('devise', $projet->devise ?? 'XAF') === $d)>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div></div>
                </div>
                @if($isEdit)
                <div class="mt-3"><label class="form-label">Avancement global (%)</label>
                    <input type="range" name="avancement" class="form-range" min="0" max="100" step="5"
                           value="{{ old('avancement', $projet->avancement ?? 0) }}"
                           oninput="document.getElementById('avVal').textContent = this.value + '%'">
                    <span id="avVal" class="form-hint fw-bold" style="color:#7C3AED;">{{ old('avancement', $projet->avancement ?? 0) }}%</span></div>
                @endif
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-users"></i> Membres du projet</div>
            <div class="form-card-body">
                <select name="membres[]" id="select-membres" multiple placeholder="Ajouter des membres…">
                    @foreach($utilisateurs as $u)
                        <option value="{{ $u->id }}"
                            data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                            data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                            @selected(in_array($u->id, old('membres', $membresIds ?? [])))>
                            {{ $u->prenoms }} {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-sliders"></i> Paramètres</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Statut</label>
                    <select name="statut_id" class="form-select">
                        <option value="">—</option>
                        @foreach($statuts as $s)
                            <option value="{{ $s->id }}" @selected(old('statut_id', $projet->statut_id) == $s->id) style="color:{{ $s->couleur }};">{{ $s->libelle }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Priorité</label>
                    <select name="priorite_id" class="form-select">
                        <option value="">—</option>
                        @foreach($priorites as $p)
                            <option value="{{ $p->id }}" @selected(old('priorite_id', $projet->priorite_id) == $p->id) style="color:{{ $p->couleur }};">{{ $p->libelle }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Catégorie</label>
                    <input type="text" name="categorie" class="form-control" list="cat-list" placeholder="IT, RH, Marketing…" value="{{ old('categorie', $projet->categorie) }}">
                    <datalist id="cat-list"><option value="IT"><option value="RH"><option value="Marketing"><option value="Finance"><option value="Direction"><option value="Opérations"></datalist></div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-user-tie"></i> Direction</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Chef de projet</label>
                    <select name="chef_projet_id" id="select-chef" class="form-select">
                        <option value="">—</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected(old('chef_projet_id', $projet->chef_projet_id) == $u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-0"><label class="form-label">Sponsor</label>
                    <select name="sponsor_id" id="select-sponsor" class="form-select">
                        <option value="">—</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected(old('sponsor_id', $projet->sponsor_id) == $u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
                        @endforeach
                    </select></div>
            </div>
        </div>

        {{-- Liens CRM & Stratégie --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-link"></i> Liens stratégiques</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Objectif stratégique</label>
                    <select name="objectif_id" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach(($objectifs ?? []) as $o)
                            <option value="{{ $o->id }}" @selected(old('objectif_id', $projet->objectif_id) == $o->id)>{{ $o->titre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Organisation cliente</label>
                    <select name="organisation_id" class="form-select">
                        <option value="">—</option>
                        @foreach(($organisations ?? []) as $o)
                            <option value="{{ $o->id }}" @selected(old('organisation_id', $projet->organisation_id) == $o->id)>{{ $o->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact principal</label>
                    <select name="contact_id" class="form-select">
                        <option value="">—</option>
                        @foreach(($contacts ?? []) as $c)
                            <option value="{{ $c->id }}" @selected(old('contact_id', $projet->contact_id) == $c->id)>{{ $c->prenoms }} {{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Opportunité liée</label>
                    <select name="opportunite_id" class="form-select">
                        <option value="">—</option>
                        @foreach(($opportunites ?? []) as $opp)
                            <option value="{{ $opp->id }}" @selected(old('opportunite_id', $projet->opportunite_id) == $opp->id)>{{ $opp->titre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Pièces jointes --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-paperclip"></i> Pièces jointes</div>
            <div class="form-card-body">
                <input type="file" name="pieces_jointes[]" class="form-control" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                <small class="form-hint mt-1 d-block">Images, vidéos, documents — max 50 Mo par fichier</small>

                @if($isEdit && $projet->piecesJointes && $projet->piecesJointes->count())
                <div class="mt-3" style="font-size:.8rem;">
                    <strong>Pièces existantes :</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($projet->piecesJointes as $pj)
                        <li><a href="{{ $pj->url }}" target="_blank"><i class="fas {{ $pj->icone }} me-1"></i>{{ $pj->nom_original }}</a> · {{ $pj->taille_humaine }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer le projet' }}
            </button>
            <a href="{{ route('intranet.projets.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    ['select-chef', 'select-sponsor'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    const colorOf = (s) => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff; return palette[Math.abs(h) % palette.length]; };
    const membresEl = document.getElementById('select-membres');
    if (membresEl) new TomSelect(membresEl, {
        plugins: ['remove_button', 'checkbox_options'], maxOptions: 500, dropdownParent: 'body',
        render: {
            option: (data, escape) => {
                const ini = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                const sub = data.subtitle ? `<div class="ts-opt-sub">${escape(data.subtitle)}</div>` : '';
                return `<div class="ts-opt"><span class="ts-opt-avatar" style="background:${colorOf(data.text)};">${escape(ini)}</span><div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div>${sub}</div></div>`;
            },
            item: (data, escape) => `<div class="ts-item-user"><span class="ts-item-avatar" style="background:${colorOf(data.text)};">${escape(data.initials || (data.text||'?').substring(0,2).toUpperCase())}</span>${escape(data.text)}</div>`,
        },
        onInitialize: function () { Array.from(membresEl.options).forEach(opt => { if (this.options[opt.value]) { this.options[opt.value].initials = opt.dataset.initials || ''; this.options[opt.value].subtitle = opt.dataset.subtitle || ''; } }); },
    });

    [{ el: 'projet-editor', hidden: 'projet-description', ph: 'Description du projet…' },
     { el: 'objectifs-editor', hidden: 'projet-objectifs', ph: 'Objectifs du projet…' }].forEach(cfg => {
        const editorEl = document.getElementById(cfg.el);
        const hidden = document.getElementById(cfg.hidden);
        if (editorEl) {
            const quill = new Quill('#' + cfg.el, { theme: 'snow', placeholder: cfg.ph,
                modules: { toolbar: [[{header:[1,2,3,false]}],['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link','blockquote'],['clean']] } });
            quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
            const form = editorEl.closest('form');
            if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
        }
    });
});
</script>
@endpush
