@extends('layouts.app')
@section('title', 'Priorités')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item">Référentiels intranet</li><li class="breadcrumb-item active">Priorités</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-flag-checkered me-2" style="color:#475569;"></i>Priorités</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Niveaux de priorité pour tâches, événements, incidents.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPrioriteModal"><i class="fas fa-plus me-1"></i>Nouvelle priorité</button>
</div>

<div class="card data-card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead style="background:#F8FAFC;"><tr><th class="ps-3">Libellé</th><th style="width:20%;">Couleur</th><th class="text-end pe-3">Actions</th></tr></thead>
    <tbody>
        @forelse($priorites as $p)
        <tr>
            <td class="ps-3"><strong>{{ $p->libelle }}</strong>
                @if(in_array($p->libelle, ['Basse','Normale','Haute','Critique'], true))<span class="badge bg-info ms-1" style="font-size:.6rem;">SYSTÈME</span>@endif
            </td>
            <td>
                @if($p->couleur)<span style="display:inline-flex; align-items:center; gap:.35rem; font-size:.8rem;"><span style="width:16px; height:16px; background:{{ $p->couleur }}; border-radius:3px; border:1px solid #E5E7EB;"></span><code>{{ $p->couleur }}</code></span>
                @else <span class="text-muted" style="font-size:.75rem;">—</span>@endif
            </td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPrioriteModal-{{ $p->id }}"><i class="fas fa-pen"></i></button>
                <form action="{{ route('admin.priorites.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette priorité ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
            </td>
        </tr>
        @empty<tr><td colspan="3" class="text-center text-muted py-3">Aucune priorité.</td></tr>@endforelse
    </tbody>
</table></div></div>

<div class="modal fade" id="createPrioriteModal" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.priorites.store') }}" method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Nouvelle priorité</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" required maxlength="50"></div>
        <div class="mb-3"><label class="form-label">Couleur</label><input type="color" name="couleur" class="form-control form-control-color" value="#64748B" style="width:80px;"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
</form></div></div>

@foreach($priorites as $p)
<div class="modal fade" id="editPrioriteModal-{{ $p->id }}" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.priorites.update', $p) }}" method="POST" class="modal-content">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Modifier « {{ $p->libelle }} »</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" value="{{ $p->libelle }}" required maxlength="50"></div>
        <div class="mb-3"><label class="form-label">Couleur</label><input type="color" name="couleur" class="form-control form-control-color" value="{{ $p->couleur ?: '#64748B' }}" style="width:80px;"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form></div></div>
@endforeach
@endsection
