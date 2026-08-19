@php
    $isEdit = isset($ressource) && $ressource->exists;
    $publication = $isEdit ? $ressource->publication : null;
    $tagsString = is_array($ressource->tags ?? null) ? implode(', ', $ressource->tags) : '';
@endphp

<div class="form-grid">
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header">
                <i class="fas {{ $type === 'dossier' ? 'fa-folder-plus' : 'fa-file-arrow-up' }}"></i>
                {{ $type === 'dossier' ? 'Nouveau dossier' : 'Ajouter un fichier' }}
            </div>
            <div class="form-card-body">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="mb-3">
                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required
                           value="{{ old('titre', $ressource->titre) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div id="ged-editor">{!! old('description', $ressource->description) !!}</div>
                    <input type="hidden" name="description" id="ged-description"
                           value="{{ old('description', $ressource->description) }}">
                </div>

                @if($type === 'fichier')
                <div class="mb-3">
                    <label class="form-label">Fichier {{ $isEdit ? '(remplacer)' : '' }} <span class="text-danger">{{ $isEdit ? '' : '*' }}</span></label>
                    @if($isEdit && $ressource->chemin)
                    <div class="pj-item mb-2">
                        <i class="fas {{ $ressource->icone_affichee }}" style="color:{{ $ressource->couleur_icone }};"></i>
                        <span class="pj-name">{{ $ressource->nom_original }}</span>
                        <span class="pj-size">{{ $ressource->taille_humaine }} · v{{ $ressource->version }}</span>
                    </div>
                    @endif
                    <input type="file" name="fichier" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    <small class="form-hint">Maximum 100 Mo. Tout format accepté.</small>
                </div>
                @else
                {{-- Options visuelles pour dossier --}}
                <div class="mb-3">
                    <label class="form-label">Image d'aperçu du dossier (optionnelle)</label>
                    @if($isEdit && $ressource->chemin)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $ressource->chemin) }}" alt="" style="max-height:120px;border-radius:9px;">
                    </div>
                    @endif
                    <input type="file" name="apercu" class="form-control" accept="image/*">
                    <small class="form-hint">Image JPG/PNG pour personnaliser l'aperçu du dossier.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icône du dossier (optionnelle)</label>
                    <input type="text" name="icone" class="form-control"
                           placeholder="ex: fa-briefcase, fa-heart, fa-code…"
                           value="{{ old('icone', $ressource->icone) }}">
                    <small class="form-hint">Nom d'icône Font Awesome. Par défaut : dossier jaune.</small>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Dossier parent</label>
                    <select name="parent_id" id="select-parent" class="form-select">
                        <option value="">📁 Racine</option>
                        @foreach($dossiers as $d)
                            <option value="{{ $d->id }}" @selected(old('parent_id', $ressource->parent_id) == $d->id)>
                                {{ $d->titre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if($type === 'fichier')
            @include('intranet._partials.media-section', [
                'entity' => $ressource,
                'isEdit' => $isEdit,
                'deleteRouteName' => 'intranet.ressources.pieces-jointes.destroy',
                'deleteRouteParam' => 'ressource',
            ])
        @endif
    </div>

    <div class="form-side">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Classement</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Catégorie</label>
                    <input type="text" name="categorie" class="form-control" list="cat-list"
                           placeholder="RH, Finance, Technique…"
                           value="{{ old('categorie', $ressource->categorie) }}">
                    <datalist id="cat-list">
                        <option value="RH">
                        <option value="Finance">
                        <option value="Technique">
                        <option value="Juridique">
                        <option value="Commercial">
                        <option value="Communication">
                        <option value="Direction">
                        <option value="Qualité">
                    </datalist>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           placeholder="procédure, modèle…"
                           value="{{ old('tags', $tagsString) }}">
                    <small class="form-hint">Séparés par des virgules</small>
                </div>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="acces_restreint" value="1"
                        @checked(old('acces_restreint', $ressource->acces_restreint))>
                    <label class="form-check-label">🔒 Accès restreint</label>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : ($type === 'dossier' ? 'folder-plus' : 'file-arrow-up') }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : ($type === 'dossier' ? 'Créer le dossier' : 'Ajouter le fichier') }}
            </button>
            <a href="{{ route('intranet.ressources.index', ['dossier' => $parent?->id]) }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    const selParent = document.getElementById('select-parent');
    if (selParent) new TomSelect(selParent, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });

    const editorEl = document.getElementById('ged-editor');
    const hidden   = document.getElementById('ged-description');
    if (editorEl) {
        const quill = new Quill('#ged-editor', {
            theme: 'snow',
            placeholder: 'Description du document…',
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
