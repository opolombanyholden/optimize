@extends('layouts.app')
@section('title', $projet->nom . ' — Problèmes')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Problèmes</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'problemes'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-triangle-exclamation"></i></span>
                Problèmes — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Journal des problèmes et incidents du projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalProbleme">
            <i class="fas fa-plus me-2"></i> Nouveau problème
        </button>
    </div>

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #DC2626;">
                <div class="stat-label">Ouverts</div>
                <div class="stat-value" style="color:#DC2626;">{{ $problemes->where('statut', 'ouvert')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #F59E0B;">
                <div class="stat-label">En cours</div>
                <div class="stat-value" style="color:#F59E0B;">{{ $problemes->where('statut', 'en_cours')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #16A34A;">
                <div class="stat-label">Résolus</div>
                <div class="stat-value" style="color:#16A34A;">{{ $problemes->where('statut', 'resolu')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #6B7280;">
                <div class="stat-label">Fermés</div>
                <div class="stat-value" style="color:#6B7280;">{{ $problemes->where('statut', 'ferme')->count() }}</div>
            </div>
        </div>
    </div>

    @if($problemes->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Priorité</th>
                    <th>Impact</th>
                    <th>Responsable</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($problemes as $probleme)
                <tr>
                    <td><strong>{{ $probleme->titre }}</strong>
                        @if($probleme->description)<br><small class="text-muted">{{ Str::limit($probleme->description, 60) }}</small>@endif
                    </td>
                    <td><span class="opp-stage-badge" style="background:{{ match($probleme->priorite) {
                        'critique' => '#DC2626', 'haute' => '#F59E0B', 'moyenne' => '#0891B2', 'basse' => '#16A34A', default => '#6B7280'
                    } }};">{{ ucfirst($probleme->priorite) }}</span></td>
                    <td>{{ $probleme->impact ?? '—' }}</td>
                    <td>{{ $probleme->responsable?->prenoms ?? '—' }}</td>
                    <td><span class="opp-stage-badge" style="background:{{ match($probleme->statut) {
                        'ouvert' => '#DC2626', 'en_cours' => '#F59E0B', 'resolu' => '#16A34A', 'ferme' => '#6B7280', default => '#6B7280'
                    } }};">{{ ucfirst(str_replace('_', ' ', $probleme->statut)) }}</span></td>
                    <td>{{ $probleme->date_identification ? $probleme->date_identification->format('d/m/Y') : ($probleme->created_at ? $probleme->created_at->format('d/m/Y') : '—') }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editProbleme({{ $probleme->id }}, {{ json_encode([
                                    'titre' => $probleme->titre, 'description' => $probleme->description, 'priorite' => $probleme->priorite,
                                    'impact' => $probleme->impact, 'responsable_id' => $probleme->responsable_id, 'statut' => $probleme->statut,
                                    'date_identification' => $probleme->date_identification?->format('Y-m-d'),
                                    'date_resolution' => $probleme->date_resolution?->format('Y-m-d'), 'resolution' => $probleme->resolution
                                ]) }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.problemes.destroy', [$projet, $probleme]) }}" method="POST" onsubmit="return confirm('Supprimer ce problème ?');">
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
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-triangle-exclamation" style="color:#0D9488;"></i></div>
        <h3>Aucun problème enregistré</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalProbleme">
            <i class="fas fa-plus me-2"></i> Signaler un problème
        </button>
    </div>
    @endif
</div>

{{-- Modale problème --}}
<div class="modal fade" id="modalProbleme" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="problemeForm" method="POST" class="modal-content" action="{{ route('projet.problemes.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="problemeMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="problemeModalTitle">Nouveau problème</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="problemeTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="problemeDesc" rows="3" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select name="priorite" id="problemePriorite" class="form-select" required>
                            <option value="basse">Basse</option>
                            <option value="moyenne" selected>Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Impact</label>
                        <input type="text" name="impact" id="problemeImpact" class="form-control" placeholder="Impact sur le projet"></div>
                    <div class="col-md-4"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="problemeResponsable" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($utilisateurs ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Statut</label>
                        <select name="statut" id="problemeStatut" class="form-select">
                            <option value="ouvert">Ouvert</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="ferme">Fermé</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Date identification</label>
                        <input type="date" name="date_identification" id="problemeDateIdent" class="form-control" value="{{ date('Y-m-d') }}"></div>
                    <div class="col-md-4"><label class="form-label">Date résolution</label>
                        <input type="date" name="date_resolution" id="problemeDateResol" class="form-control"></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Résolution</label>
                    <textarea name="resolution" id="problemeResolution" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-triangle-exclamation me-2"></i> <span id="problemeSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editProbleme(id, data) {
    document.getElementById('problemeModalTitle').textContent = 'Modifier le problème';
    document.getElementById('problemeTitre').value = data.titre;
    document.getElementById('problemeDesc').value = data.description || '';
    document.getElementById('problemePriorite').value = data.priorite;
    document.getElementById('problemeImpact').value = data.impact || '';
    document.getElementById('problemeResponsable').value = data.responsable_id || '';
    document.getElementById('problemeStatut').value = data.statut;
    document.getElementById('problemeDateIdent').value = data.date_identification || '';
    document.getElementById('problemeDateResol').value = data.date_resolution || '';
    document.getElementById('problemeResolution').value = data.resolution || '';
    document.getElementById('problemeMethod').value = 'PUT';
    document.getElementById('problemeSubmitText').textContent = 'Enregistrer';
    document.getElementById('problemeForm').action = '/projet/{{ $projet->id }}/problemes/' + id;
    new bootstrap.Modal(document.getElementById('modalProbleme')).show();
}

document.getElementById('modalProbleme')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('problemeModalTitle').textContent = 'Nouveau problème';
    ['problemeTitre','problemeDesc','problemeImpact','problemeDateResol','problemeResolution'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('problemePriorite').value = 'moyenne';
    document.getElementById('problemeResponsable').value = '';
    document.getElementById('problemeStatut').value = 'ouvert';
    document.getElementById('problemeDateIdent').value = new Date().toISOString().split('T')[0];
    document.getElementById('problemeMethod').value = 'POST';
    document.getElementById('problemeSubmitText').textContent = 'Enregistrer';
    document.getElementById('problemeForm').action = '{{ route("projet.problemes.store", $projet) }}';
});
</script>
@endpush
@endsection
