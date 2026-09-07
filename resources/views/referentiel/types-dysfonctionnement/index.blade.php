@extends('layouts.app')

@section('title', 'Types de dysfonctionnement')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-triangle-exclamation me-2" style="color:#D97706;"></i>Types de dysfonctionnement</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Liste des typologies utilisées dans les tickets Moyens Généraux.</p>
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
                    <th style="width:25%;">Libellé</th>
                    <th style="width:20%;">Famille</th>
                    <th>Description</th>
                    <th style="width:12%; text-align:center;">Tickets liés</th>
                    <th style="width:15%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr>
                    <td><strong>{{ $type->libelle }}</strong></td>
                    <td>
                        @if($type->famille)
                            <span style="display:inline-flex; align-items:center; gap:.35rem; font-size:.8rem;">
                                @if($type->famille->couleur)<span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:{{ $type->famille->couleur }};"></span>@endif
                                {{ $type->famille->libelle }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:.85rem;">{{ $type->description ?? '—' }}</td>
                    <td style="text-align:center;">
                        <span class="badge bg-light text-dark">{{ $type->dysfonctionnements()->count() }}</span>
                    </td>
                    <td style="text-align:right;">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#editTypeModal-{{ $type->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="{{ route('referentiel.types-dysfonctionnement.destroy', $type) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Supprimer ce type ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox d-block mb-2" style="font-size:1.5rem; opacity:.4;"></i>
                        Aucun type enregistré. Cliquez sur « Nouveau type » pour en créer un.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modales d'édition (hors de la table pour un HTML valide) --}}
@foreach($types as $type)
<div class="modal fade" id="editTypeModal-{{ $type->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.types-dysfonctionnement.update', $type) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Modifier le type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" value="{{ $type->libelle }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Famille</label>
                    <select name="famille_id" class="form-select">
                        <option value="">— Aucune</option>
                        @foreach($familles as $f)
                        <option value="{{ $f->id }}" @selected($type->famille_id == $f->id)>{{ $f->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $type->description }}</textarea>
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

{{-- Modale de création --}}
<div class="modal fade" id="createTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('referentiel.types-dysfonctionnement.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nouveau type de dysfonctionnement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Famille</label>
                    <select name="famille_id" class="form-select">
                        <option value="">— Aucune</option>
                        @foreach($familles as $f)
                        <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
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
