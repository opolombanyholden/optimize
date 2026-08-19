{{-- ============================================================
     PARTIAL — Affichage du média principal + pièces jointes
     Variables :
     - $entity (modèle avec media_url + piecesJointes)
     ============================================================ --}}

@if($entity->media_url)
<figure class="contact-detail-card mt-3">
    <h4 class="contact-detail-card-title"><i class="fas fa-image"></i> Média principal</h4>
    @if($entity->media_principal_type === 'image')
        <img src="{{ $entity->media_url }}" alt="" class="entity-media-img lightbox-trigger">
    @elseif($entity->media_principal_type === 'video')
        <video src="{{ $entity->media_url }}" controls class="entity-media-img"></video>
    @else
        <a href="{{ $entity->media_url }}" target="_blank" class="current-media-doc">
            <i class="fas fa-file-lines"></i> Ouvrir le document
        </a>
    @endif
</figure>
@endif

@if($entity->piecesJointes && $entity->piecesJointes->count())
<section class="contact-detail-card mt-3">
    <h4 class="contact-detail-card-title">
        <i class="fas fa-paperclip"></i> Pièces jointes ({{ $entity->piecesJointes->count() }})
    </h4>
    <div class="attachments-grid">
        @foreach($entity->piecesJointes as $pj)
            @if($pj->categorie === 'image')
            <button type="button" class="attachment-card lightbox-trigger"
                    data-src="{{ $pj->url }}" data-name="{{ $pj->nom_original }}">
                <img src="{{ $pj->url }}" alt="" class="attachment-thumb">
                <div class="attachment-info">
                    <div class="attachment-name">{{ $pj->nom_original }}</div>
                    <div class="attachment-size">{{ $pj->taille_humaine }}</div>
                </div>
            </button>
            @else
            <a href="{{ $pj->url }}" target="_blank" class="attachment-card">
                <div class="attachment-icon"><i class="fas {{ $pj->icone }}"></i></div>
                <div class="attachment-info">
                    <div class="attachment-name">{{ $pj->nom_original }}</div>
                    <div class="attachment-size">{{ $pj->taille_humaine }}</div>
                </div>
            </a>
            @endif
        @endforeach
    </div>
</section>
@endif
