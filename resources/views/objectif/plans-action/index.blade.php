@extends('layouts.app')
@section('title', 'Plans d\'action')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item active">Plans d'action</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #6366F1;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#6366F1,#4F46E5);"><i class="fas fa-list-check"></i></span>
                Plans d'action
            </h1>
            <p class="page-subtitle">{{ $stats['total'] }} actions · Mise en œuvre opérationnelle des objectifs</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#6366F1,#4F46E5);" data-bs-toggle="modal" data-bs-target="#modalPlan">
            <i class="fas fa-plus me-2"></i> Nouveau plan
        </button>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #6366F1;"><div class="stat-label">Total</div><div class="stat-value" style="color:#6366F1;">{{ $stats['total'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #0891B2;"><div class="stat-label">Actifs</div><div class="stat-value" style="color:#0891B2;">{{ $stats['actifs'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #16A34A;"><div class="stat-label">Réalisés</div><div class="stat-value" style="color:#16A34A;">{{ $stats['realises'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #DC2626;"><div class="stat-label">En retard</div><div class="stat-value" style="color:#DC2626;">{{ $stats['retard'] }}</div></div></div>
    </div>

    {{-- Filtre statut --}}
    <div class="d-flex gap-2 mb-3">
        <a href="{{ route('objectifs.plans-action.index') }}" class="btn btn-sm {{ ! $statut ? 'btn-dark' : 'btn-light' }}">Tous</a>
        @foreach(['planifie' => 'Planifiés', 'en_cours' => 'En cours', 'realise' => 'Réalisés', 'reporte' => 'Reportés'] as $key => $libelle)
        <a href="{{ route('objectifs.plans-action.index', ['statut' => $key]) }}" class="btn btn-sm {{ $statut === $key ? 'btn-dark' : 'btn-light' }}">{{ $libelle }}</a>
        @endforeach
    </div>

    @if($plans->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Plan d'action</th>
                    <th>Objectif</th>
                    <th>Responsable</th>
                    <th style="text-align:center;">Priorité</th>
                    <th style="text-align:center;">Statut</th>
                    <th style="width:140px;">Avancement</th>
                    <th>Échéance</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $p)
                <tr>
                    <td><strong>{{ $p->titre }}</strong></td>
                    <td>
                        @if($p->objectif)
                        <a href="{{ route('objectifs.objectifs.show', $p->objectif) }}" style="font-size:.78rem;">{{ Str::limit($p->objectif->titre, 30) }}</a>
                        @else — @endif
                    </td>
                    <td>{{ $p->responsable?->prenoms }} {{ $p->responsable?->name }}</td>
                    <td style="text-align:center;">
                        <span class="opp-stage-badge" style="background:{{ $p->priorite_couleur }};font-size:.6rem;">{{ ucfirst($p->priorite) }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span class="opp-stage-badge" style="background:{{ $p->statut_couleur }};font-size:.6rem;">{{ $p->statut_libelle }}</span>
                    </td>
                    <td>
                        <div class="tache-progress-bar"><div class="tache-progress-fill" style="width:{{ $p->avancement }}%;background:{{ $p->statut_couleur }};"></div></div>
                        <small style="font-size:.65rem;">{{ $p->avancement }}%</small>
                    </td>
                    <td style="font-size:.75rem;">
                        @if($p->date_echeance)
                            <span class="{{ $p->estEnRetard() ? 'text-danger fw-bold' : '' }}">
                                {{ $p->date_echeance->format('d/m/Y') }}
                                @if($p->estEnRetard())<i class="fas fa-triangle-exclamation"></i>@endif
                            </span>
                        @else — @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><a class="dropdown-item" href="{{ route('objectifs.plans-action.show', $p) }}"><i class="fas fa-eye"></i> Détails</a></li>
                                <li><button class="dropdown-item" onclick='editPlan(@json($p))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('objectifs.plans-action.destroy', $p) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
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
        <div class="empty-state-icon" style="background:#EEF2FF;"><i class="fas fa-list-check" style="color:#6366F1;"></i></div>
        <h3>Aucun plan d'action</h3>
        <p>Les plans d'action déclinent vos objectifs en actions concrètes et mesurables.</p>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#6366F1,#4F46E5);" data-bs-toggle="modal" data-bs-target="#modalPlan">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalPlan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="planForm" method="POST" class="modal-content" action="{{ route('objectifs.plans-action.store') }}">
            @csrf
            <input type="hidden" name="_method" id="planMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="planModalTitle">Nouveau plan d'action</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="planTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="planDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Objectif <span class="text-danger">*</span></label>
                        <select name="objectif_id" id="planObjectif" class="form-select" required>
                            <option value="">— Choisir —</option>
                            @foreach($objectifs as $o)<option value="{{ $o->id }}">{{ $o->titre }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="planResp" class="form-select">
                            <option value="">—</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3"><label class="form-label">Statut <span class="text-danger">*</span></label>
                        <select name="statut" id="planStatut" class="form-select" required>
                            <option value="planifie">Planifié</option>
                            <option value="en_cours">En cours</option>
                            <option value="realise">Réalisé</option>
                            <option value="reporte">Reporté</option>
                            <option value="annule">Annulé</option>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select name="priorite" id="planPriorite" class="form-select" required>
                            <option value="basse">Basse</option>
                            <option value="normale" selected>Normale</option>
                            <option value="haute">Haute</option>
                            <option value="urgente">Urgente</option>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Date début</label>
                        <input type="date" name="date_debut" id="planDateDebut" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Échéance</label>
                        <input type="date" name="date_echeance" id="planDateEcheance" class="form-control"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Avancement (%)</label>
                        <input type="number" name="avancement" id="planAvancement" class="form-control" min="0" max="100" value="0"></div>
                    <div class="col-md-6"><label class="form-label">Budget estimé (XAF)</label>
                        <input type="number" name="budget_estime" id="planBudget" class="form-control" min="0" step="0.01"></div>
                </div>
                <div class="mt-3"><label class="form-label">Résultats attendus</label>
                    <textarea name="resultats_attendus" id="planResults" rows="2" class="form-control"></textarea></div>
                <div class="mt-3"><label class="form-label">Moyens requis</label>
                    <textarea name="moyens_requis" id="planMoyens" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#6366F1,#4F46E5);"><span id="planSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editPlan(p) {
    document.getElementById('planModalTitle').textContent = 'Modifier le plan';
    document.getElementById('planTitre').value = p.titre || '';
    document.getElementById('planDesc').value = p.description || '';
    document.getElementById('planObjectif').value = p.objectif_id || '';
    document.getElementById('planResp').value = p.responsable_id || '';
    document.getElementById('planStatut').value = p.statut || 'planifie';
    document.getElementById('planPriorite').value = p.priorite || 'normale';
    document.getElementById('planDateDebut').value = p.date_debut?.substring(0, 10) || '';
    document.getElementById('planDateEcheance').value = p.date_echeance?.substring(0, 10) || '';
    document.getElementById('planAvancement').value = p.avancement ?? 0;
    document.getElementById('planBudget').value = p.budget_estime || '';
    document.getElementById('planResults').value = p.resultats_attendus || '';
    document.getElementById('planMoyens').value = p.moyens_requis || '';
    document.getElementById('planMethod').value = 'PUT';
    document.getElementById('planSubmitText').textContent = 'Enregistrer';
    document.getElementById('planForm').action = '/objectifs/plans-action/' + p.id;
    new bootstrap.Modal(document.getElementById('modalPlan')).show();
}

document.getElementById('modalPlan')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('planModalTitle').textContent = 'Nouveau plan d\'action';
    ['planTitre','planDesc','planDateDebut','planDateEcheance','planBudget','planResults','planMoyens'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('planObjectif').value = '';
    document.getElementById('planResp').value = '';
    document.getElementById('planStatut').value = 'planifie';
    document.getElementById('planPriorite').value = 'normale';
    document.getElementById('planAvancement').value = '0';
    document.getElementById('planMethod').value = 'POST';
    document.getElementById('planSubmitText').textContent = 'Créer';
    document.getElementById('planForm').action = '{{ route("objectifs.plans-action.store") }}';
});
</script>
@endpush
@endsection
