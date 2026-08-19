@php
    $isEdit = isset($organisation) && $organisation->exists;
    $tagsString = is_array($organisation->tags ?? null) ? implode(', ', $organisation->tags) : '';
    $publication = $isEdit ? $organisation->publication : null;
    $typeCourant = old('type', $organisation->type ?? 'autre');
    $B2B_TYPES = ['client','fournisseur','investisseur','administration'];
@endphp

<div class="form-grid">
    <div class="form-main">

        {{-- ═════════ TYPE D'ORGANISATION ═════════ --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tag"></i> Type d'organisation</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="orga-type" class="form-select" required>
                            @foreach(\App\Models\Intranet\ContactOrganisation::TYPES as $tk => $tv)
                                <option value="{{ $tk }}" @selected($typeCourant === $tk)>{{ $tv }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Détermine le rôle relationnel : client, fournisseur, administration…</small>
                    </div>
                    <div class="col-md-4 b2b-only" @if(!in_array($typeCourant, $B2B_TYPES)) style="display:none" @endif>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="CLI-0001, FRN-0001…" value="{{ old('code', $organisation->code) }}">
                    </div>
                    <div class="col-md-2 b2b-only" @if(!in_array($typeCourant, $B2B_TYPES)) style="display:none" @endif>
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="1" @selected(old('statut', $organisation->statut ?? 1) == 1)>Actif</option>
                            <option value="0" @selected(old('statut', $organisation->statut ?? 1) == 0)>Inactif</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═════════ DOCUMENTS ATTENDUS (prévisualisation dynamique par type) ═════════ --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-folder-open"></i> Documents à fournir <small class="text-muted fw-normal ms-2">selon le type sélectionné</small></div>
            <div class="form-card-body">
                <ul id="preview-documents" class="list-unstyled mb-0">
                    {{-- Rendu initial pour le type courant (fallback sans JS) --}}
                    @php $previewInit = $previewDocumentsParType[$typeCourant] ?? []; @endphp
                    @forelse($previewInit as $doc)
                        <li class="d-flex align-items-center gap-2 py-1 border-bottom">
                            <i class="fas {{ $doc['icone'] }} text-muted"></i>
                            <span class="flex-grow-1">{{ $doc['libelle'] }}</span>
                            @if($doc['obligatoire'])
                                <span class="badge bg-danger">Obligatoire</span>
                            @else
                                <span class="badge bg-secondary">Facultatif</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-muted small py-2">Aucun document requis pour ce type.</li>
                    @endforelse
                </ul>
                @if($isEdit)
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fas fa-circle-info me-1"></i>
                        Vous pourrez téléverser ces documents sur la <a href="{{ route('intranet.organisations.show', $organisation) }}">fiche de l'organisation</a>.
                    </div>
                @else
                    <div class="alert alert-info small mt-3 mb-0">
                        <i class="fas fa-circle-info me-1"></i>
                        Créez d'abord l'organisation, puis vous pourrez y téléverser les documents.
                    </div>
                @endif
            </div>
        </div>

        {{-- ═════════ INFOS B2B (client / fournisseur / investisseur / administration) ═════════ --}}
        <div class="form-card b2b-only" @if(!in_array($typeCourant, $B2B_TYPES)) style="display:none" @endif>
            <div class="form-card-header"><i class="fas fa-file-invoice"></i> Informations légales & bancaires</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Raison sociale</label>
                        <input type="text" name="raison_sociale" class="form-control" value="{{ old('raison_sociale', $organisation->raison_sociale) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Forme juridique</label>
                        <input type="text" name="forme_juridique" class="form-control" placeholder="SA, SARL, EI…" value="{{ old('forme_juridique', $organisation->forme_juridique) }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">NIF</label>
                        <input type="text" name="nif" class="form-control" value="{{ old('nif', $organisation->nif) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">RCCM</label>
                        <input type="text" name="rccm" class="form-control" value="{{ old('rccm', $organisation->rccm) }}">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">RIB / IBAN</label>
                        <input type="text" name="rib" class="form-control" value="{{ old('rib', $organisation->rib) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Banque</label>
                        <input type="text" name="banque" class="form-control" value="{{ old('banque', $organisation->banque) }}">
                    </div>
                </div>
                <hr class="my-3">
                <h6 class="text-muted"><i class="fas fa-user-tie me-1"></i> Interlocuteur principal</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nom</label>
                        <input type="text" name="contact_principal_nom" class="form-control" value="{{ old('contact_principal_nom', $organisation->contact_principal_nom) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="contact_principal_telephone" class="form-control" value="{{ old('contact_principal_telephone', $organisation->contact_principal_telephone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_principal_email" class="form-control" value="{{ old('contact_principal_email', $organisation->contact_principal_email) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-building"></i> Identité</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Raison sociale <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" required value="{{ old('nom', $organisation->nom) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div id="orga-editor">{!! old('description', $organisation->description) !!}</div>
                    <input type="hidden" name="description" id="orga-description" value="{{ old('description', $organisation->description) }}">
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Secteur d'activité</label>
                        <select name="secteur_id" id="select-secteur" class="form-select">
                            <option value="">— Choisir —</option>
                            @foreach($secteurs as $s)
                                <option value="{{ $s->id }}" @selected(old('secteur_id', $organisation->secteur_id) == $s->id)>{{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Taille</label>
                        <select name="taille" class="form-select">
                            <option value="">—</option>
                            @foreach(['TPE'=>'TPE (< 10)','PME'=>'PME (10-249)','ETI'=>'ETI (250-4999)','GE'=>'GE (5000+)'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('taille', $organisation->taille) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-address-card"></i> Coordonnées</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $organisation->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $organisation->telephone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site web</label>
                        <input type="url" name="site_web" class="form-control" value="{{ old('site_web', $organisation->site_web) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control" value="{{ old('linkedin', $organisation->linkedin) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-location-dot"></i> Adresse</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" rows="2" class="form-control">{{ old('adresse', $organisation->adresse) }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Pays</label>
                        <select name="pays_id" id="select-pays" class="form-select">
                            <option value="">— Choisir —</option>
                            @foreach($pays as $p)
                                <option value="{{ $p->id }}" @selected(old('pays_id', $organisation->pays_id) == $p->id)>
                                    {{ $p->drapeau_emoji }} {{ $p->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="{{ old('ville', $organisation->ville) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Code postal</label>
                        <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal', $organisation->code_postal) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-file-invoice-dollar"></i> Informations légales & financières</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SIRET</label>
                        <input type="text" name="siret" class="form-control" value="{{ old('siret', $organisation->siret) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro de TVA</label>
                        <input type="text" name="numero_tva" class="form-control" value="{{ old('numero_tva', $organisation->numero_tva) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Chiffre d'affaires</label>
                        <input type="number" step="0.01" name="chiffre_affaires" class="form-control" value="{{ old('chiffre_affaires', $organisation->chiffre_affaires) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Effectif</label>
                        <input type="number" name="effectif" class="form-control" value="{{ old('effectif', $organisation->effectif) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-note-sticky"></i> Notes</div>
            <div class="form-card-body">
                <textarea name="notes" rows="4" class="form-control">{{ old('notes', $organisation->notes) }}</textarea>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $organisation,
            'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.organisations.pieces-jointes.destroy',
            'deleteRouteParam' => 'organisation',
        ])
    </div>

    <div class="form-side">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-image"></i> Logo</div>
            <div class="form-card-body text-center">
                @if($isEdit && $organisation->logo_url)
                    <img src="{{ $organisation->logo_url }}" alt="" class="contact-photo-preview mb-3">
                @endif
                <input type="file" name="logo" class="form-control" accept="image/*">
                <small class="form-hint">Max 5 Mo · JPG, PNG</small>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Statut de la relation</div>
            <div class="form-card-body">
                <label class="form-label mb-1">Statut selon le type</label>
                <select name="statut_relation" id="orga-statut-relation" class="form-select mb-3">
                    <option value="">— Aucun —</option>
                    {{-- Options injectées par JS selon le type. On pré-rend celles du type courant pour SSR / fallback. --}}
                    @foreach(\App\Models\Intranet\ContactOrganisation::STATUTS_RELATION[$typeCourant] ?? [] as $sv => $sl)
                        <option value="{{ $sv }}" @selected(old('statut_relation', $organisation->statut_relation) === $sv)>{{ $sl }}</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mb-3">La liste s'adapte au type d'organisation choisi.</small>

                <label class="form-label">Étiquette</label>
                <div class="etiquette-row">
                    @foreach(['hot'=>'🔥 Hot','warm'=>'☀️ Warm','cold'=>'❄️ Cold'] as $k=>$lbl)
                        <label class="etiquette-pill {{ $k }}">
                            <input type="radio" name="etiquette" value="{{ $k }}" @checked(old('etiquette', $organisation->etiquette) === $k)>
                            {{ $lbl }}
                        </label>
                    @endforeach
                    <label class="etiquette-pill aucune">
                        <input type="radio" name="etiquette" value="" @checked(! old('etiquette', $organisation->etiquette))>
                        Aucune
                    </label>
                </div>

                <div class="mt-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           placeholder="grand-compte, partenaire…"
                           value="{{ old('tags', $tagsString) }}">
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer l\'organisation' }}
            </button>
            <a href="{{ route('intranet.organisations.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    // ── Toggle des cartes B2B selon le type sélectionné ──
    const typeSelect = document.getElementById('orga-type');
    const b2bBlocks  = document.querySelectorAll('.b2b-only');
    const B2B_TYPES  = ['client','fournisseur','investisseur','administration'];
    const toggleB2b  = () => {
        const show = B2B_TYPES.includes(typeSelect.value);
        b2bBlocks.forEach(el => el.style.display = show ? '' : 'none');
    };

    // ── Statut de relation dynamique selon le type ──
    const STATUTS_PAR_TYPE = @json(\App\Models\Intranet\ContactOrganisation::STATUTS_RELATION);
    const statutSelect     = document.getElementById('orga-statut-relation');
    const currentStatut    = @json(old('statut_relation', $organisation->statut_relation ?? ''));
    const rebuildStatuts   = () => {
        if (!statutSelect) return;
        const options  = STATUTS_PAR_TYPE[typeSelect.value] || {};
        const previous = statutSelect.value || currentStatut;
        // Remplace le contenu par des nœuds construits programmatiquement (pas d'innerHTML → pas de risque XSS)
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = '— Aucun —';
        statutSelect.replaceChildren(empty);
        Object.entries(options).forEach(([v, lbl]) => {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = lbl;
            if (v === previous) opt.selected = true;
            statutSelect.appendChild(opt);
        });
    };

    // ── Documents attendus dynamiques ──
    const DOCUMENTS_PAR_TYPE = @json($previewDocumentsParType ?? []);
    const previewList = document.getElementById('preview-documents');
    const rebuildPreviewDocs = () => {
        if (!previewList) return;
        const list = DOCUMENTS_PAR_TYPE[typeSelect.value] || [];
        previewList.replaceChildren();
        if (list.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'text-muted small py-2';
            empty.textContent = 'Aucun document requis pour ce type.';
            previewList.appendChild(empty);
            return;
        }
        list.forEach(doc => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-center gap-2 py-1 border-bottom';

            const icon = document.createElement('i');
            icon.className = 'fas ' + doc.icone + ' text-muted';
            li.appendChild(icon);

            const label = document.createElement('span');
            label.className = 'flex-grow-1';
            label.textContent = doc.libelle;
            li.appendChild(label);

            const badge = document.createElement('span');
            badge.className = 'badge bg-' + (doc.obligatoire ? 'danger' : 'secondary');
            badge.textContent = doc.obligatoire ? 'Obligatoire' : 'Facultatif';
            li.appendChild(badge);

            previewList.appendChild(li);
        });
    };

    if (typeSelect) {
        typeSelect.addEventListener('change', () => { toggleB2b(); rebuildStatuts(); rebuildPreviewDocs(); });
        toggleB2b();
    }

    ['select-secteur', 'select-pays'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    const editorEl = document.getElementById('orga-editor');
    const hidden   = document.getElementById('orga-description');
    if (editorEl) {
        const quill = new Quill('#orga-editor', {
            theme: 'snow',
            placeholder: 'Décrivez l\'organisation, son activité, ses spécificités…',
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
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
