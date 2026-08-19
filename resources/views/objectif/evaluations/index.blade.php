@extends('layouts.app')
@section('title', 'Évaluations')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item active">Évaluations</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #F59E0B;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#F59E0B,#D97706);"><i class="fas fa-star-half-stroke"></i></span>
                Évaluations
            </h1>
            <p class="page-subtitle">{{ $evaluations->count() }} évaluations · Score moyen : {{ $stats['score_moyen'] }}/100</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtresEval">
                <i class="fas fa-filter me-1"></i> Filtres
                @php
                    $filtresActifs = collect(['q','statut','user_id','evaluateur_id','objectif_id','date_du','date_au','score_min','score_max'])
                        ->filter(fn($k) => request($k) !== null && request($k) !== '')->count();
                @endphp
                @if($filtresActifs > 0)<span class="badge bg-danger ms-1">{{ $filtresActifs }}</span>@endif
            </button>
            <button class="btn btn-intranet" style="background:linear-gradient(135deg,#F59E0B,#D97706);" data-bs-toggle="modal" data-bs-target="#modalEvaluation">
                <i class="fas fa-plus me-2"></i> Nouvelle évaluation
            </button>
        </div>
    </div>

    {{-- Panneau filtres --}}
    <div class="collapse mt-3 mb-3 {{ $filtresActifs > 0 ? 'show' : '' }}" id="filtresEval">
        <div class="contact-detail-card">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Recherche</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Titre, commentaire…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach(['brouillon'=>'Brouillon','finalise'=>'Finalisé','valide'=>'Validé'] as $k => $lib)
                            <option value="{{ $k }}" @selected(request('statut')===$k)>{{ $lib }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Évalué</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Évaluateur</label>
                    <select name="evaluateur_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" @selected(request('evaluateur_id')==$u->id)>{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Objectif</label>
                    <select name="objectif_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($objectifs as $o)<option value="{{ $o->id }}" @selected(request('objectif_id')==$o->id)>{{ \Illuminate\Support\Str::limit($o->titre, 30) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2"><label class="form-label small">Du</label><input type="date" name="date_du" class="form-control form-control-sm" value="{{ request('date_du') }}"></div>
                <div class="col-md-2"><label class="form-label small">Au</label><input type="date" name="date_au" class="form-control form-control-sm" value="{{ request('date_au') }}"></div>
                <div class="col-md-2"><label class="form-label small">Score min</label><input type="number" name="score_min" class="form-control form-control-sm" min="0" max="100" value="{{ request('score_min') }}"></div>
                <div class="col-md-2"><label class="form-label small">Score max</label><input type="number" name="score_max" class="form-control form-control-sm" min="0" max="100" value="{{ request('score_max') }}"></div>
                <div class="col-md-4 d-flex justify-content-end align-items-end gap-2">
                    <a href="{{ route('objectifs.evaluations.index') }}" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i> Réinitialiser</a>
                    <button class="btn btn-sm btn-dark"><i class="fas fa-search me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #F59E0B;"><div class="stat-label">Total</div><div class="stat-value" style="color:#F59E0B;">{{ $stats['total'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #94A3B8;"><div class="stat-label">Brouillons</div><div class="stat-value" style="color:#94A3B8;">{{ $stats['brouillon'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #0891B2;"><div class="stat-label">Finalisées</div><div class="stat-value" style="color:#0891B2;">{{ $stats['finalises'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #16A34A;"><div class="stat-label">Validées</div><div class="stat-value" style="color:#16A34A;">{{ $stats['valides'] }}</div></div></div>
    </div>

    @if($evaluations->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr><th>Titre</th><th>Évalué</th><th>Évaluateur</th><th>Objectif</th><th style="text-align:center;">Score</th><th>Statut</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($evaluations as $e)
                <tr>
                    <td><strong>{{ Str::limit($e->titre, 30) }}</strong></td>
                    <td>{{ $e->utilisateur?->prenoms }} {{ $e->utilisateur?->name }}</td>
                    <td style="font-size:.78rem;color:#64748B;">{{ $e->evaluateur?->prenoms }} {{ $e->evaluateur?->name }}</td>
                    <td>
                        @if($e->objectif)<span style="font-size:.7rem;">{{ Str::limit($e->objectif->titre, 25) }}</span>
                        @else — @endif
                    </td>
                    <td style="text-align:center;">
                        @if($e->score !== null)
                        <span class="opp-stage-badge" style="background:{{ $e->score >= 70 ? '#16A34A' : ($e->score >= 50 ? '#F59E0B' : '#DC2626') }};font-size:.7rem;">{{ $e->score }}/100</span>
                        @else — @endif
                    </td>
                    <td>
                        <span class="opp-stage-badge" style="background:{{ $e->statut === 'valide' ? '#16A34A' : ($e->statut === 'finalise' ? '#0891B2' : '#94A3B8') }};font-size:.6rem;">{{ ucfirst($e->statut) }}</span>
                    </td>
                    <td style="font-size:.75rem;">{{ $e->date_evaluation?->format('d/m/Y') }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick='editEvaluation(@json($e))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('objectifs.evaluations.destroy', $e) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#FFFBEB;"><i class="fas fa-star-half-stroke" style="color:#F59E0B;"></i></div>
        <h3>Aucune évaluation</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#F59E0B,#D97706);" data-bs-toggle="modal" data-bs-target="#modalEvaluation">
            <i class="fas fa-plus me-2"></i> Créer la première
        </button>
    </div>
    @endif
</div>

<div class="modal fade" id="modalEvaluation" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="evalForm" method="POST" class="modal-content" action="{{ route('objectifs.evaluations.store') }}">
            @csrf
            <input type="hidden" name="_method" id="evalMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="evalModalTitle">Nouvelle évaluation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="evalTitre" class="form-control" required placeholder="Évaluation Q1 2026, Atteinte OKR..."></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Évalué <span class="text-danger">*</span></label>
                        <select name="user_id" id="evalUser" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Évaluateur <span class="text-danger">*</span></label>
                        <select name="evaluateur_id" id="evalEvaluateur" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Objectif lié</label>
                        <select name="objectif_id" id="evalObjectif" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($objectifs as $o)<option value="{{ $o->id }}">{{ $o->titre }}</option>@endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Score (/100)</label>
                        <input type="number" name="score" id="evalScore" class="form-control" min="0" max="100"></div>
                    <div class="col-md-3"><label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date_evaluation" id="evalDate" class="form-control" value="{{ now()->toDateString() }}" required></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Points forts</label>
                        <textarea name="points_forts" id="evalPF" rows="3" class="form-control"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Axes d'amélioration</label>
                        <textarea name="axes_amelioration" id="evalAxes" rows="3" class="form-control"></textarea></div>
                </div>
                <div class="mt-3"><label class="form-label">Commentaire général</label>
                    <textarea name="commentaire" id="evalComment" rows="2" class="form-control"></textarea></div>
                <div class="mt-3"><label class="form-label">Statut <span class="text-danger">*</span></label>
                    <select name="statut" id="evalStatut" class="form-select" required>
                        <option value="brouillon">Brouillon</option>
                        <option value="finalise">Finalisée</option>
                        <option value="valide">Validée</option>
                    </select></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#F59E0B,#D97706);"><span id="evalSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editEvaluation(e) {
    document.getElementById('evalModalTitle').textContent = 'Modifier l\'évaluation';
    document.getElementById('evalTitre').value = e.titre || '';
    document.getElementById('evalUser').value = e.user_id || '';
    document.getElementById('evalEvaluateur').value = e.evaluateur_id || '';
    document.getElementById('evalObjectif').value = e.objectif_id || '';
    document.getElementById('evalScore').value = e.score ?? '';
    document.getElementById('evalDate').value = e.date_evaluation?.substring(0, 10) || '';
    document.getElementById('evalPF').value = e.points_forts || '';
    document.getElementById('evalAxes').value = e.axes_amelioration || '';
    document.getElementById('evalComment').value = e.commentaire || '';
    document.getElementById('evalStatut').value = e.statut || 'brouillon';
    document.getElementById('evalMethod').value = 'PUT';
    document.getElementById('evalSubmitText').textContent = 'Enregistrer';
    document.getElementById('evalForm').action = '/objectifs/evaluations/' + e.id;
    new bootstrap.Modal(document.getElementById('modalEvaluation')).show();
}

document.getElementById('modalEvaluation')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('evalModalTitle').textContent = 'Nouvelle évaluation';
    ['evalTitre','evalScore','evalPF','evalAxes','evalComment'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('evalDate').value = '{{ now()->toDateString() }}';
    document.getElementById('evalUser').value = '';
    document.getElementById('evalEvaluateur').value = '';
    document.getElementById('evalObjectif').value = '';
    document.getElementById('evalStatut').value = 'brouillon';
    document.getElementById('evalMethod').value = 'POST';
    document.getElementById('evalSubmitText').textContent = 'Créer';
    document.getElementById('evalForm').action = '{{ route("objectifs.evaluations.store") }}';
});
</script>
@endpush
@endsection
