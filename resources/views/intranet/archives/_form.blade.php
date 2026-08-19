@php
    $isEdit = isset($archive) && $archive->exists;
    $publication = $isEdit ? $archive->publication : null;
    $tagsString = is_array($archive->tags ?? null) ? implode(', ', $archive->tags) : '';
@endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-box-archive"></i> Document à archiver</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $archive->titre) }}"></div>

                <div class="mb-3"><label class="form-label">Description</label>
                    <div id="archive-editor">{!! old('description', $archive->description) !!}</div>
                    <input type="hidden" name="description" id="archive-description" value="{{ old('description', $archive->description) }}"></div>

                <div class="mb-3"><label class="form-label">Fichier {{ $isEdit ? '(remplacer)' : '' }}</label>
                    @if($isEdit && $archive->fichier)
                    <div class="pj-item mb-2">
                        <i class="fas {{ $archive->icone }}" style="color:{{ $archive->couleur_icone }};"></i>
                        <span class="pj-name">{{ $archive->nom_original }}</span>
                        <span class="pj-size">{{ $archive->taille_humaine }}</span>
                    </div>
                    @endif
                    <input type="file" name="fichier" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    <small class="form-hint">Maximum 100 Mo.</small>
                </div>

                <div class="mb-3"><label class="form-label">Dossier d'archives</label>
                    <select name="dossier_id" id="select-dossier" class="form-select">
                        <option value="">— Racine —</option>
                        @foreach($dossiers as $d)
                            <option value="{{ $d->id }}" @selected(old('dossier_id', $archive->dossier_id) == $d->id)>{{ $d->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-calendar-days"></i> Dates & conservation</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Date du document</label>
                        <input type="date" name="date_document" class="form-control" value="{{ old('date_document', $archive->date_document?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label">Date d'archivage</label>
                        <input type="date" name="date_archivage" class="form-control" value="{{ old('date_archivage', $archive->date_archivage?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label">Durée conservation (mois)</label>
                        <input type="number" name="duree_conservation_mois" class="form-control" min="1" placeholder="ex: 60" value="{{ old('duree_conservation_mois', $archive->duree_conservation_mois) }}">
                        <small class="form-hint">La date de destruction sera calculée automatiquement.</small></div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6"><label class="form-label">Date de destruction prévue</label>
                        <input type="date" name="date_destruction_prevue" class="form-control" value="{{ old('date_destruction_prevue', $archive->date_destruction_prevue?->format('Y-m-d')) }}"></div>
                    <div class="col-md-6"><label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            @foreach(['actif'=>'Actif','semi_actif'=>'Semi-actif','inactif'=>'Inactif','a_detruire'=>'À détruire'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('statut', $archive->statut ?? 'actif') === $k)>{{ $v }}</option>
                            @endforeach
                        </select></div>
                </div>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $archive, 'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.archives.pieces-jointes.destroy',
            'deleteRouteParam' => 'archive',
        ])
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Métadonnées</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Référence</label>
                    <input type="text" name="reference" class="form-control" placeholder="Auto si vide" value="{{ old('reference', $archive->reference) }}"></div>
                <div class="mb-3"><label class="form-label">Nature</label>
                    <input type="text" name="nature" class="form-control" list="natures-list" placeholder="Contrat, Facture, PV…" value="{{ old('nature', $archive->nature) }}">
                    <datalist id="natures-list">
                        <option value="Contrat"><option value="Facture"><option value="PV"><option value="Rapport"><option value="Correspondance"><option value="Note interne"><option value="Acte"><option value="Plan"><option value="Photo">
                    </datalist></div>
                <div class="mb-3"><label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="juridique, fiscal…" value="{{ old('tags', $tagsString) }}"></div>
                <div class="mb-3"><label class="form-label">Lieu physique (carton, étagère…)</label>
                    <input type="text" name="lieu_physique" class="form-control" value="{{ old('lieu_physique', $archive->lieu_physique) }}"></div>
                <div class="mb-3"><label class="form-label">Code-barre</label>
                    <input type="text" name="code_barre" class="form-control" value="{{ old('code_barre', $archive->code_barre) }}"></div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_confidentiel" value="1" @checked(old('is_confidentiel', $archive->is_confidentiel))>
                    <label class="form-check-label">🔒 Confidentiel</label>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'box-archive' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Archiver' }}
            </button>
            <a href="{{ route('intranet.archives.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    const el = document.getElementById('select-dossier');
    if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });

    const editorEl = document.getElementById('archive-editor');
    const hidden   = document.getElementById('archive-description');
    if (editorEl) {
        const quill = new Quill('#archive-editor', {
            theme: 'snow', placeholder: 'Description du document…',
            modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link'],['clean']] },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
