@php
    $isEdit = isset($template) && $template->exists;
    $publication = $isEdit ? $template->publication : null;
    $tagsString = is_array($template->tags ?? null) ? implode(', ', $template->tags) : '';
    $varsString = is_array($template->variables ?? null) ? implode(', ', $template->variables) : '';
@endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-file-circle-plus"></i> Template</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $template->titre) }}"></div>

                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description', $template->description) }}</textarea></div>

                <div class="mb-3"><label class="form-label">Format</label>
                    <div class="media-source-tabs">
                        <button type="button" class="media-src-tab {{ old('format', $template->format ?? 'html') === 'html' ? 'active' : '' }}" data-target="fmtHtml" onclick="setFormat('html')">
                            <i class="fas fa-code"></i> Éditeur HTML
                        </button>
                        <button type="button" class="media-src-tab {{ old('format', $template->format ?? 'html') === 'fichier' ? 'active' : '' }}" data-target="fmtFichier" onclick="setFormat('fichier')">
                            <i class="fas fa-file-arrow-up"></i> Fichier modèle
                        </button>
                    </div>
                    <input type="hidden" name="format" id="formatInput" value="{{ old('format', $template->format ?? 'html') }}">
                </div>

                {{-- Panel HTML --}}
                <div id="fmtHtml" class="media-src-panel {{ old('format', $template->format ?? 'html') === 'fichier' ? 'd-none' : '' }}">
                    <label class="form-label">Contenu du template</label>
                    <div id="tpl-editor">{!! old('contenu', $template->contenu) !!}</div>
                    <input type="hidden" name="contenu" id="tpl-contenu" value="{{ old('contenu', $template->contenu) }}">
                    <small class="form-hint">Utilisez <code>@{{nom}}</code>, <code>@{{date}}</code>, <code>@{{entreprise}}</code> pour les variables de substitution.</small>
                </div>

                {{-- Panel Fichier --}}
                <div id="fmtFichier" class="media-src-panel {{ old('format', $template->format ?? 'html') === 'html' ? 'd-none' : '' }}">
                    @if($isEdit && $template->fichier_modele)
                    <div class="pj-item mb-3">
                        <i class="fas fa-file-lines" style="color:#7C3AED;"></i>
                        <span class="pj-name">{{ $template->nom_original }}</span>
                        <span class="pj-size">{{ $template->taille_humaine }}</span>
                    </div>
                    @endif
                    <input type="file" name="fichier_modele" class="form-control"
                           accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.pdf,.odt,.ods,.odp">
                    <small class="form-hint">DOCX, XLSX, PPTX, PDF, ODT… Maximum 50 Mo.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Classement</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Catégorie</label>
                    <select name="categorie_id" class="form-select">
                        <option value="">— Aucune —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('categorie_id', $template->categorie_id) == $c->id)>
                                {{ $c->nom }}
                            </option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="contrat, RH…" value="{{ old('tags', $tagsString) }}"></div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-wand-magic-sparkles"></i> Variables</div>
            <div class="form-card-body">
                <label class="form-label">Variables de substitution</label>
                <input type="text" name="variables" class="form-control"
                       placeholder="nom, date, entreprise, montant…"
                       value="{{ old('variables', $varsString) }}">
                <small class="form-hint">Séparées par virgules. Auto-détectées si vide pour le format HTML.</small>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer le template' }}
            </button>
            <a href="{{ route('intranet.templates.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
function setFormat(fmt) {
    document.getElementById('formatInput').value = fmt;
    document.querySelectorAll('.media-src-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.media-src-panel').forEach(p => p.classList.add('d-none'));
    event.target.closest('.media-src-tab').classList.add('active');
    document.getElementById(fmt === 'html' ? 'fmtHtml' : 'fmtFichier').classList.remove('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
    const editorEl = document.getElementById('tpl-editor');
    const hidden   = document.getElementById('tpl-contenu');
    if (editorEl) {
        const quill = new Quill('#tpl-editor', {
            theme: 'snow',
            placeholder: 'Rédigez votre template avec des variables @{{nom}}, @{{date}}…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ align: [] }],
                    [{ color: [] }, { background: [] }],
                    ['link', 'image', 'blockquote'],
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
