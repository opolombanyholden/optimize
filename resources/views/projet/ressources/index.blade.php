@extends('layouts.app')
@section('title', $projet->nom . ' — Ressources')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Ressources</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'ressources'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-people-carry-box"></i></span>
                Ressources — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Affectation et suivi de l'équipe projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalRessource">
            <i class="fas fa-plus me-2"></i> Ajouter une ressource
        </button>
    </div>

    @if($projet->ressources->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Collaborateur</th>
                    <th>Rôle projet</th>
                    <th>Heures allouées</th>
                    <th>Heures réelles</th>
                    <th>Taux jour.</th>
                    <th>Coût estimé</th>
                    <th>Période</th>
                    <th>Actif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($projet->ressources as $r)
                <tr>
                    <td><strong>{{ $r->utilisateur?->prenoms }} {{ $r->utilisateur?->name }}</strong></td>
                    <td>
                        @if($r->roleProjet)
                        <span class="opp-stage-badge" style="background:{{ $r->roleProjet->couleur ?? '#64748B' }};font-size:.65rem;">{{ $r->roleProjet->libelle }}</span>
                        @else — @endif
                    </td>
                    <td>{{ $r->heures_allouees ?? '—' }}h</td>
                    <td>{{ $r->heures_reelles ?? '—' }}h</td>
                    <td>{{ $r->taux_journalier ? number_format($r->taux_journalier, 0, ',', ' ') : '—' }}</td>
                    <td>{{ number_format($r->cout_estime, 0, ',', ' ') }} {{ $projet->devise }}</td>
                    <td>
                        @if($r->date_debut){{ $r->date_debut->format('d/m/Y') }}@endif
                        @if($r->date_fin) → {{ $r->date_fin->format('d/m/Y') }}@endif
                    </td>
                    <td>
                        @if($r->est_actif)<span class="badge-client">Actif</span>
                        @else<span class="badge-prospect">Inactif</span>@endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editRessource({{ $r->id }}, '{{ $r->role_projet_id }}', '{{ $r->heures_allouees }}', '{{ $r->heures_reelles }}', '{{ $r->taux_journalier }}', '{{ $r->date_debut?->format('Y-m-d') }}', '{{ $r->date_fin?->format('Y-m-d') }}', {{ $r->est_actif ? 'true' : 'false' }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.ressources.destroy', [$projet, $r]) }}" method="POST" onsubmit="return confirm('Retirer ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Retirer</button>
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
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-people-carry-box" style="color:#0D9488;"></i></div>
        <h3>Aucune ressource</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalRessource">
            <i class="fas fa-plus me-2"></i> Ajouter
        </button>
    </div>
    @endif
</div>

{{-- Modale ajout --}}
<div class="modal fade" id="modalRessource" tabindex="-1">
    <div class="modal-dialog">
        <form id="ressourceForm" action="{{ route('projet.ressources.store', $projet) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="ressourceMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="ressourceModalTitle">Ajouter une ressource</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3" id="ressourceUserWrap">
                    <label class="form-label">Collaborateur <span class="text-danger">*</span></label>
                    <select name="user_id" id="ressourceUser" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rôle dans le projet</label>
                    <select name="role_projet_id" id="ressourceRole" class="form-select">
                        <option value="">— Choisir —</option>
                        @foreach($rolesProjet as $rp)
                            <option value="{{ $rp->id }}" style="color:{{ $rp->couleur }};">{{ $rp->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Heures allouées</label>
                        <input type="number" step="0.5" name="heures_allouees" id="ressourceHeures" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Heures réelles</label>
                        <input type="number" step="0.5" name="heures_reelles" id="ressourceHeuresR" class="form-control" style="display:none;"></div>
                    <div class="col-md-4"><label class="form-label">Taux journalier</label>
                        <input type="number" step="0.01" name="taux_journalier" id="ressourceTaux" class="form-control"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Date début</label>
                        <input type="date" name="date_debut" id="ressourceDateDebut" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Date fin</label>
                        <input type="date" name="date_fin" id="ressourceDateFin" class="form-control"></div>
                </div>
                <div class="mt-3" id="ressourceActifWrap" style="display:none;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="est_actif" id="ressourceActif" value="1" checked>
                        <label class="form-check-label" for="ressourceActif">Ressource active</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-plus me-2"></i> <span id="ressourceSubmitText">Ajouter</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editRessource(id, roleId, heures, heuresR, taux, debut, fin, actif) {
    document.getElementById('ressourceModalTitle').textContent = 'Modifier la ressource';
    document.getElementById('ressourceUserWrap').style.display = 'none';
    document.getElementById('ressourceRole').value = roleId || '';
    document.getElementById('ressourceHeures').value = heures || '';
    document.getElementById('ressourceHeuresR').value = heuresR || '';
    document.getElementById('ressourceHeuresR').style.display = 'block';
    document.getElementById('ressourceTaux').value = taux || '';
    document.getElementById('ressourceDateDebut').value = debut || '';
    document.getElementById('ressourceDateFin').value = fin || '';
    document.getElementById('ressourceActifWrap').style.display = 'block';
    document.getElementById('ressourceActif').checked = actif;
    document.getElementById('ressourceMethod').value = 'PUT';
    document.getElementById('ressourceSubmitText').textContent = 'Enregistrer';
    document.getElementById('ressourceForm').action = '/projet/{{ $projet->id }}/ressources/' + id;
    new bootstrap.Modal(document.getElementById('modalRessource')).show();
}

document.getElementById('modalRessource')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('ressourceModalTitle').textContent = 'Ajouter une ressource';
    document.getElementById('ressourceUserWrap').style.display = 'block';
    document.getElementById('ressourceUser').value = '';
    document.getElementById('ressourceRole').value = '';
    ['ressourceHeures','ressourceTaux','ressourceDateDebut','ressourceDateFin'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('ressourceHeuresR').value = '';
    document.getElementById('ressourceHeuresR').style.display = 'none';
    document.getElementById('ressourceActifWrap').style.display = 'none';
    document.getElementById('ressourceActif').checked = true;
    document.getElementById('ressourceMethod').value = 'POST';
    document.getElementById('ressourceSubmitText').textContent = 'Ajouter';
    document.getElementById('ressourceForm').action = '{{ route("projet.ressources.store", $projet) }}';
});
</script>
@endpush
@endsection
