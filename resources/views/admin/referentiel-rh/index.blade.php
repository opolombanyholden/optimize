@extends('layouts.app')

@section('title', $meta['libelle'])

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Administration</li>
    <li class="breadcrumb-item">Référentiels RH</li>
    <li class="breadcrumb-item active">{{ $meta['libelle'] }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1><i class="fas {{ $meta['icon'] }} me-2 text-muted"></i> {{ $meta['libelle'] }}</h1>
        <p class="text-muted mb-0">Référentiel utilisé dans les formulaires RH (employés, contrats…)</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#refModal" onclick="refOpenCreate()">
        <i class="fas fa-plus me-1"></i> Ajouter
    </button>
</div>

{{-- Sub-nav référentiels (2 lignes : core RH + localisation administrative) --}}
<div class="mb-2">
    <div class="text-muted small mb-1 fw-bold">Référentiels RH</div>
    <div class="btn-group flex-wrap" role="group">
        @foreach(['types-contrat' => 'Types de contrat', 'postes' => 'Postes', 'departements' => 'Départements', 'types-evenement' => 'Types d\'évènement carrière', 'niveaux-qualification' => 'Niveaux de qualification', 'nationalites' => 'Nationalités', 'groupes-rubriques' => 'Groupes rubriques paie'] as $k => $lbl)
            <a href="{{ route('admin.referentiel-rh.index', $k) }}"
               class="btn btn-sm {{ $meta['type'] === $k ? 'btn-primary' : 'btn-outline-primary' }}">{{ $lbl }}</a>
        @endforeach
    </div>
</div>
<div class="mb-4">
    <div class="text-muted small mb-1 fw-bold">Localisation administrative</div>
    <div class="btn-group flex-wrap" role="group">
        @foreach(['pays-loc' => 'Pays', 'provinces' => 'Provinces', 'departements-admin' => 'Départements', 'prefectures' => 'Préfectures', 'sous-prefectures' => 'Sous-préfectures', 'communes' => 'Communes', 'arrondissements' => 'Arrondissements', 'quartiers' => 'Quartiers', 'cantons' => 'Cantons', 'regroupements-village' => 'Regr. villages', 'villages' => 'Villages'] as $k => $lbl)
            <a href="{{ route('admin.referentiel-rh.index', $k) }}"
               class="btn btn-sm {{ $meta['type'] === $k ? 'btn-success' : 'btn-outline-success' }}">{{ $lbl }}</a>
        @endforeach
    </div>
</div>

{{-- Recherche --}}
<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher par libellé ou code…">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-search me-1"></i> Rechercher</button>
                @if(request('q'))
                    <a href="{{ route('admin.referentiel-rh.index', $meta['type']) }}" class="btn btn-outline-secondary"><i class="fas fa-xmark"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2 text-muted"></i> Liste</h5>
        <span class="text-muted small">{{ $items->total() }} entrée(s)</span>
    </div>
    <div class="card-body p-0">
        @if($items->isEmpty())
            <div class="empty-state"><i class="fas {{ $meta['icon'] }}"></i><p>Aucune entrée. Cliquez sur "Ajouter" pour démarrer.</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:80px;">Ordre</th>
                            <th style="width:160px;">Code</th>
                            <th>Libellé</th>
                            <th>Description</th>
                            <th style="width:90px;">Statut</th>
                            <th class="text-end" style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->ordre }}</td>
                            <td><code style="background:#F1F5F9;padding:.15rem .5rem;border-radius:4px;">{{ $it->code }}</code></td>
                            <td><strong>{{ $it->libelle }}</strong></td>
                            <td class="text-muted small">{{ Str::limit($it->description, 80) }}</td>
                            <td>
                                @if($it->statut == 1)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#refModal"
                                        onclick='refOpenEdit(@json($it))' title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.referentiel-rh.destroy', [$meta['type'], $it->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($items->hasPages())
<div class="d-flex justify-content-center mt-4">{{ $items->withQueryString()->links() }}</div>
@endif

{{-- Modale ajout/édition --}}
<div class="modal fade" id="refModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="refForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="refMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refTitle">Ajouter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="refCode" class="form-control text-uppercase" maxlength="50" required>
                        <small class="form-text text-muted">Identifiant unique (ex: CDI, DEV_BACK). Espaces convertis en underscores.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="refLibelle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="refDescription" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Ordre</label>
                            <input type="number" name="ordre" id="refOrdre" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" id="refStatut" class="form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const REF_STORE_URL  = "{{ route('admin.referentiel-rh.store', $meta['type']) }}";
const REF_UPDATE_TPL = "{{ route('admin.referentiel-rh.update', [$meta['type'], '__ID__']) }}";

function refOpenCreate() {
    document.getElementById('refTitle').textContent = 'Ajouter';
    document.getElementById('refForm').action = REF_STORE_URL;
    document.getElementById('refMethod').value = 'POST';
    document.getElementById('refCode').value = '';
    document.getElementById('refLibelle').value = '';
    document.getElementById('refDescription').value = '';
    document.getElementById('refOrdre').value = 0;
    document.getElementById('refStatut').value = 1;
}
function refOpenEdit(item) {
    document.getElementById('refTitle').textContent = 'Modifier';
    document.getElementById('refForm').action = REF_UPDATE_TPL.replace('__ID__', item.id);
    document.getElementById('refMethod').value = 'PUT';
    document.getElementById('refCode').value = item.code;
    document.getElementById('refLibelle').value = item.libelle;
    document.getElementById('refDescription').value = item.description || '';
    document.getElementById('refOrdre').value = item.ordre;
    document.getElementById('refStatut').value = item.statut;
}
</script>
@endpush
@endsection
