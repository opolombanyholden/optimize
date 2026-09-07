@extends('layouts.app')
@section('title', 'Statuts')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item">Référentiels intranet</li><li class="breadcrumb-item active">Statuts</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-flag me-2" style="color:#475569;"></i>Statuts</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Statuts génériques utilisés par tâches, projets, phases, jalons.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStatutModal"><i class="fas fa-plus me-1"></i>Nouveau statut</button>
</div>

<div class="card data-card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead style="background:#F8FAFC;"><tr><th class="ps-3">Libellé</th><th style="width:20%;">Couleur</th><th class="text-end pe-3">Actions</th></tr></thead>
    <tbody>
        @forelse($statuts as $s)
        <tr>
            <td class="ps-3"><strong>{{ $s->libelle }}</strong>
                @if(in_array($s->libelle, ['Non démarré','En cours','Terminé','Annulé'], true))<span class="badge bg-info ms-1" style="font-size:.6rem;">SYSTÈME</span>@endif
            </td>
            <td>
                @if($s->couleur)<span style="display:inline-flex; align-items:center; gap:.35rem; font-size:.8rem;"><span style="width:16px; height:16px; background:{{ $s->couleur }}; border-radius:3px; border:1px solid #E5E7EB;"></span><code>{{ $s->couleur }}</code></span>
                @else <span class="text-muted" style="font-size:.75rem;">—</span>@endif
            </td>
            <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStatutModal-{{ $s->id }}"><i class="fas fa-pen"></i></button>
                <form action="{{ route('admin.statuts.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce statut ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
            </td>
        </tr>
        @empty<tr><td colspan="3" class="text-center text-muted py-3">Aucun statut.</td></tr>@endforelse
    </tbody>
</table></div></div>

<div class="modal fade" id="createStatutModal" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.statuts.store') }}" method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Nouveau statut</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" required maxlength="50"></div>
        <div class="mb-3"><label class="form-label">Couleur</label><input type="color" name="couleur" class="form-control form-control-color" value="#64748B" style="width:80px;"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
</form></div></div>

@foreach($statuts as $s)
<div class="modal fade" id="editStatutModal-{{ $s->id }}" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.statuts.update', $s) }}" method="POST" class="modal-content">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Modifier « {{ $s->libelle }} »</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Libellé *</label><input type="text" name="libelle" class="form-control" value="{{ $s->libelle }}" required maxlength="50"></div>
        <div class="mb-3"><label class="form-label">Couleur</label><input type="color" name="couleur" class="form-control form-control-color" value="{{ $s->couleur ?: '#64748B' }}" style="width:80px;"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form></div></div>
@endforeach
@endsection
