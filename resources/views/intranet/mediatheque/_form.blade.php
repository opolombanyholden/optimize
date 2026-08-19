@php
    $isEdit = isset($media) && $media->exists;
    $publication = $isEdit ? $media->publication : null;
    $tagsString = is_array($media->tags ?? null) ? implode(', ', $media->tags) : '';
@endphp

<div class="form-grid">
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-photo-film"></i> Média</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $media->titre) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div id="media-editor">{!! old('description', $media->description) !!}</div>
                    <input type="hidden" name="description" id="media-description" value="{{ old('description', $media->description) }}">
                </div>

                {{-- Onglets : Fichier local / URL externe --}}
                <div class="mb-3">
                    <label class="form-label">Source du média</label>
                    <div class="media-source-tabs">
                        <button type="button" class="media-src-tab active" data-target="srcFichier">
                            <i class="fas fa-file-arrow-up"></i> Fichier local
                        </button>
                        <button type="button" class="media-src-tab" data-target="srcUrl">
                            <i class="fas fa-link"></i> URL vidéo externe
                        </button>
                    </div>
                </div>

                {{-- Panel fichier --}}
                <div id="srcFichier" class="media-src-panel">
                    @if($isEdit && $media->fichier_url && !$media->est_externe)
                    <div class="current-media mb-3">
                        @if($media->est_image)
                            <img src="{{ $media->fichier_url }}" alt="" class="current-media-img">
                        @elseif($media->est_video)
                            <video src="{{ $media->fichier_url }}" controls class="current-media-img"></video>
                        @else
                            <div class="pj-item">
                                <i class="fas {{ $media->icone }}" style="color:{{ $media->couleur }};"></i>
                                <span class="pj-name">{{ $media->nom_original }}</span>
                                <span class="pj-size">{{ $media->taille_humaine }}</span>
                            </div>
                        @endif
                    </div>
                    @endif
                    <input type="file" name="fichier" class="form-control">
                    <small class="form-hint">Image, vidéo, audio ou document. Maximum 100 Mo.</small>
                </div>

                {{-- Panel URL externe --}}
                <div id="srcUrl" class="media-src-panel d-none">
                    @if($isEdit && $media->est_externe)
                    <div class="pj-item mb-3">
                        <i class="fab fa-{{ $media->plateforme === 'youtube' ? 'youtube' : ($media->plateforme === 'dailymotion' ? 'dailymotion' : ($media->plateforme === 'vimeo' ? 'vimeo' : 'globe')) }}" style="color:{{ $media->couleur }};"></i>
                        <span class="pj-name">{{ $media->url_externe }}</span>
                        <span class="pj-size">{{ ucfirst($media->plateforme) }}</span>
                    </div>
                    @endif
                    <input type="url" name="url_externe" class="form-control"
                           placeholder="https://www.youtube.com/watch?v=… / https://dai.ly/… / https://vimeo.com/…"
                           value="{{ old('url_externe', $media->url_externe) }}">
                    <small class="form-hint">
                        Plateformes supportées : <strong>YouTube</strong>, <strong>Dailymotion</strong>, <strong>Vimeo</strong>.
                        La miniature sera récupérée automatiquement.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Classement</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Album / Dossier</label>
                    <select name="album_id" id="select-album" class="form-select">
                        <option value="">— Racine (sans album) —</option>
                        @foreach($albums as $a)
                            <option value="{{ $a->id }}" @selected(old('album_id', $media->album_id) == $a->id)>
                                {{ $a->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           placeholder="séminaire, logo…"
                           value="{{ old('tags', $tagsString) }}">
                    <small class="form-hint">Séparés par des virgules</small>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'cloud-arrow-up' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Ajouter le média' }}
            </button>
            <a href="{{ route('intranet.mediatheque.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    // ── Onglets source (Fichier / URL) ──
    document.querySelectorAll('.media-src-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.media-src-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.media-src-panel').forEach(p => p.classList.add('d-none'));
            this.classList.add('active');
            document.getElementById(this.dataset.target)?.classList.remove('d-none');
        });
    });

    // Si édition d'un média externe, activer l'onglet URL
    @if($isEdit && $media->est_externe)
    document.querySelector('.media-src-tab[data-target="srcUrl"]')?.click();
    @endif

    const editorEl = document.getElementById('media-editor');
    const hidden   = document.getElementById('media-description');
    if (editorEl) {
        const quill = new Quill('#media-editor', {
            theme: 'snow',
            placeholder: 'Description du média…',
            modules: { toolbar: [['bold','italic','underline'],['link'],['clean']] },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
