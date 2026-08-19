@php
    $isEdit      = isset($evenement) && $evenement->exists;
    $publication = $isEdit ? $evenement->publication : null;
@endphp

<div class="form-grid">

    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-pen-to-square"></i> Informations</div>
            <div class="form-card-body">

                <div class="mb-3">
                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control"
                           value="{{ old('titre', $evenement->titre) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type d'événement <span class="text-danger">*</span></label>
                    <select name="type_evenement_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($types as $t)
                            <option value="{{ $t->id }}" @selected(old('type_evenement_id', $evenement->type_evenement_id) == $t->id)>
                                {{ $t->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Chapeau / Résumé</label>
                    <textarea name="extrait" rows="2" class="form-control" maxlength="500">{{ old('extrait', $evenement->extrait) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description détaillée</label>
                    <div id="evt-editor">{!! old('description', $evenement->description) !!}</div>
                    <input type="hidden" name="description" id="evt-description"
                           value="{{ old('description', $evenement->description) }}">
                </div>
            </div>
        </div>

        {{-- Dates --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-clock"></i> Date & heure</div>
            <div class="form-card-body">

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="journee_entiere" value="1"
                        @checked(old('journee_entiere', $evenement->journee_entiere)) id="journeeEntiere">
                    <label class="form-check-label">Journée entière</label>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="date_debut" class="form-control" required
                               value="{{ old('date_debut', $evenement->date_debut?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="date_fin" class="form-control" required
                               value="{{ old('date_fin', $evenement->date_fin?->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>

                @if(!$isEdit)
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Récurrence</label>
                        <select name="recurrence" class="form-select" id="recurrenceSelect">
                            <option value="aucune">Aucune</option>
                            <option value="quotidienne">Quotidienne</option>
                            <option value="hebdomadaire">Hebdomadaire</option>
                            <option value="mensuelle">Mensuelle</option>
                            <option value="annuelle">Annuelle</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="recurrenceJusquauWrap" style="display:none;">
                        <label class="form-label">Jusqu'au</label>
                        <input type="date" name="recurrence_jusqu_au" class="form-control">
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Lieu / Visio --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-location-dot"></i> Lieu & accès</div>
            <div class="form-card-body">

                <div class="mb-3">
                    <label class="form-label">Lieu</label>
                    <input type="text" name="lieu" class="form-control"
                           placeholder="Salle de réunion, adresse…"
                           value="{{ old('lieu', $evenement->lieu) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lien vers carte (Google Maps, Plan…)</label>
                    <input type="url" name="lieu_url" class="form-control"
                           placeholder="https://…"
                           value="{{ old('lieu_url', $evenement->lieu_url) }}">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="est_visio" value="1"
                        @checked(old('est_visio', $evenement->est_visio)) id="estVisio">
                    <label class="form-check-label">Événement en visioconférence</label>
                </div>

                <div class="mb-0" id="lienVisioWrap">
                    <label class="form-label">Lien visio (Zoom, Meet, Teams…)</label>
                    <input type="url" name="lien_visio" class="form-control"
                           placeholder="https://meet.google.com/…"
                           value="{{ old('lien_visio', $evenement->lien_visio) }}">
                </div>
            </div>
        </div>

        {{-- Média principal --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-image"></i> Bannière</div>
            <div class="form-card-body">
                @if($isEdit && $evenement->media_url)
                    <div class="current-media mb-3">
                        @if($evenement->media_principal_type === 'image')
                            <img src="{{ $evenement->media_url }}" alt="" class="current-media-img">
                        @elseif($evenement->media_principal_type === 'video')
                            <video src="{{ $evenement->media_url }}" controls class="current-media-img"></video>
                        @endif
                    </div>
                @endif
                <input type="file" name="media_principal" class="form-control" accept="image/*,video/*,.pdf">
                <small class="form-hint">Image, vidéo ou PDF. Maximum 50 Mo.</small>
            </div>
        </div>

        {{-- Pièces jointes --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-paperclip"></i> Pièces jointes</div>
            <div class="form-card-body">
                @if($isEdit && $evenement->piecesJointes->count())
                    <div class="pieces-jointes-list mb-3">
                        @foreach($evenement->piecesJointes as $pj)
                        <div class="pj-item">
                            <i class="fas {{ $pj->icone }}"></i>
                            <a href="{{ $pj->url }}" target="_blank" class="pj-name">{{ $pj->nom_original }}</a>
                            <span class="pj-size">{{ $pj->taille_humaine }}</span>
                            <form action="{{ route('intranet.evenements.pieces-jointes.destroy', [$evenement, $pj]) }}"
                                  method="POST" class="pj-delete-form"
                                  onsubmit="return confirm('Supprimer cette pièce jointe ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="pj-delete-btn"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="pieces_jointes[]" class="form-control" multiple>
            </div>
        </div>
    </div>

    <div class="form-side">

        {{-- Publication --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-paper-plane"></i> Publication</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Visibilité</label>
                    <select name="visibilite" class="form-select" id="visibiliteSelect">
                        @php $vis = old('visibilite', $publication->visibilite ?? 'public'); @endphp
                        <option value="public"    @selected($vis === 'public')>🌐 Public</option>
                        <option value="prive"     @selected($vis === 'prive')>🔒 Privé</option>
                        <option value="brouillon" @selected($vis === 'brouillon')>📝 Brouillon</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        @foreach(['prevu'=>'Prévu','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('statut', $evenement->statut ?? 'prevu') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Cibles --}}
        <div class="form-card" id="ciblesCard">
            <div class="form-card-header"><i class="fas fa-bullseye"></i> Cibles (mode privé)</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Utilisateurs ciblés</label>
                    <select name="cibles_users[]" id="select-cibles-users" multiple placeholder="Rechercher…">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}"
                                data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                                data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                                @selected(in_array($u->id, old('cibles_users', $ciblesUsers ?? [])))>
                                {{ $u->prenoms }} {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Groupes ciblés</label>
                    <select name="cibles_groupes[]" id="select-cibles-groupes" multiple placeholder="Rechercher…">
                        @foreach($groupes as $g)
                            <option value="{{ $g->id }}"
                                data-color="{{ $g->couleur ?? '#7C3AED' }}"
                                @selected(in_array($g->id, old('cibles_groupes', $ciblesGroupes ?? [])))>
                                {{ $g->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Participants invités --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-users"></i> Participants invités</div>
            <div class="form-card-body">
                <label class="form-label">Collaborateurs internes</label>
                <select name="participants[]" id="select-participants" multiple placeholder="Inviter des collaborateurs…">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}"
                            data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                            data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                            @selected(in_array($u->id, old('participants', $participantsIds ?? [])))>
                            {{ $u->prenoms }} {{ $u->name }}
                        </option>
                    @endforeach
                </select>

                <label class="form-label mt-3">
                    <i class="fas fa-envelope" style="color:#7C3AED;"></i> Invités externes
                </label>
                <select name="invites_externes[]" id="select-invites-externes" multiple
                        placeholder="Email ou nom d'un contact CRM…">
                    @foreach(old('invites_externes', $invitesExternesEmails ?? []) as $email)
                        <option value="{{ $email }}" selected>{{ $email }}</option>
                    @endforeach
                </select>
                <small class="form-hint">
                    Tapez un email puis <kbd>Entrée</kbd>, ou recherchez un contact existant du CRM.
                </small>
            </div>
        </div>

        {{-- Options --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-sliders"></i> Options</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Capacité maximum</label>
                    <input type="number" name="capacite_max" class="form-control" min="1"
                           placeholder="Illimité"
                           value="{{ old('capacite_max', $evenement->capacite_max) }}">
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="inscription_requise" value="1"
                        @checked(old('inscription_requise', $evenement->inscription_requise))>
                    <label class="form-check-label">Inscription obligatoire</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rappel (minutes avant)</label>
                    <select name="rappel_minutes" class="form-select">
                        <option value="">Aucun</option>
                        @foreach([5,10,15,30,60,120,1440] as $m)
                            <option value="{{ $m }}" @selected(old('rappel_minutes', $evenement->rappel_minutes) == $m)>
                                @if($m < 60) {{ $m }} min avant
                                @elseif($m == 60) 1 heure avant
                                @elseif($m < 1440) {{ $m / 60 }} heures avant
                                @else 1 jour avant @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="likes_actifs" value="1"
                        @checked(old('likes_actifs', $publication->likes_actifs ?? false))>
                    <label class="form-check-label">Autoriser les likes</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="commentaires_actifs" value="1"
                        @checked(old('commentaires_actifs', $publication->commentaires_actifs ?? false))>
                    <label class="form-check-label">Autoriser les commentaires</label>
                </div>
            </div>
        </div>

        {{-- Apparence --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-palette"></i> Apparence</div>
            <div class="form-card-body">
                <label class="form-label">Couleur (override du type)</label>
                <input type="color" name="couleur" class="form-control form-control-color"
                       value="{{ old('couleur', $evenement->couleur ?? '#7C3AED') }}">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'paper-plane' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer l\'événement' }}
            </button>
            <a href="{{ route('intranet.evenements.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Tom Select pour multi-sélecteurs ──
    const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    const colorOf = (s) => {
        let h = 0;
        for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff;
        return palette[Math.abs(h) % palette.length];
    };
    const userRender = {
        option: (data, escape) => {
            const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
            const sub = data.subtitle ? `<div class="ts-opt-sub">${escape(data.subtitle)}</div>` : '';
            return `<div class="ts-opt">
                <span class="ts-opt-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                <div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div>${sub}</div>
            </div>`;
        },
        item: (data, escape) => {
            const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
            return `<div class="ts-item-user">
                <span class="ts-item-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                ${escape(data.text)}
            </div>`;
        },
        no_results: () => '<div class="no-results">Aucun résultat</div>',
    };
    const userOnInit = function () {
        Array.from(this.input.options).forEach(opt => {
            if (this.options[opt.value]) {
                this.options[opt.value].initials = opt.dataset.initials || '';
                this.options[opt.value].subtitle = opt.dataset.subtitle || '';
            }
        });
    };

    ['select-cibles-users', 'select-participants'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            searchField: ['text', 'subtitle'],
            dropdownParent: 'body',
            render: userRender,
            onInitialize: userOnInit,
        });
    });

    // ── Invités externes (email + recherche contacts CRM) ──
    const externesEl = document.getElementById('select-invites-externes');
    if (externesEl) {
        const isEmail = (s) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(s);
        new TomSelect(externesEl, {
            plugins: ['remove_button'],
            persist: false,
            createOnBlur: true,
            create: function (input) {
                if (!isEmail(input)) return false;
                return { value: input, text: input };
            },
            createFilter: isEmail,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text', 'email'],
            maxOptions: 30,
            dropdownParent: 'body',
            load: function (query, callback) {
                if (!query.length) return callback();
                fetch('{{ route("intranet.evenements.search-contacts") }}?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => callback(data))
                    .catch(() => callback());
            },
            render: {
                option: (data, escape) => {
                    if (data.email && data.text && data.text !== data.email) {
                        return `<div class="ts-opt">
                            <span class="ts-opt-avatar" style="background:${colorOf(data.text)};">
                                <i class="fas fa-address-card" style="font-size:.7rem;"></i>
                            </span>
                            <div class="ts-opt-body">
                                <div class="ts-opt-name">${escape(data.text)}</div>
                                <div class="ts-opt-sub">${escape(data.email)} · contact CRM</div>
                            </div>
                        </div>`;
                    }
                    return `<div class="ts-opt">
                        <span class="ts-opt-avatar" style="background:#94A3B8;">
                            <i class="fas fa-envelope" style="font-size:.65rem;"></i>
                        </span>
                        <div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div></div>
                    </div>`;
                },
                option_create: (data, escape) => `<div class="create">
                    <i class="fas fa-plus" style="color:#7C3AED;"></i>
                    Ajouter <strong>${escape(data.input)}</strong>
                </div>`,
                item: (data, escape) => `<div class="ts-item-user">
                    <span class="ts-item-avatar" style="background:#7C3AED;">
                        <i class="fas fa-envelope" style="font-size:.55rem;"></i>
                    </span>
                    ${escape(data.text)}
                </div>`,
                no_results: () => '<div class="no-results">Tapez un email puis Entrée</div>',
            },
        });
    }

    const groupesEl = document.getElementById('select-cibles-groupes');
    if (groupesEl) new TomSelect(groupesEl, {
        plugins: ['remove_button', 'checkbox_options'],
        maxOptions: 500,
        dropdownParent: 'body',
        render: {
            option: (d, e) => `<div class="ts-opt"><span class="ts-opt-color" style="background:${e(d.color || '#7C3AED')};"></span><div class="ts-opt-body"><div class="ts-opt-name">${e(d.text)}</div></div></div>`,
            item: (d, e) => `<div class="ts-item-group"><span class="ts-item-dot" style="background:${e(d.color || '#7C3AED')};"></span>${e(d.text)}</div>`,
            no_results: () => '<div class="no-results">Aucun résultat</div>',
        },
        onInitialize: function () {
            Array.from(groupesEl.options).forEach(opt => {
                if (this.options[opt.value]) this.options[opt.value].color = opt.dataset.color || '#7C3AED';
            });
        },
    });

    // ── Quill ──
    const editorEl = document.getElementById('evt-editor');
    const hidden   = document.getElementById('evt-description');
    if (editorEl) {
        const quill = new Quill('#evt-editor', {
            theme: 'snow',
            placeholder: "Décrivez l'événement…",
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ color: [] }],
                    ['link', 'blockquote'],
                    ['clean'],
                ],
            },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }

    // ── Cibles cachées si non privé ──
    const visSelect  = document.getElementById('visibiliteSelect');
    const ciblesCard = document.getElementById('ciblesCard');
    function update() {
        if (!visSelect || !ciblesCard) return;
        ciblesCard.style.opacity = visSelect.value === 'prive' ? '1' : '.5';
        ciblesCard.style.pointerEvents = visSelect.value === 'prive' ? 'auto' : 'none';
    }
    if (visSelect) { visSelect.addEventListener('change', update); update(); }

    // ── Récurrence : afficher date jusqu'au ──
    const recSelect = document.getElementById('recurrenceSelect');
    const recWrap   = document.getElementById('recurrenceJusquauWrap');
    if (recSelect && recWrap) {
        recSelect.addEventListener('change', () => {
            recWrap.style.display = recSelect.value === 'aucune' ? 'none' : 'block';
        });
    }

    // ── Visio : afficher lien si activé ──
    const visioCb   = document.getElementById('estVisio');
    const visioWrap = document.getElementById('lienVisioWrap');
    if (visioCb && visioWrap) {
        const upd = () => visioWrap.style.display = visioCb.checked ? 'block' : 'none';
        visioCb.addEventListener('change', upd);
        upd();
    }
});
</script>
@endpush
