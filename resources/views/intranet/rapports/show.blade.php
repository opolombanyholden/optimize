@extends('layouts.app')
@section('title', $rapport->reference . ' — ' . $rapport->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.rapports.index') }}">Rapports & CR</a></li>
    <li class="breadcrumb-item active">{{ $rapport->reference }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $rapport->type_couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $rapport->type_couleur }}; font-size: 1.5rem;">
            <i class="fas fa-file-lines"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <code class="opp-ref">{{ $rapport->reference }}</code>
                <span class="opp-stage-badge" style="background:{{ $rapport->type_couleur }};">{{ $rapport->type_libelle }}</span>
                <span class="opp-stage-badge" style="background:{{ $rapport->statut_couleur }};">{{ $rapport->statut_libelle }}</span>
            </div>
            <h1 class="contact-detail-name">{{ $rapport->titre }}</h1>
            @if($rapport->extrait)
            <p class="contact-detail-poste">{{ $rapport->extrait }}</p>
            @endif
        </div>
        <div class="contact-detail-actions">
            <button onclick="window.print()" class="btn btn-light"><i class="fas fa-print me-2"></i> Imprimer</button>
            @can('update:rapport')
            <a href="{{ route('intranet.rapports.edit', $rapport) }}" class="btn btn-intranet"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
            @endcan
            @can('delete:rapport')
            <form action="{{ route('intranet.rapports.destroy', $rapport) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger-soft"><i class="fas fa-trash"></i></button>
            </form>
            @endcan
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-info-circle"></i> Informations</h4>
            <ul class="contact-info-list">
                <li><strong>Type :</strong> {{ $rapport->type_libelle }}</li>
                <li><strong>Date :</strong> {{ $rapport->date_document?->translatedFormat('d F Y') }}</li>
                <li><strong>Auteur :</strong> {{ $rapport->auteur?->prenoms }} {{ $rapport->auteur?->name }}</li>
                <li><strong>Vues :</strong> {{ $rapport->vues_count }}</li>
            </ul>
        </div>

        @if($rapport->evenement || $rapport->projet || $rapport->phase || $rapport->tache || $rapport->activite)
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-link"></i> Liens</h4>
            <ul class="contact-info-list">
                @if($rapport->evenement)
                <li><i class="fas fa-calendar-check" style="color:#0891B2;"></i>
                    <a href="{{ route('intranet.evenements.show', $rapport->evenement) }}">{{ $rapport->evenement->titre }}</a>
                </li>
                @endif
                @if($rapport->projet)
                <li><i class="fas fa-diagram-project" style="color:#7C3AED;"></i>
                    {{ $rapport->projet->nom }}
                </li>
                @endif
                @if($rapport->phase)
                <li><i class="fas fa-sitemap" style="color:#0D9488;"></i>
                    Phase : {{ $rapport->phase->nom }}
                </li>
                @endif
                @if($rapport->tache)
                <li><i class="fas fa-list-check" style="color:#D97706;"></i>
                    Tâche : {{ $rapport->tache->titre }}
                </li>
                @endif
                @if($rapport->activite)
                <li><i class="fas fa-circle-check" style="color:#059669;"></i>
                    Activité : {{ $rapport->activite->titre }}
                </li>
                @endif
            </ul>
        </div>
        @endif

        {{-- Validation N+1 --}}
        @if($rapport->valideur_id)
        <div class="contact-detail-card" style="border-left: 4px solid {{ $rapport->validation_couleur }};">
            <h4 class="contact-detail-card-title"><i class="fas fa-check-double"></i> Validation</h4>
            <ul class="contact-info-list">
                <li><strong>Valideur :</strong> {{ $rapport->valideur?->prenoms }} {{ $rapport->valideur?->name }}</li>
                @if($rapport->soumis_le)
                <li><strong>Soumis le :</strong> {{ $rapport->soumis_le->translatedFormat('d F Y à H:i') }}</li>
                @endif
                <li>
                    <strong>Statut :</strong>
                    <span style="color:{{ $rapport->validation_couleur }};font-weight:700;">
                        {{ $rapport->validation_libelle }}
                    </span>
                </li>
                @if($rapport->commentaire_validation)
                <li><strong>Commentaire :</strong> {{ $rapport->commentaire_validation }}</li>
                @endif
                @if($rapport->valide_le)
                <li><strong>Traité le :</strong> {{ $rapport->valide_le->translatedFormat('d F Y à H:i') }}</li>
                @endif
            </ul>

            {{-- Actions du valideur --}}
            @if($rapport->valideur_id === auth()->id() && $rapport->statut_validation === 'en_attente')
            <div class="courrier-actions mt-3" style="padding:0;border:0;background:transparent;">
                <button type="button" class="btn-action process" data-bs-toggle="modal" data-bs-target="#modalApprouver">
                    <i class="fas fa-check"></i> Approuver
                </button>
                <button type="button" class="btn-action reopen" data-bs-toggle="modal" data-bs-target="#modalRevisions">
                    <i class="fas fa-pen"></i> Demander des révisions
                </button>
                <button type="button" class="btn-action" style="border-color:#DC2626;color:#DC2626;" data-bs-toggle="modal" data-bs-target="#modalRejeter">
                    <i class="fas fa-xmark"></i> Rejeter
                </button>
            </div>
            @endif

            {{-- Bouton soumettre (si pas encore soumis et valideur choisi) --}}
            @if($rapport->created_by === auth()->id() && !$rapport->soumis_le)
            <form action="{{ route('intranet.rapports.soumettre', $rapport) }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="valideur_id" value="{{ $rapport->valideur_id }}">
                <button type="submit" class="btn btn-intranet btn-sm">
                    <i class="fas fa-paper-plane me-2"></i> Soumettre pour validation
                </button>
            </form>
            @endif
        </div>
        @endif

        @if($participantsUsers->count())
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Participants ({{ $participantsUsers->count() }})</h4>
            <div class="contacts-mini-grid">
                @foreach($participantsUsers as $p)
                <div class="contact-mini">
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:{{ ['#7C3AED','#059669','#D97706','#0891B2'][$loop->index % 4] }};">
                        {{ strtoupper(substr($p->prenoms ?? $p->name, 0, 1)) }}{{ strtoupper(substr($p->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="contact-mini-name">{{ $p->prenoms }} {{ $p->name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Contenu du rapport --}}
    @if($rapport->contenu)
    <div class="contact-detail-card mt-3">
        <div class="annonce-content p-0 border-0">{!! $rapport->contenu !!}</div>
    </div>
    @endif

    @if(!empty($rapport->tags))
    <div class="contact-detail-card mt-3">
        <div class="news-detail-tags" style="border:none;padding:0;">
            @foreach($rapport->tags as $t)<span class="news-tag">#{{ $t }}</span>@endforeach
        </div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $rapport])

    @if($rapport->commentairesActifs())
    <section class="annonce-comments contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-comment"></i> Commentaires ({{ $rapport->commentaires->count() }})</h4>
        @forelse($rapport->commentaires->where('parent_id', null) as $comment)
        <div class="comment-item">
            <div class="comment-avatar">{{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}</div>
            <div class="comment-body">
                <div class="comment-header"><strong>{{ $comment->user->prenoms ?? '' }} {{ $comment->user->name }}</strong><span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span></div>
                <div class="comment-text">{{ $comment->contenu }}</div>
            </div>
        </div>
        @empty
        <p class="text-muted small mb-0">Aucun commentaire.</p>
        @endforelse
    </section>
    @endif

    {{-- Modales de validation --}}
    @if($rapport->valideur_id === auth()->id() && $rapport->statut_validation === 'en_attente')
    <div class="modal fade" id="modalApprouver" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('intranet.rapports.approuver', $rapport) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Approuver le rapport</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Commentaire (optionnel)</label>
                    <textarea name="commentaire" class="form-control" rows="3" placeholder="Observations, remarques…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#16A34A,#059669);"><i class="fas fa-check me-2"></i> Approuver</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalRevisions" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('intranet.rapports.revisions', $rapport) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Demander des révisions</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Précisez les modifications demandées <span class="text-danger">*</span></label>
                    <textarea name="commentaire" class="form-control" rows="4" required placeholder="Points à revoir, corrections, compléments…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#D97706,#F59E0B);"><i class="fas fa-pen me-2"></i> Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalRejeter" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('intranet.rapports.rejeter', $rapport) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Rejeter le rapport</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                    <textarea name="commentaire" class="form-control" rows="4" required placeholder="Raison du rejet…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger-soft"><i class="fas fa-xmark me-2"></i> Rejeter</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <footer class="annonce-footer">
        <div class="annonce-footer-author">
            <div class="annonce-author-avatar-lg" style="background: {{ $rapport->type_couleur }};">
                <i class="fas fa-file-lines"></i>
            </div>
            <div>
                <div class="annonce-footer-label">Rédigé par</div>
                <div class="annonce-author-name">{{ $rapport->auteur?->prenoms }} {{ $rapport->auteur?->name }}</div>
                <div class="annonce-author-date"><i class="far fa-clock"></i> {{ $rapport->created_at->translatedFormat('d F Y') }}</div>
            </div>
        </div>
        <div class="annonce-footer-stats">
            <div class="footer-stat"><i class="fas fa-eye"></i><span>{{ $rapport->vues_count }}</span><small>vues</small></div>
        </div>
        <div class="annonce-footer-actions">
            <a href="{{ route('intranet.rapports.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Retour</a>
        </div>
    </footer>

</article>
@include('intranet._partials.lightbox')
</div>
@endsection
