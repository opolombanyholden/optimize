@extends('layouts.app')

@section('title', 'Types d\'engagement')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-file-contract me-2" style="color:#0A66C2;"></i>Types d'engagement fournisseur</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Natures d'engagement utilisables : achat, prestation, cadre, maintenance…</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTypeModal">
        <i class="fas fa-plus me-1"></i> Nouveau type
    </button>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th style="width:15%;">Code</th>
                    <th>Libellé</th>
                    <th>Description</th>
                    <th style="width:8%; text-align:center;">Ordre</th>
                    <th style="width:8%; text-align:center;">Actif</th>
                    <th style="width:12%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr>
                    <td><code>{{ $type->code }}</code></td>
                    <td><strong>{{ $type->libelle }}</strong></td>
                    <td class="text-muted" style="font-size:.85rem;">{{ $type->description ?? '—' }}</td>
                    <td class="text-center">{{ $type->ordre }}</td>
                    <td class="text-center">
                        @if($type->actif)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTypeModal-{{ $type->id }}"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('referentiel.types-engagement.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce type ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun type enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($types as $type)
<div class="modal fade" id="editTypeModal-{{ $type->id }}" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('referentiel.types-engagement.update', $type) }}" method="POST" class="modal-content">@csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Modifier le type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="{{ $type->code }}"></div>
                <div class="col-md-7"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" value="{{ $type->libelle }}" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Ordre</label><input type="number" name="ordre" class="form-control" value="{{ $type->ordre }}"></div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-{{ $type->id }}" @checked($type->actif)><label class="form-check-label" for="actif-{{ $type->id }}">Actif</label></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
    </form></div>
</div>
@endforeach

<div class="modal fade" id="createTypeModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('referentiel.types-engagement.store') }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Nouveau type d'engagement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Code <span class="text-muted small">(auto si vide)</span></label><input type="text" name="code" class="form-control"></div>
                <div class="col-md-7"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-md-6"><label class="form-label">Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-new-type" checked><label class="form-check-label" for="actif-new-type">Actif</label></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
    </form></div>
</div>
@endsection
