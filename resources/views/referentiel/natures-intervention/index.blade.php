@extends('layouts.app')

@section('title', 'Natures d\'intervention')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-wrench me-2" style="color:#4F46E5;"></i>Natures d'intervention</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Typologies (préventive, corrective, curative, améliorative…) utilisées lors de la planification d'une intervention.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNatureModal">
        <i class="fas fa-plus me-1"></i> Nouvelle nature
    </button>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th style="width:15%;">Code</th>
                    <th style="width:25%;">Libellé</th>
                    <th>Description</th>
                    <th style="width:10%; text-align:center;">Couleur</th>
                    <th style="width:10%; text-align:center;">Interventions</th>
                    <th style="width:8%; text-align:center;">Actif</th>
                    <th style="width:15%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($natures as $nature)
                <tr>
                    <td><code>{{ $nature->code }}</code></td>
                    <td><strong>{{ $nature->libelle }}</strong></td>
                    <td class="text-muted" style="font-size:.85rem;">{{ $nature->description ?? '—' }}</td>
                    <td style="text-align:center;">
                        @if($nature->couleur)
                        <span style="display:inline-block; width:20px; height:20px; border-radius:4px; background:{{ $nature->couleur }};" title="{{ $nature->couleur }}"></span>
                        @endif
                    </td>
                    <td style="text-align:center;"><span class="badge bg-light text-dark">{{ $nature->interventions_count }}</span></td>
                    <td style="text-align:center;">
                        @if($nature->actif)
                        <span class="badge bg-success">Oui</span>
                        @else
                        <span class="badge bg-secondary">Non</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editNatureModal-{{ $nature->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="{{ route('referentiel.natures-intervention.destroy', $nature) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Supprimer cette nature ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem; opacity:.4;"></i>
                        Aucune nature enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modales d'édition (hors table pour HTML valide) --}}
@foreach($natures as $nature)
<div class="modal fade" id="editNatureModal-{{ $nature->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.natures-intervention.update', $nature) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Modifier la nature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Code <span class="text-muted" style="font-size:.72rem;">(slug)</span></label>
                        <input type="text" name="code" class="form-control" value="{{ $nature->code }}">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" value="{{ $nature->libelle }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $nature->description }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="couleur" class="form-control form-control-color" value="{{ $nature->couleur ?? '#4F46E5' }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-{{ $nature->id }}" @checked($nature->actif)>
                            <label class="form-check-label" for="actif-{{ $nature->id }}">Actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="createNatureModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.natures-intervention.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle nature d'intervention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Code <span class="text-muted" style="font-size:.72rem;">(auto si vide)</span></label>
                        <input type="text" name="code" class="form-control">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Couleur</label>
                        <input type="color" name="couleur" class="form-control form-control-color" value="#4F46E5">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1" id="actif-new" checked>
                            <label class="form-check-label" for="actif-new">Actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>
@endsection
