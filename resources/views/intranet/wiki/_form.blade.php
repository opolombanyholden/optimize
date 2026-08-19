@php
    $isEdit = isset($article) && $article->exists;
    $publication = $isEdit ? $article->publication : null;
    $tagsString = is_array($article->tags ?? null) ? implode(', ', $article->tags) : '';
@endphp

<div class="form-grid">
    <div class="form-main">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-book-open"></i> Article Wiki</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $article->titre) }}"></div>
                <div class="mb-3"><label class="form-label">Résumé / Extrait</label>
                    <textarea name="extrait" rows="2" class="form-control" maxlength="500">{{ old('extrait', $article->extrait) }}</textarea></div>
                <div class="mb-3"><label class="form-label">Contenu</label>
                    <div id="wiki-editor">{!! old('contenu', $article->contenu) !!}</div>
                    <input type="hidden" name="contenu" id="wiki-contenu" value="{{ old('contenu', $article->contenu) }}">
                    <small class="form-hint">Le temps de lecture est calculé automatiquement.</small></div>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $article, 'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.wiki.pieces-jointes.destroy',
            'deleteRouteParam' => 'wiki',
        ])
    </div>

    <div class="form-side">
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Classement</div>
            <div class="form-card-body">
                <div class="mb-3"><label class="form-label">Catégorie</label>
                    <select name="categorie_id" id="select-categorie" class="form-select">
                        <option value="">— Aucune —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('categorie_id', $article->categorie_id) == $c->id)>{{ $c->nom }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Article parent</label>
                    <select name="parent_id" id="select-parent" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($articles as $a)
                            @if(!$isEdit || $a->id !== $article->id)
                            <option value="{{ $a->id }}" @selected(old('parent_id', $article->parent_id) == $a->id)>{{ $a->titre }}</option>
                            @endif
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control" placeholder="documentation, procédure…" value="{{ old('tags', $tagsString) }}"></div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="is_epingle" value="1" @checked(old('is_epingle', $article->is_epingle))>
                    <label class="form-check-label">Épingler</label>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Publier l\'article' }}
            </button>
            <a href="{{ route('intranet.wiki.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    ['select-categorie', 'select-parent'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    const editorEl = document.getElementById('wiki-editor');
    const hidden   = document.getElementById('wiki-contenu');
    if (editorEl) {
        const quill = new Quill('#wiki-editor', {
            theme: 'snow', placeholder: 'Rédigez votre article…',
            modules: { toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ color: [] }, { background: [] }],
                [{ align: [] }],
                ['link', 'image', 'blockquote', 'code-block'],
                ['clean'],
            ] },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
