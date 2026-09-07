@extends('layouts.app')

@section('title', 'Familles de dysfonctionnement')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-layer-group me-2" style="color:#DC2626;"></i>Familles de dysfonctionnement</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Regroupement de haut niveau des types de dysfonctionnement.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFamilleModal">
        <i class="fas fa-plus me-1"></i> Nouvelle famille
    </button>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th style="width:30%;">Libellé</th>
                    <th>Description</th>
                    <th style="width:12%; text-align:center;">Couleur</th>
                    <th style="width:12%; text-align:center;">Types</th>
                    <th style="width:15%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($familles as $famille)
                <tr>
                    <td><strong>{{ $famille->libelle }}</strong></td>
                    <td class="text-muted" style="font-size:.85rem;">{{ $famille->description ?? '—' }}</td>
                    <td style="text-align:center;">
                        @if($famille->couleur)
                        <span style="display:inline-block; width:20px; height:20px; border-radius:4px; background:{{ $famille->couleur }};" title="{{ $famille->couleur }}"></span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span class="badge bg-light text-dark">{{ $famille->types_count }}</span>
                    </td>
                    <td style="text-align:right;">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editFamilleModal-{{ $famille->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="{{ route('referentiel.familles-dysfonctionnement.destroy', $famille) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Supprimer cette famille ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem; opacity:.4;"></i>
                        Aucune famille enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modales d'édition (hors de la table pour un HTML valide) --}}
@foreach($familles as $famille)
<div class="modal fade" id="editFamilleModal-{{ $famille->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.familles-dysfonctionnement.update', $famille) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Modifier la famille</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" value="{{ $famille->libelle }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $famille->description }}</textarea>
                </div>
                <div>
                    <label class="form-label">Couleur (hex)</label>
                    <input type="color" name="couleur" class="form-control form-control-color" value="{{ $famille->couleur ?? '#DC2626' }}">
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

<div class="modal fade" id="createFamilleModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.familles-dysfonctionnement.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle famille</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div>
                    <label class="form-label">Couleur</label>
                    <input type="color" name="couleur" class="form-control form-control-color" value="#DC2626">
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
