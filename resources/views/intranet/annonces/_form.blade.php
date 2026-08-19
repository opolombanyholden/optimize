{{-- ============================================================
     FORMULAIRE PARTAGÉ — Annonces (create + edit)
     ============================================================ --}}

@php
    $isEdit = isset($annonce) && $annonce->exists;
    $publication = $isEdit ? $annonce->publication : null;
@endphp

<div class="form-grid">

    {{-- ───── COLONNE PRINCIPALE ─────────────────────────── --}}
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-pen-to-square"></i> Contenu de l'annonce
            </div>
            <div class="form-card-body">

                <div class="mb-3">
                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control"
                           value="{{ old('title', $annonce->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Courte description (extrait)</label>
                    <textarea name="extrait" rows="2" class="form-control"
                              maxlength="500" placeholder="Résumé visible dans les listes…">{{ old('extrait', $annonce->extrait) }}</textarea>
                    <small class="form-hint">Maximum 500 caractères</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu détaillé <span class="text-danger">*</span></label>
                    <div id="annonce-editor">{!! old('content', $annonce->content) !!}</div>
                    <input type="hidden" name="content" id="annonce-content" value="{{ old('content', $annonce->content) }}">
                </div>
            </div>
        </div>

        {{-- ───── MÉDIA PRINCIPAL ─────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-image"></i> Média principal
            </div>
            <div class="form-card-body">
                @if($isEdit && $annonce->media_url)
                    <div class="current-media mb-3">
                        @if($annonce->media_principal_type === 'image')
                            <img src="{{ $annonce->media_url }}" alt="" class="current-media-img">
                        @elseif($annonce->media_principal_type === 'video')
                            <video src="{{ $annonce->media_url }}" controls class="current-media-img"></video>
                        @else
                            <a href="{{ $annonce->media_url }}" target="_blank" class="current-media-doc">
                                <i class="fas fa-file-lines"></i> Document attaché
                            </a>
                        @endif
                    </div>
                @endif

                <input type="file" name="media_principal" class="form-control"
                       accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                <small class="form-hint">Image, vidéo ou document. Maximum 50 Mo.</small>
            </div>
        </div>

        {{-- ───── PIÈCES JOINTES ─────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-paperclip"></i> Pièces jointes additionnelles
            </div>
            <div class="form-card-body">
                @if($isEdit && $annonce->piecesJointes->count())
                    <div class="pieces-jointes-list mb-3">
                        @foreach($annonce->piecesJointes as $pj)
                        <div class="pj-item">
                            <i class="fas {{ $pj->icone }}"></i>
                            <a href="{{ $pj->url }}" target="_blank" class="pj-name">{{ $pj->nom_original }}</a>
                            <span class="pj-size">{{ $pj->taille_humaine }}</span>
                            <form action="{{ route('intranet.annonces.pieces-jointes.destroy', [$annonce, $pj]) }}"
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
                <small class="form-hint">Vous pouvez ajouter plusieurs fichiers. Maximum 50 Mo par fichier.</small>
            </div>
        </div>

    </div>

    {{-- ───── COLONNE LATÉRALE ───────────────────────────── --}}
    <div class="form-side">

        {{-- Publication --}}
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-paper-plane"></i> Publication
            </div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Visibilité</label>
                    <select name="visibilite" class="form-select" id="visibiliteSelect">
                        @php $vis = old('visibilite', $publication->visibilite ?? 'public'); @endphp
                        <option value="public"    @selected($vis === 'public')>🌐 Public — tout le monde</option>
                        <option value="prive"     @selected($vis === 'prive')>🔒 Privé — cibles définies</option>
                        <option value="brouillon" @selected($vis === 'brouillon')>📝 Brouillon</option>
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col">
                        <label class="form-label">Publier le</label>
                        <input type="date" name="publie_le" class="form-control"
                               value="{{ old('publie_le', $publication?->publie_le?->format('Y-m-d')) }}">
                    </div>
                    <div class="col">
                        <label class="form-label">Expire le</label>
                        <input type="date" name="expire_le" class="form-control"
                               value="{{ old('expire_le', $publication?->expire_le?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Cibles --}}
        <div class="form-card" id="ciblesCard">
            <div class="form-card-header">
                <i class="fas fa-bullseye"></i> Cibles (mode privé)
            </div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Utilisateurs ciblés</label>
                    <select name="cibles_users[]" id="select-users" multiple
                            placeholder="Rechercher un collaborateur…">
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
                    <select name="cibles_groupes[]" id="select-groupes" multiple
                            placeholder="Rechercher un groupe…">
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

        {{-- Options sociales --}}
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-heart"></i> Interactions
            </div>
            <div class="form-card-body">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="likes_actifs" value="1"
                        @checked(old('likes_actifs', $publication->likes_actifs ?? false))>
                    <label class="form-check-label">Autoriser les likes</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="commentaires_actifs" value="1"
                        @checked(old('commentaires_actifs', $publication->commentaires_actifs ?? false))>
                    <label class="form-check-label">Autoriser les commentaires</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="is_urgent" value="1"
                        @checked(old('is_urgent', $annonce->is_urgent))>
                    <label class="form-check-label text-danger fw-semibold">Marquer urgent</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="epingle" value="1"
                        @checked(old('epingle', $annonce->epingle))>
                    <label class="form-check-label">Épingler</label>
                </div>
            </div>
        </div>

        {{-- Métadonnées --}}
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-tags"></i> Classement
            </div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="categorie" class="form-control"
                           list="categories-list" placeholder="RH, IT, Direction…"
                           value="{{ old('categorie', $annonce->categorie) }}">
                    <datalist id="categories-list">
                        <option value="Direction">
                        <option value="RH">
                        <option value="IT">
                        <option value="Finance">
                        <option value="Communication">
                        <option value="Général">
                    </datalist>
                </div>

                <div class="row g-2">
                    <div class="col">
                        <label class="form-label">Date début</label>
                        <input type="date" name="date_debut" class="form-control"
                               value="{{ old('date_debut', $annonce->date_debut?->format('Y-m-d')) }}">
                    </div>
                    <div class="col">
                        <label class="form-label">Date fin</label>
                        <input type="date" name="date_fin" class="form-control"
                               value="{{ old('date_fin', $annonce->date_fin?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Couleur d'accent</label>
                    <input type="color" name="couleur" class="form-control form-control-color"
                           value="{{ old('couleur', $annonce->couleur ?? '#7C3AED') }}">
                </div>
            </div>
        </div>

        {{-- Boutons --}}
        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'paper-plane' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Publier l\'annonce' }}
            </button>
            <a href="{{ route('intranet.annonces.index') }}" class="btn btn-light w-100 mt-2">
                Annuler
            </a>
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
        // ── Tom Select : utilisateurs ──
        const usersEl = document.getElementById('select-users');
        if (usersEl) {
            const palette = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
            const colorOf = (s) => {
                let h = 0;
                for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff;
                return palette[Math.abs(h) % palette.length];
            };
            new TomSelect(usersEl, {
                plugins: ['remove_button', 'checkbox_options'],
                maxOptions: 500,
                searchField: ['text', 'subtitle'],
                dropdownParent: 'body',
                render: {
                    option: function (data, escape) {
                        const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                        const sub = data.subtitle ? `<div class="ts-opt-sub">${escape(data.subtitle)}</div>` : '';
                        return `<div class="ts-opt">
                            <span class="ts-opt-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                            <div class="ts-opt-body">
                                <div class="ts-opt-name">${escape(data.text)}</div>
                                ${sub}
                            </div>
                        </div>`;
                    },
                    item: function (data, escape) {
                        const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                        return `<div class="ts-item-user">
                            <span class="ts-item-avatar" style="background:${colorOf(data.text)};">${escape(initials)}</span>
                            ${escape(data.text)}
                        </div>`;
                    },
                    no_results: function () {
                        return '<div class="no-results">Aucun collaborateur trouvé</div>';
                    },
                },
                onInitialize: function () {
                    // Lire les data-* dans la liste interne
                    Array.from(usersEl.options).forEach(opt => {
                        if (this.options[opt.value]) {
                            this.options[opt.value].initials = opt.dataset.initials || '';
                            this.options[opt.value].subtitle = opt.dataset.subtitle || '';
                        }
                    });
                },
            });
        }

        // ── Tom Select : groupes ──
        const groupesEl = document.getElementById('select-groupes');
        if (groupesEl) {
            new TomSelect(groupesEl, {
                plugins: ['remove_button', 'checkbox_options'],
                maxOptions: 500,
                dropdownParent: 'body',
                render: {
                    option: function (data, escape) {
                        return `<div class="ts-opt">
                            <span class="ts-opt-color" style="background:${escape(data.color || '#7C3AED')};"></span>
                            <div class="ts-opt-body">
                                <div class="ts-opt-name">${escape(data.text)}</div>
                            </div>
                        </div>`;
                    },
                    item: function (data, escape) {
                        return `<div class="ts-item-group">
                            <span class="ts-item-dot" style="background:${escape(data.color || '#7C3AED')};"></span>
                            ${escape(data.text)}
                        </div>`;
                    },
                    no_results: function () {
                        return '<div class="no-results">Aucun groupe trouvé</div>';
                    },
                },
                onInitialize: function () {
                    Array.from(groupesEl.options).forEach(opt => {
                        if (this.options[opt.value]) {
                            this.options[opt.value].color = opt.dataset.color || '#7C3AED';
                        }
                    });
                },
            });
        }

        // ── Quill WYSIWYG ──
        const editorEl = document.getElementById('annonce-editor');
        const hidden   = document.getElementById('annonce-content');
        if (editorEl) {
            const quill = new Quill('#annonce-editor', {
                theme: 'snow',
                placeholder: 'Rédigez votre annonce…',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ color: [] }, { background: [] }],
                        [{ align: [] }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean'],
                    ],
                },
            });
            quill.on('text-change', function () {
                hidden.value = quill.root.innerHTML;
            });
            // Init au submit
            const form = editorEl.closest('form');
            if (form) form.addEventListener('submit', function () {
                hidden.value = quill.root.innerHTML;
            });
        }

        // ── Affichage conditionnel des cibles ──
        const visSelect  = document.getElementById('visibiliteSelect');
        const ciblesCard = document.getElementById('ciblesCard');
        function updateCiblesVisibility() {
            if (!visSelect || !ciblesCard) return;
            ciblesCard.style.opacity = visSelect.value === 'prive' ? '1' : '.5';
            ciblesCard.style.pointerEvents = visSelect.value === 'prive' ? 'auto' : 'none';
        }
        if (visSelect) {
            visSelect.addEventListener('change', updateCiblesVisibility);
            updateCiblesVisibility();
        }
    });
</script>
@endpush
