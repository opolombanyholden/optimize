@php
    $isEdit = isset($contact) && $contact->exists;
    $tagsString = is_array($contact->tags ?? null) ? implode(', ', $contact->tags) : '';
    $publication = $isEdit ? $contact->publication : null;
@endphp

<div class="form-grid">
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-user"></i> Identité</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Civilité</label>
                        <select name="civilite" class="form-select">
                            <option value="">—</option>
                            @foreach(['M.','Mme','Mlle','Dr','Pr'] as $c)
                                <option value="{{ $c }}" @selected(old('civilite', $contact->civilite) === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Prénoms</label>
                        <input type="text" name="prenoms" class="form-control" value="{{ old('prenoms', $contact->prenoms) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required value="{{ old('nom', $contact->nom) }}">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Poste</label>
                        <input type="text" name="poste" class="form-control" value="{{ old('poste', $contact->poste) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organisation</label>
                        <select name="organisation_id" id="select-organisation" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($organisations as $o)
                                <option value="{{ $o->id }}" @selected(old('organisation_id', $contact->organisation_id) == $o->id)>{{ $o->nom }}</option>
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
                        <input type="email" name="email" class="form-control" value="{{ old('email', $contact->email) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $contact->telephone) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $contact->mobile) }}">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control" placeholder="https://…" value="{{ old('linkedin', $contact->linkedin) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Twitter / X</label>
                        <input type="text" name="twitter" class="form-control" placeholder="@handle" value="{{ old('twitter', $contact->twitter) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Site web</label>
                        <input type="url" name="site_web" class="form-control" placeholder="https://…" value="{{ old('site_web', $contact->site_web) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-location-dot"></i> Adresse</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" rows="2" class="form-control">{{ old('adresse', $contact->adresse) }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pays</label>
                        <select name="pays_id" id="select-pays" class="form-select">
                            <option value="">— Choisir —</option>
                            @foreach($pays as $p)
                                <option value="{{ $p->id }}"
                                    data-emoji="{{ $p->drapeau_emoji }}"
                                    @selected(old('pays_id', $contact->pays_id) == $p->id)>
                                    {{ $p->drapeau_emoji }} {{ $p->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="{{ old('ville', $contact->ville) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-note-sticky"></i> Notes</div>
            <div class="form-card-body">
                <div id="contact-editor">{!! old('notes', $contact->notes) !!}</div>
                <input type="hidden" name="notes" id="contact-notes" value="{{ old('notes', $contact->notes) }}">
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $contact,
            'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.contacts.pieces-jointes.destroy',
            'deleteRouteParam' => 'contact',
        ])
    </div>

    <div class="form-side">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-image-portrait"></i> Photo</div>
            <div class="form-card-body text-center">
                @if($isEdit && $contact->photo_url)
                    <img src="{{ $contact->photo_url }}" alt="" class="contact-photo-preview mb-3">
                @else
                    <div class="contact-photo-placeholder mb-3">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <input type="file" name="photo" class="form-control" accept="image/*">
                <small class="form-hint">Max 5 Mo · JPG, PNG</small>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Classement</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Étiquette</label>
                    <div class="etiquette-row">
                        @foreach(['hot'=>'🔥 Hot','warm'=>'☀️ Warm','cold'=>'❄️ Cold'] as $k=>$lbl)
                            <label class="etiquette-pill {{ $k }}">
                                <input type="radio" name="etiquette" value="{{ $k }}" @checked(old('etiquette', $contact->etiquette) === $k)>
                                {{ $lbl }}
                            </label>
                        @endforeach
                        <label class="etiquette-pill aucune">
                            <input type="radio" name="etiquette" value="" @checked(! old('etiquette', $contact->etiquette))>
                            Aucune
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <select name="categorie" class="form-select">
                        <option value="">— Aucune —</option>
                        @foreach(['client' => 'Client', 'fournisseur' => 'Fournisseur', 'partenaire' => 'Partenaire', 'sponsor' => 'Sponsor', 'prospect' => 'Prospect', 'autre' => 'Autre'] as $k => $lbl)
                            <option value="{{ $k }}" @selected(old('categorie', $contact->categorie) === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" list="sources-list"
                           placeholder="Salon, Site web, LinkedIn…"
                           value="{{ old('source', $contact->source) }}">
                    <datalist id="sources-list">
                        <option value="Site web">
                        <option value="Salon professionnel">
                        <option value="LinkedIn">
                        <option value="Recommandation">
                        <option value="Email entrant">
                        <option value="Téléphone">
                    </datalist>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           placeholder="VIP, partenaire, technique…"
                           value="{{ old('tags', $tagsString) }}">
                    <small class="form-hint">Séparés par des virgules</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control"
                           value="{{ old('date_naissance', $contact->date_naissance?->format('Y-m-d')) }}">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="est_favori" value="1" @checked(old('est_favori', $contact->est_favori))>
                    <label class="form-check-label">⭐ Favori</label>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer le contact' }}
            </button>
            <a href="{{ route('intranet.contacts.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    ['select-organisation', 'select-pays'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, {
            allowEmptyOption: true,
            maxOptions: 500,
            dropdownParent: 'body',
        });
    });

    const editorEl = document.getElementById('contact-editor');
    const hidden   = document.getElementById('contact-notes');
    if (editorEl) {
        const quill = new Quill('#contact-editor', {
            theme: 'snow',
            placeholder: 'Notes sur ce contact…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
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
