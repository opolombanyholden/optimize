@extends('layouts.app')
@section('title', $courrier->reference . ' — ' . $courrier->objet)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.courriers.index') }}">Courriers</a></li>
    <li class="breadcrumb-item active">{{ $courrier->reference }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail courrier-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $courrier->statut_couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $courrier->statut_couleur }}; font-size: 1.6rem;">
            @if($courrier->type === 'entrant')<i class="fas fa-arrow-down"></i>
            @elseif($courrier->type === 'sortant')<i class="fas fa-arrow-up"></i>
            @else<i class="fas fa-arrows-left-right"></i>@endif
        </div>

        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <code class="opp-ref">{{ $courrier->reference }}</code>
                <span class="opp-stage-badge" style="background:{{ $courrier->statut_couleur }};">{{ $courrier->statut_libelle }}</span>
                @if($courrier->urgent)<span class="contact-tag-lg hot">🔥 URGENT</span>@endif
                @if($courrier->confidentiel)<span class="badge-soft">🔒 Confidentiel</span>@endif
                @if($courrier->est_en_retard)<span class="contact-tag-lg hot">⚠️ En retard</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $courrier->objet }}</h1>
            @if($courrier->extrait)
            <p class="contact-detail-poste">{{ $courrier->extrait }}</p>
            @endif
        </div>

        <div class="contact-detail-actions">
            @can('update:courrier')
            <a href="{{ route('intranet.courriers.edit', $courrier) }}" class="btn btn-intranet">
                <i class="fas fa-pen-to-square me-2"></i> Modifier
            </a>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-arrow-down"></i> Expéditeur</h4>
            <ul class="contact-info-list">
                @if($courrier->expediteur)<li>{{ $courrier->expediteur }}</li>@endif
                @if($courrier->expediteur_email)<li><i class="fas fa-envelope"></i> {{ $courrier->expediteur_email }}</li>@endif
                @if($courrier->expediteur_organisation)<li><i class="fas fa-building"></i> {{ $courrier->expediteur_organisation }}</li>@endif
                @if($courrier->serviceExpediteur)<li><i class="fas fa-sitemap"></i> {{ $courrier->serviceExpediteur->nom }}</li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-arrow-up"></i> Destinataire</h4>
            <ul class="contact-info-list">
                @if($courrier->destinataire)<li>{{ $courrier->destinataire }}</li>@endif
                @if($courrier->destinataire_email)<li><i class="fas fa-envelope"></i> {{ $courrier->destinataire_email }}</li>@endif
                @if($courrier->serviceDestinataire)<li><i class="fas fa-sitemap"></i> {{ $courrier->serviceDestinataire->nom }}</li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Dates</h4>
            <ul class="contact-info-list">
                @if($courrier->date_reception)<li><strong>Reçu le :</strong> {{ $courrier->date_reception->format('d/m/Y') }}</li>@endif
                @if($courrier->date_expedition)<li><strong>Expédié le :</strong> {{ $courrier->date_expedition->format('d/m/Y') }}</li>@endif
                @if($courrier->echeance_traitement)
                <li class="{{ $courrier->est_en_retard ? 'text-danger' : '' }}">
                    <strong>Échéance :</strong> {{ $courrier->echeance_traitement->format('d/m/Y') }}
                </li>
                @endif
                @if($courrier->traite_le)
                <li><strong>Traité le :</strong> {{ $courrier->traite_le->format('d/m/Y H:i') }}</li>
                @endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-user-check"></i> Assignation</h4>
            @if($courrier->assigne)
                <div><strong>{{ $courrier->assigne->prenoms }} {{ $courrier->assigne->name }}</strong></div>
                @if($courrier->assigne_le)
                <small class="text-muted">Assigné le {{ $courrier->assigne_le->translatedFormat('d M Y') }}</small>
                @endif
                @if($courrier->assignePar)
                <div><small class="text-muted">par {{ $courrier->assignePar->prenoms }} {{ $courrier->assignePar->name }}</small></div>
                @endif
            @else
                <p class="text-muted small mb-0">Non assigné</p>
            @endif
        </div>
    </div>

    {{-- ACTIONS WORKFLOW --}}
    <div class="courrier-actions mt-3">

        @can('assign:courrier')
        <button type="button" class="btn-action assign" data-bs-toggle="modal" data-bs-target="#modalAssigner">
            <i class="fas fa-user-plus"></i> {{ $courrier->assigne ? 'Réassigner' : 'Assigner' }}
        </button>
        @endcan

        {{-- Clôture : seul l'initiateur (created_by) ou super-admin --}}
        @php $estInitiateur = auth()->id() === $courrier->created_by; @endphp
        @if($estInitiateur || auth()->user()?->hasRole('super-admin'))
            @if(! $courrier->est_traite)
            <button type="button" class="btn-action process" data-bs-toggle="modal" data-bs-target="#modalTraiter" title="Vous êtes l'initiateur — seul vous pouvez clôturer">
                <i class="fas fa-check-circle"></i> Clôturer le courrier
            </button>
            @else
            <form action="{{ route('intranet.courriers.rouvrir', $courrier) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn-action reopen">
                    <i class="fas fa-rotate-left"></i> Rouvrir
                </button>
            </form>
            @endif
        @elseif(! $courrier->est_traite)
            <span class="badge-soft" style="opacity:.7;" title="Seul l'initiateur peut clôturer ce courrier">
                <i class="fas fa-lock me-1"></i> Clôture réservée à l'initiateur
            </span>
        @endif

        {{-- Ajout de pièces jointes : autorisé pour l'initiateur, l'assigné --}}
        @if($estInitiateur || auth()->id() === $courrier->assigne_a || auth()->user()?->can('update:courrier'))
        <button type="button" class="btn-action" style="border-color:#0891B2;color:#0891B2;" data-bs-toggle="modal" data-bs-target="#modalAjouterPJ">
            <i class="fas fa-paperclip"></i> Ajouter des pièces
        </button>
        @endif

        @can('update:courrier')
        @if($courrier->type === 'sortant' && ! $courrier->accuse_reception)
        <button type="button" class="btn-action ar" data-bs-toggle="modal" data-bs-target="#modalAr">
            <i class="fas fa-check-double"></i> Enregistrer accusé de réception
        </button>
        @endif
        @endcan
    </div>

    {{-- Accusé de réception --}}
    @if($courrier->accuse_reception)
    <div class="contact-detail-card mt-3" style="border-left:4px solid #16A34A;">
        <h4 class="contact-detail-card-title text-success">
            <i class="fas fa-check-double"></i> Accusé de réception
        </h4>
        <ul class="contact-info-list">
            <li><strong>Reçu le :</strong> {{ $courrier->accuse_reception_le->translatedFormat('d F Y à H:i') }}</li>
            <li><strong>Méthode :</strong> {{ ucfirst(str_replace('_', ' ', $courrier->accuse_reception_methode)) }}</li>
            @if($courrier->accuse_reception_scan_url)
            <li>
                <a href="{{ $courrier->accuse_reception_scan_url }}" target="_blank">
                    <i class="fas fa-file-image"></i> Voir le scan de l'accusé
                </a>
            </li>
            @endif
        </ul>
    </div>
    @endif

    {{-- Contenu --}}
    @if($courrier->contenu)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Contenu</h4>
        <div class="annonce-content p-0 border-0">{!! $courrier->contenu !!}</div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $courrier])

    {{-- Notations / commentaires --}}
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title">
            <i class="fas fa-comments"></i> Notations ({{ $courrier->commentaires->count() }})
        </h4>

        @auth
        <form action="{{ route('intranet.courriers.notation', $courrier) }}" method="POST" class="mb-3">
            @csrf
            <textarea name="commentaire" class="form-control mb-2" rows="2"
                      placeholder="Ajouter une notation sur ce courrier…" required></textarea>
            <button type="submit" class="btn btn-intranet btn-sm">
                <i class="fas fa-paper-plane me-2"></i> Publier
            </button>
        </form>
        @endauth

        @if($courrier->commentaires->count())
            @foreach($courrier->commentaires as $comment)
            <div class="comment-item">
                <div class="comment-avatar">
                    {{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}
                </div>
                <div class="comment-body">
                    <div class="comment-header">
                        <strong>{{ $comment->user->prenoms ?? '' }} {{ $comment->user->name }}</strong>
                        <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="comment-text">{{ $comment->contenu }}</div>
                </div>
            </div>
            @endforeach
        @else
            <p class="text-muted small mb-0">Aucune notation pour le moment.</p>
        @endif
    </div>

    {{-- Journal de traitement --}}
    @if($courrier->traitements->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title">
            <i class="fas fa-clock-rotate-left"></i> Historique du traitement
        </h4>
        <div class="interactions-timeline">
            @foreach($courrier->traitements as $t)
            <div class="interaction-item">
                <div class="interaction-icon" style="background:{{ $t->couleur }};">
                    <i class="fas {{ $t->icone }}"></i>
                </div>
                <div class="interaction-body">
                    <div class="interaction-header">
                        <strong>{{ $t->libelle }}</strong>
                        <span class="text-muted small">{{ $t->created_at->diffForHumans() }}</span>
                    </div>
                    @if($t->commentaire)
                    <div class="interaction-desc">{{ $t->commentaire }}</div>
                    @endif
                    <div class="interaction-meta">par {{ $t->user->prenoms }} {{ $t->user->name }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</article>

{{-- ════════════════════════════════════════════════════
     MODALES
════════════════════════════════════════════════════ --}}

@can('assign:courrier')
<div class="modal fade" id="modalAssigner" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.courriers.assigner', $courrier) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ $courrier->assigne ? 'Réassigner' : 'Assigner' }} le courrier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Utilisateur</label>
                    <select name="assigne_a" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected($courrier->assigne_a == $u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Commentaire (optionnel)</label>
                    <textarea name="commentaire" class="form-control" rows="3" placeholder="Instructions de traitement…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-user-check me-2"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('process:courrier')
<div class="modal fade" id="modalTraiter" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.courriers.traiter', $courrier) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Marquer comme traité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label">Compte-rendu de traitement</label>
                    <textarea name="commentaire" class="form-control" rows="4" placeholder="Décrire les actions effectuées…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-check me-2"></i> Marquer traité</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('update:courrier')
<div class="modal fade" id="modalAr" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.courriers.accuser-reception', $courrier) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Enregistrer un accusé de réception</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Méthode de réception</label>
                    <select name="methode" class="form-select" required>
                        <option value="email">Email</option>
                        <option value="courrier">Courrier postal</option>
                        <option value="fax">Fax</option>
                        <option value="en_main_propre">En main propre</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Scan de l'accusé (optionnel)</label>
                    <input type="file" name="scan" class="form-control" accept="image/*,.pdf">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-check-double me-2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endcan

@include('intranet._partials.lightbox')

{{-- Modale : ajouter des pièces jointes (depuis show) --}}
@if(auth()->id() === $courrier->created_by || auth()->id() === $courrier->assigne_a || auth()->user()?->can('update:courrier'))
<div class="modal fade" id="modalAjouterPJ" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.courriers.pieces-jointes.store', $courrier) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#0891B2,#0E7490);">
                <h5 class="modal-title text-white"><i class="fas fa-paperclip me-2"></i> Ajouter des pièces jointes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3" style="font-size:.78rem;">
                    <i class="fas fa-circle-info me-1"></i> Téléchargez un ou plusieurs documents complémentaires (justificatifs, scans, etc.).
                </div>
                <div class="mb-3">
                    <label class="form-label">Fichiers <span class="text-danger">*</span></label>
                    <input type="file" name="pieces_jointes[]" class="form-control" multiple required>
                    <small class="form-hint">50 Mo par fichier · Ctrl+clic pour sélection multiple</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#0891B2;"><i class="fas fa-upload me-2"></i> Téléverser</button>
            </div>
        </form>
    </div>
</div>
@endif

</div>
@endsection
