@extends('layouts.app')

@section('title', 'Fréquences de paiement')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-calendar-check me-2" style="color:#0A66C2;"></i>Fréquences de paiement</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Périodicités disponibles pour les échéances d'engagement fournisseur.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFreqModal">
        <i class="fas fa-plus me-1"></i> Nouvelle fréquence
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
                    <th style="width:12%; text-align:center;">Mois entre 2 échéances</th>
                    <th style="width:8%; text-align:center;">Ordre</th>
                    <th style="width:8%; text-align:center;">Actif</th>
                    <th style="width:12%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($frequences as $f)
                <tr>
                    <td><code>{{ $f->code }}</code></td>
                    <td><strong>{{ $f->libelle }}</strong></td>
                    <td class="text-muted" style="font-size:.85rem;">{{ $f->description ?? '—' }}</td>
                    <td class="text-center">{{ $f->mois_increment !== null ? $f->mois_increment.' mois' : '—' }}</td>
                    <td class="text-center">{{ $f->ordre }}</td>
                    <td class="text-center">
                        @if($f->actif)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFreqModal-{{ $f->id }}"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('referentiel.frequences-paiement.destroy', $f) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette fréquence ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune fréquence enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($frequences as $f)
<div class="modal fade" id="editFreqModal-{{ $f->id }}" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('referentiel.frequences-paiement.update', $f) }}" method="POST" class="modal-content">@csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Modifier la fréquence</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="{{ $f->code }}"></div>
                <div class="col-md-7"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" value="{{ $f->libelle }}" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2">{{ $f->description }}</textarea></div>
                <div class="col-md-4"><label class="form-label">Mois entre échéances</label><input type="number" min="1" max="24" name="mois_increment" class="form-control" value="{{ $f->mois_increment }}"><div class="form-text">Vide = paiement ponctuel</div></div>
                <div class="col-md-4"><label class="form-label">Ordre</label><input type="number" name="ordre" class="form-control" value="{{ $f->ordre }}"></div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-f-{{ $f->id }}" @checked($f->actif)><label class="form-check-label" for="actif-f-{{ $f->id }}">Actif</label></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
    </form></div>
</div>
@endforeach

<div class="modal fade" id="createFreqModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('referentiel.frequences-paiement.store') }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Nouvelle fréquence de paiement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Code <span class="text-muted small">(auto si vide)</span></label><input type="text" name="code" class="form-control"></div>
                <div class="col-md-7"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" required></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-md-4"><label class="form-label">Mois entre échéances</label><input type="number" min="1" max="24" name="mois_increment" class="form-control"><div class="form-text">Vide = ponctuel</div></div>
                <div class="col-md-4"><label class="form-label">Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-new-freq" checked><label class="form-check-label" for="actif-new-freq">Actif</label></div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
    </form></div>
</div>
@endsection
