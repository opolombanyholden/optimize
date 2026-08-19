{{-- ============================================================
     PARTIAL — Média principal + pièces jointes (réutilisable)
     Variables attendues :
     - $entity (Eloquent model avec piecesJointes + media_url)
     - $isEdit (bool)
     - $deleteRouteName (string ex: 'intranet.contacts.pieces-jointes.destroy')
     - $deleteRouteParam (string ex: 'contact')
     ============================================================ --}}

<div class="form-card">
    <div class="form-card-header"><i class="fas fa-image"></i> Média principal</div>
    <div class="form-card-body">
        @if($isEdit && $entity->media_url)
            <div class="current-media mb-3">
                @if($entity->media_principal_type === 'image')
                    <img src="{{ $entity->media_url }}" alt="" class="current-media-img">
                @elseif($entity->media_principal_type === 'video')
                    <video src="{{ $entity->media_url }}" controls class="current-media-img"></video>
                @else
                    <a href="{{ $entity->media_url }}" target="_blank" class="current-media-doc">
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

<div class="form-card">
    <div class="form-card-header"><i class="fas fa-paperclip"></i> Pièces jointes</div>
    <div class="form-card-body">
        @if($isEdit && $entity->piecesJointes->count())
            <div class="pieces-jointes-list mb-3">
                @foreach($entity->piecesJointes as $pj)
                <div class="pj-item">
                    <i class="fas {{ $pj->icone }}"></i>
                    <a href="{{ $pj->url }}" target="_blank" class="pj-name">{{ $pj->nom_original }}</a>
                    <span class="pj-size">{{ $pj->taille_humaine }}</span>
                    <form action="{{ route($deleteRouteName, [$deleteRouteParam => $entity, 'piece' => $pj]) }}"
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
        <small class="form-hint">Plusieurs fichiers possibles. Maximum 50 Mo par fichier.</small>
    </div>
</div>
