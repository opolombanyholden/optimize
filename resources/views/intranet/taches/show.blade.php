@extends('layouts.app')
@section('title', $tache->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    @if($tache->projet)
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $tache->projet) }}">{{ Str::limit($tache->projet->nom, 20) }}</a></li>
    @if($tache->phase)
    <li class="breadcrumb-item"><a href="{{ route('projet.wbs.show', [$tache->projet, $tache->phase]) }}">{{ Str::limit($tache->phase->nom, 20) }}</a></li>
    @endif
    @else
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.taches.index') }}">Tâches</a></li>
    @endif
    <li class="breadcrumb-item active">{{ Str::limit($tache->titre, 35) }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
<article class="contact-detail">

    <div class="contact-detail-header" style="border-top-color: {{ $tache->statut_couleur }};">
        <div class="contact-detail-photo contact-avatar-letters" style="background: {{ $tache->priorite_couleur }}; font-size: 1.5rem;">
            <i class="fas {{ $tache->est_jalon ? 'fa-flag-checkered' : 'fa-list-check' }}"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($tache->statut)<span class="opp-stage-badge" style="background:{{ $tache->statut_couleur }};">{{ $tache->statut->libelle }}</span>@endif
                @if($tache->priorite)<span class="opp-stage-badge" style="background:{{ $tache->priorite_couleur }};">{{ $tache->priorite->libelle }}</span>@endif
                <span class="opp-stage-badge" style="background:{{ $tache->validation_couleur }};">{{ $tache->validation_libelle }}</span>
                @if($tache->estEnRetard())<span class="contact-tag-lg hot">⚠️ En retard</span>@endif
                @if($tache->est_jalon)<span class="badge-soft">🏁 Jalon</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $tache->titre }}</h1>
            @if($tache->resume)<p class="contact-detail-poste">{{ $tache->resume }}</p>@endif
            @if($tache->projet || $tache->objectif)
            <div class="d-flex flex-wrap gap-2 mt-1">
                @if($tache->projet)
                <a href="{{ route('projet.overview', $tache->projet) }}" class="contact-detail-orga" style="text-decoration:none;">
                    <i class="fas fa-diagram-project"></i> {{ $tache->projet->nom }}
                    @if($tache->ponderation) · {{ $tache->ponderation }}% du projet @endif
                </a>
                @if($tache->phase)
                <a href="{{ route('projet.wbs.show', [$tache->projet, $tache->phase]) }}" class="contact-detail-orga" style="text-decoration:none;">
                    <i class="fas fa-sitemap"></i> {{ $tache->phase->nom }}
                </a>
                @endif
                @endif
                @if($tache->objectif)
                <span class="contact-detail-orga" style="color:#7C3AED;">
                    <i class="fas fa-bullseye"></i> {{ $tache->objectif->titre }}
                </span>
                @endif
            </div>
            @endif
        </div>
        <div class="contact-detail-actions">
            @can('update:tache_intranet')
                @if($tache->peutModifier())
                <a href="{{ route('intranet.taches.edit', $tache) }}" class="btn btn-intranet"><i class="fas fa-pen-to-square me-2"></i> Modifier</a>
                @elseif($tache->est_verrouille)
                <button class="btn btn-warning btn-sm" onclick="demanderModification({{ $tache->id }}, 'tache', '{{ addslashes($tache->titre) }}')">
                    <i class="fas fa-lock me-2"></i> Demander modification
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="demanderSuppression({{ $tache->id }}, 'tache', '{{ addslashes($tache->titre) }}')">
                    <i class="fas fa-trash me-1"></i> Suppr.
                </button>
                @endif
            @endcan
        </div>
    </div>

    {{-- Demande en attente --}}
    @if($tache->aDemandeEnAttente())
    <div class="alert alert-warning mt-3 mb-0" style="font-size:.82rem;border-radius:9px;">
        <i class="fas fa-clock me-2"></i> <strong>Demande en attente :</strong> Une demande de modification/suppression est en cours d'examen.
    </div>
    @endif

    {{-- Barre d'avancement --}}
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-chart-line"></i> Avancement</h4>
        <div class="tache-detail-progress">
            <div class="tache-detail-progress-bar">
                <div class="tache-detail-progress-fill" style="width:{{ $tache->avancement }}%;background:{{ $tache->statut_couleur }};"></div>
            </div>
            <span class="tache-detail-progress-pct">{{ $tache->avancement }}%</span>
        </div>
    </div>

    <div class="contact-detail-grid">
        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="far fa-calendar"></i> Planification</h4>
            <ul class="contact-info-list">
                @if($tache->date_debut)<li><strong>Début estimé :</strong> {{ $tache->date_debut->format('d/m/Y') }}</li>@endif
                @if($tache->date_fin)<li class="{{ $tache->estEnRetard() ? 'text-danger' : '' }}"><strong>Fin estimée :</strong> {{ $tache->date_fin->format('d/m/Y') }}</li>@endif
                @if($tache->date_debut_reelle)<li><strong>Début réel :</strong> {{ $tache->date_debut_reelle->format('d/m/Y') }}</li>@endif
                @if($tache->date_fin_reelle)<li><strong>Fin réelle :</strong> {{ $tache->date_fin_reelle->format('d/m/Y') }}</li>@endif
                @if($tache->heures_estimees)<li><strong>Temps estimé :</strong> {{ $tache->heures_estimees }}h</li>@endif
                @if($tache->temps_reel_heures)<li><strong>Temps réel :</strong> {{ $tache->temps_reel_heures }}h</li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-coins"></i> Coût & pondération</h4>
            <ul class="contact-info-list">
                @if($tache->cout_execution)<li><strong>Coût :</strong> {{ number_format($tache->cout_execution, 0, ',', ' ') }} {{ $tache->devise_cout }}</li>@endif
                @if($tache->ponderation)<li><strong>Pondération projet :</strong> {{ $tache->ponderation }}%</li>@endif
            </ul>
        </div>

        <div class="contact-detail-card">
            <h4 class="contact-detail-card-title"><i class="fas fa-user-check"></i> Équipe</h4>
            @if($tache->responsable)
            <div class="contact-mini mb-2">
                <div class="contact-mini-avatar contact-avatar-letters" style="background:#7C3AED;">
                    {{ strtoupper(substr($tache->responsable->prenoms ?? $tache->responsable->name, 0, 1)) }}{{ strtoupper(substr($tache->responsable->name, 0, 1)) }}
                </div>
                <div><div class="contact-mini-name">{{ $tache->responsable->prenoms }} {{ $tache->responsable->name }}</div><div class="contact-mini-poste">Responsable</div></div>
            </div>
            @endif
            @if($tache->assignes->count())
            <div class="contacts-mini-grid">
                @foreach($tache->assignes as $a)
                <div class="contact-mini">
                    <div class="contact-mini-avatar contact-avatar-letters" style="background:{{ ['#059669','#D97706','#0891B2','#0D9488'][$loop->index % 4] }};">
                        {{ strtoupper(substr($a->prenoms ?? $a->name, 0, 1)) }}{{ strtoupper(substr($a->name, 0, 1)) }}
                    </div>
                    <div><div class="contact-mini-name">{{ $a->prenoms }} {{ $a->name }}</div></div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- VALIDATION ──────────────────────────────────── --}}
    <div class="contact-detail-card mt-3" style="border-left: 4px solid {{ $tache->validation_couleur }};">
        <h4 class="contact-detail-card-title"><i class="fas fa-check-double"></i> Validation</h4>

        {{-- Valideurs désignés --}}
        @if($tache->valideurs->count())
        <div class="mb-3">
            <strong class="form-hint">Valideurs désignés :</strong>
            <div class="d-flex flex-wrap gap-2 mt-1">
                @foreach($tache->valideurs as $v)
                <span class="badge-soft">
                    <i class="fas {{ $v->valideur_type === 'user' ? 'fa-user' : 'fa-users' }}"></i>
                    {{ $v->nom_affichage }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <p><strong>Statut :</strong> <span style="color:{{ $tache->validation_couleur }};font-weight:700;">{{ $tache->validation_libelle }}</span></p>

        @if($tache->motif_rejet)
        <div class="pj-item" style="background:#FEE2E2;border-color:#FCA5A5;margin-bottom:.75rem;">
            <i class="fas fa-circle-exclamation text-danger"></i>
            <span class="pj-name text-danger">{{ $tache->motif_rejet }}</span>
        </div>
        @endif

        {{-- Bouton soumettre (pour le responsable/assigné) --}}
        @if(in_array($tache->statut_validation, ['non_soumis', 'rejete', 'revisions'])
            && ($tache->responsable_id === auth()->id() || $tache->assignes->contains('id', auth()->id())))
        <form action="{{ route('intranet.taches.soumettre', $tache) }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="mb-2">
                <label class="form-label">Pièces justificatives (optionnel)</label>
                <input type="file" name="justificatifs[]" class="form-control" multiple>
            </div>
            <div class="mb-2">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="2" placeholder="Commentaire de soumission…"></textarea>
            </div>
            <button type="submit" class="btn btn-intranet btn-sm">
                <i class="fas fa-paper-plane me-2"></i> Soumettre pour validation
            </button>
        </form>
        @endif

        {{-- Actions du valideur --}}
        @if($tache->statut_validation === 'soumis' && $tache->estValideur())
        <div class="courrier-actions mt-3" style="padding:0;border:0;background:transparent;">
            <button type="button" class="btn-action process" data-bs-toggle="modal" data-bs-target="#modalApprouver">
                <i class="fas fa-check"></i> Approuver
            </button>
            <button type="button" class="btn-action reopen" data-bs-toggle="modal" data-bs-target="#modalRevisions">
                <i class="fas fa-pen"></i> Révisions
            </button>
            <button type="button" class="btn-action" style="border-color:#DC2626;color:#DC2626;" data-bs-toggle="modal" data-bs-target="#modalRejeter">
                <i class="fas fa-xmark"></i> Rejeter
            </button>
        </div>
        @endif

        {{-- Bouton évaluer (valideur + tâche approuvée) --}}
        @if($tache->statut_validation === 'approuve' && $tache->estValideur() && ! $tache->note_evaluation)
        <button type="button" class="btn btn-intranet btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalEvaluer">
            <i class="fas fa-star me-2"></i> Évaluer la tâche
        </button>
        @endif
    </div>

    {{-- Évaluation existante --}}
    @if($tache->note_evaluation)
    <div class="contact-detail-card mt-3" style="border-left:4px solid #F59E0B;">
        <h4 class="contact-detail-card-title"><i class="fas fa-star"></i> Évaluation</h4>
        <div class="mb-2">
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star" style="color:{{ $i <= $tache->note_evaluation ? '#F59E0B' : '#E2E8F0' }};font-size:1.2rem;"></i>
            @endfor
            <span class="ms-2 fw-bold">{{ $tache->note_evaluation }}/5</span>
        </div>
        @if($tache->appreciation)<p class="mb-1">{{ $tache->appreciation }}</p>@endif
        <small class="text-muted">Par {{ $tache->evaluateur?->prenoms }} {{ $tache->evaluateur?->name }} · {{ $tache->evalue_le?->translatedFormat('d F Y') }}</small>
    </div>
    @endif

    {{-- Besoins --}}
    @if($tache->besoins)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-tools"></i> Besoins de réalisation</h4>
        <div class="annonce-content p-0 border-0">{!! $tache->besoins !!}</div>
    </div>
    @endif

    {{-- Description --}}
    @if($tache->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <div class="annonce-content p-0 border-0">{!! $tache->description !!}</div>
    </div>
    @endif

    {{-- Checklist --}}
    @if($tache->checklist->count())
    @php $cl = $tache->checklist_progression; @endphp
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-check-square"></i> Checklist ({{ $cl['done'] }}/{{ $cl['total'] }})</h4>
        <div class="tache-checklist">
            @foreach($tache->checklist as $item)
            <div class="tache-checklist-item {{ $item->complete ? 'done' : '' }}">
                <i class="fas {{ $item->complete ? 'fa-check-square' : 'fa-square' }}"></i> {{ $item->titre }}
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Activités --}}
    @if($tache->activites->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-circle-check"></i> Activités ({{ $tache->activites->count() }})</h4>
        <div class="tache-activities">
            @foreach($tache->activites as $act)
            <div class="tache-activity-item">
                <span class="tache-activity-name">{{ $act->titre }}</span>
                <div class="tache-progress-bar" style="width:100px;">
                    <div class="tache-progress-fill" style="width:{{ $act->avancement }}%;background:#7C3AED;"></div>
                </div>
                <span class="tache-progress-pct">{{ $act->avancement }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @include('intranet._partials.media-display', ['entity' => $tache])

    {{-- Historique de la tâche --}}
    @if($tache->historique->count())
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-clock-rotate-left"></i> Historique ({{ $tache->historique->count() }})</h4>
        <div class="interactions-timeline">
            @foreach($tache->historique as $h)
            <div class="interaction-item">
                <div class="interaction-icon" style="background:{{ $h->couleur }};"><i class="fas {{ $h->icone }}"></i></div>
                <div class="interaction-body">
                    <div class="interaction-header">
                        <strong>{{ $h->libelle }}</strong>
                        <span class="text-muted small">{{ $h->created_at->diffForHumans() }}</span>
                    </div>
                    @if($h->commentaire)<div class="interaction-desc">{{ $h->commentaire }}</div>@endif
                    @if($h->note)<div class="interaction-desc">Note : @for($i=1;$i<=5;$i++)<i class="fas fa-star" style="color:{{ $i <= $h->note ? '#F59E0B' : '#E2E8F0' }};"></i>@endfor</div>@endif
                    <div class="interaction-meta">par {{ $h->user->prenoms }} {{ $h->user->name }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($tache->commentairesActifs())
    <section class="annonce-comments contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-comment"></i> Commentaires ({{ $tache->commentaires->count() }})</h4>
        @forelse($tache->commentaires->where('parent_id', null) as $comment)
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

    <footer class="annonce-footer">
        <div class="annonce-footer-actions">
            @if($tache->phase && $tache->projet)
            <a href="{{ route('projet.wbs.show', [$tache->projet, $tache->phase]) }}" class="btn btn-light"><i class="fas fa-sitemap me-2"></i> Phase : {{ $tache->phase->nom }}</a>
            @endif
            @if($tache->projet)
            <a href="{{ route('projet.overview', $tache->projet) }}" class="btn btn-light"><i class="fas fa-diagram-project me-2"></i> {{ $tache->projet->nom }}</a>
            @endif
            <a href="{{ route('intranet.taches.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Toutes les tâches</a>
        </div>
    </footer>

</article>

{{-- Modales de validation --}}
@if($tache->statut_validation === 'soumis' && $tache->estValideur())
<div class="modal fade" id="modalApprouver" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.taches.approuver', $tache) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Approuver la tâche</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><label class="form-label">Commentaire (optionnel)</label><textarea name="commentaire" class="form-control" rows="3"></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#16A34A,#059669);"><i class="fas fa-check me-2"></i> Approuver</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalRevisions" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.taches.revisions', $tache) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Demander des révisions</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><label class="form-label">Commentaire <span class="text-danger">*</span></label><textarea name="commentaire" class="form-control" rows="4" required></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#D97706,#F59E0B);"><i class="fas fa-pen me-2"></i> Envoyer</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalRejeter" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.taches.rejeter', $tache) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Rejeter la tâche</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><label class="form-label">Motif du rejet <span class="text-danger">*</span></label><textarea name="motif" class="form-control" rows="4" required></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-danger-soft"><i class="fas fa-xmark me-2"></i> Rejeter</button></div>
        </form>
    </div>
</div>
@endif

@if($tache->statut_validation === 'approuve' && $tache->estValideur() && ! $tache->note_evaluation)
<div class="modal fade" id="modalEvaluer" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.taches.evaluer', $tache) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Évaluer la tâche</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Note <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        @for($i = 1; $i <= 5; $i++)
                        <label style="cursor:pointer;font-size:1.5rem;">
                            <input type="radio" name="note" value="{{ $i }}" class="d-none" required>
                            <i class="fas fa-star star-rating" data-val="{{ $i }}" style="color:#E2E8F0;transition:color .15s;"></i>
                        </label>
                        @endfor
                    </div>
                </div>
                <div class="mb-0"><label class="form-label">Appréciation / Observations</label><textarea name="appreciation" class="form-control" rows="4" placeholder="Qualité du travail, points forts, axes d'amélioration…"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#D97706,#F59E0B);"><i class="fas fa-star me-2"></i> Évaluer</button></div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.querySelectorAll('.star-rating').forEach(star => {
    star.addEventListener('click', function () {
        const val = parseInt(this.dataset.val);
        document.querySelectorAll('.star-rating').forEach((s, i) => {
            s.style.color = i < val ? '#F59E0B' : '#E2E8F0';
        });
    });
    star.addEventListener('mouseenter', function () {
        const val = parseInt(this.dataset.val);
        document.querySelectorAll('.star-rating').forEach((s, i) => {
            s.style.color = i < val ? '#FBBF24' : '#E2E8F0';
        });
    });
});
document.querySelector('#modalEvaluer .modal-body')?.addEventListener('mouseleave', function () {
    const checked = document.querySelector('input[name="note"]:checked');
    const val = checked ? parseInt(checked.value) : 0;
    document.querySelectorAll('.star-rating').forEach((s, i) => {
        s.style.color = i < val ? '#F59E0B' : '#E2E8F0';
    });
});
</script>
@endpush
@endif

@include('intranet._partials.lightbox')
@include('projet._partials.modales-protection')
</div>
@endsection
