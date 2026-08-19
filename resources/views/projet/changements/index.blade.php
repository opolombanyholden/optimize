@extends('layouts.app')
@section('title', $projet->nom . ' — Changements')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Changements</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'changements'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-code-pull-request"></i></span>
                Demandes de changement — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Gestion des demandes de modification du périmètre projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalChangement">
            <i class="fas fa-plus me-2"></i> Nouvelle demande
        </button>
    </div>

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #6366F1;">
                <div class="stat-label">Soumises</div>
                <div class="stat-value" style="color:#6366F1;">{{ $changements->where('statut', 'soumis')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #F59E0B;">
                <div class="stat-label">En évaluation</div>
                <div class="stat-value" style="color:#F59E0B;">{{ $changements->where('statut', 'en_evaluation')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #16A34A;">
                <div class="stat-label">Approuvées</div>
                <div class="stat-value" style="color:#16A34A;">{{ $changements->where('statut', 'approuve')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #DC2626;">
                <div class="stat-label">Rejetées</div>
                <div class="stat-value" style="color:#DC2626;">{{ $changements->where('statut', 'rejete')->count() }}</div>
            </div>
        </div>
    </div>

    @if($changements->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Type</th>
                    <th>Demandeur</th>
                    <th>Impact coût</th>
                    <th>Impact délai</th>
                    <th>Statut</th>
                    <th>Décision</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($changements as $changement)
                <tr>
                    <td><strong>{{ $changement->titre }}</strong>
                        @if($changement->description)<br><small class="text-muted">{{ Str::limit($changement->description, 60) }}</small>@endif
                    </td>
                    <td><span class="opp-stage-badge" style="background:#0D9488;">{{ ucfirst(str_replace('_', ' ', $changement->type ?? '—')) }}</span></td>
                    <td>{{ $changement->demandeur?->prenoms ?? $changement->demandeur_nom ?? '—' }}</td>
                    <td>
                        @if($changement->impact_cout)
                        <span style="color:{{ $changement->impact_cout > 0 ? '#DC2626' : '#16A34A' }};">
                            {{ $changement->impact_cout > 0 ? '+' : '' }}{{ number_format($changement->impact_cout, 0, ',', ' ') }} {{ $projet->devise }}
                        </span>
                        @else — @endif
                    </td>
                    <td>
                        @if($changement->impact_delai)
                        <span style="color:{{ $changement->impact_delai > 0 ? '#DC2626' : '#16A34A' }};">
                            {{ $changement->impact_delai > 0 ? '+' : '' }}{{ $changement->impact_delai }} j
                        </span>
                        @else — @endif
                    </td>
                    <td><span class="opp-stage-badge" style="background:{{ match($changement->statut) {
                        'soumis' => '#6366F1', 'en_evaluation' => '#F59E0B', 'approuve' => '#16A34A', 'rejete' => '#DC2626', default => '#6B7280'
                    } }};">{{ ucfirst(str_replace('_', ' ', $changement->statut)) }}</span></td>
                    <td>
                        @if(in_array($changement->statut, ['soumis', 'en_evaluation']))
                        <div class="d-flex gap-1">
                            <form action="{{ route('projet.changements.decider', [$projet, $changement]) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="statut" value="approuve">
                                <button type="submit" class="btn btn-sm" style="background:#16A34A;color:#fff;border:none;font-size:.75rem;padding:2px 8px;" onclick="return confirm('Approuver cette demande ?')">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                            </form>
                            <form action="{{ route('projet.changements.decider', [$projet, $changement]) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="statut" value="rejete">
                                <button type="submit" class="btn btn-sm" style="background:#DC2626;color:#fff;border:none;font-size:.75rem;padding:2px 8px;" onclick="return confirm('Rejeter cette demande ?')">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editChangement({{ $changement->id }}, {{ json_encode([
                                    'titre' => $changement->titre, 'description' => $changement->description, 'type' => $changement->type,
                                    'demandeur_id' => $changement->demandeur_id, 'demandeur_nom' => $changement->demandeur_nom,
                                    'impact_cout' => $changement->impact_cout, 'impact_delai' => $changement->impact_delai,
                                    'justification' => $changement->justification, 'statut' => $changement->statut
                                ]) }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.changements.destroy', [$projet, $changement]) }}" method="POST" onsubmit="return confirm('Supprimer cette demande ?');">
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
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-code-pull-request" style="color:#0D9488;"></i></div>
        <h3>Aucune demande de changement</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalChangement">
            <i class="fas fa-plus me-2"></i> Créer une demande
        </button>
    </div>
    @endif
</div>

{{-- Modale changement --}}
<div class="modal fade" id="modalChangement" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="changementForm" method="POST" class="modal-content" action="{{ route('projet.changements.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="changementMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="changementModalTitle">Nouvelle demande de changement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="changementTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="changementDesc" rows="3" class="form-control" required></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Type</label>
                        <select name="type" id="changementType" class="form-select">
                            <option value="perimetre">Périmètre</option>
                            <option value="budget">Budget</option>
                            <option value="delai">Délai</option>
                            <option value="qualite">Qualité</option>
                            <option value="ressource">Ressource</option>
                            <option value="autre">Autre</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Demandeur</label>
                        <select name="demandeur_id" id="changementDemandeur" class="form-select">
                            <option value="">— Choisir —</option>
                            @foreach($utilisateurs ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Ou nom libre</label>
                        <input type="text" name="demandeur_nom" id="changementDemandeurNom" class="form-control" placeholder="Nom du demandeur"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Impact coût ({{ $projet->devise }})</label>
                        <input type="number" step="0.01" name="impact_cout" id="changementCout" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Impact délai (jours)</label>
                        <input type="number" name="impact_delai" id="changementDelai" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Statut</label>
                        <select name="statut" id="changementStatut" class="form-select">
                            <option value="soumis">Soumis</option>
                            <option value="en_evaluation">En évaluation</option>
                            <option value="approuve">Approuvé</option>
                            <option value="rejete">Rejeté</option>
                        </select></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Justification</label>
                    <textarea name="justification" id="changementJustification" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-code-pull-request me-2"></i> <span id="changementSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editChangement(id, data) {
    document.getElementById('changementModalTitle').textContent = 'Modifier la demande';
    document.getElementById('changementTitre').value = data.titre;
    document.getElementById('changementDesc').value = data.description || '';
    document.getElementById('changementType').value = data.type || 'perimetre';
    document.getElementById('changementDemandeur').value = data.demandeur_id || '';
    document.getElementById('changementDemandeurNom').value = data.demandeur_nom || '';
    document.getElementById('changementCout').value = data.impact_cout || '';
    document.getElementById('changementDelai').value = data.impact_delai || '';
    document.getElementById('changementStatut').value = data.statut;
    document.getElementById('changementJustification').value = data.justification || '';
    document.getElementById('changementMethod').value = 'PUT';
    document.getElementById('changementSubmitText').textContent = 'Enregistrer';
    document.getElementById('changementForm').action = '/projet/{{ $projet->id }}/changements/' + id;
    new bootstrap.Modal(document.getElementById('modalChangement')).show();
}

document.getElementById('modalChangement')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('changementModalTitle').textContent = 'Nouvelle demande de changement';
    ['changementTitre','changementDesc','changementDemandeurNom','changementCout','changementDelai','changementJustification'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('changementType').value = 'perimetre';
    document.getElementById('changementDemandeur').value = '';
    document.getElementById('changementStatut').value = 'soumis';
    document.getElementById('changementMethod').value = 'POST';
    document.getElementById('changementSubmitText').textContent = 'Enregistrer';
    document.getElementById('changementForm').action = '{{ route("projet.changements.store", $projet) }}';
});
</script>
@endpush
@endsection
