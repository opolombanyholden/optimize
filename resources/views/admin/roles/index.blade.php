@extends('layouts.app')
@section('title', 'Rôles')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item active">Rôles</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-shield-halved me-2" style="color:#475569;"></i>Rôles</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Rôles Spatie utilisés pour l'accès aux fonctionnalités. Cliquez sur un rôle pour affecter ses permissions.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal"><i class="fas fa-plus me-1"></i> Nouveau rôle</button>
</div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
        <thead style="background:#F8FAFC;"><tr>
            <th class="ps-3">Rôle</th><th class="text-center">Utilisateurs</th><th class="text-center">Permissions</th><th class="text-end pe-3">Actions</th>
        </tr></thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td class="ps-3">
                    <a href="{{ route('admin.roles.show', $role) }}" style="text-decoration:none; color:#0F172A; font-weight:600;">{{ $role->name }}</a>
                    @if(in_array($role->name, ['super-admin','admin','user'], true))<span class="badge bg-info ms-1" style="font-size:.6rem;">SYSTÈME</span>@endif
                </td>
                <td class="text-center"><span class="badge bg-secondary">{{ $role->users_count }}</span></td>
                <td class="text-center">
                    @if($role->name === 'super-admin')<span class="badge bg-warning text-dark">Toutes ({{ $role->permissions_count }})</span>
                    @else<span class="badge" style="background:#DBEAFE;color:#1E40AF;">{{ $role->permissions_count }}</span>@endif
                </td>
                <td class="text-end pe-3">
                    <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-outline-primary" title="Permissions"><i class="fas fa-key"></i></a>
                    @if(!in_array($role->name, ['super-admin','admin','user'], true))
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoleModal-{{ $role->id }}"><i class="fas fa-pen"></i></button>
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer le rôle « {{ $role->name }} » ?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table></div>
</div>

<div class="modal fade" id="createRoleModal" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.roles.store') }}" method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-shield-halved me-2"></i>Nouveau rôle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Nom du rôle *</label>
        <input type="text" name="name" class="form-control" required maxlength="50" placeholder="Ex : chef-service, gestionnaire-appro">
        <div class="form-text">Sans espaces, en minuscules, séparateurs autorisés : tiret ou underscore.</div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
</form></div></div>

@foreach($roles as $role)
@if(!in_array($role->name, ['super-admin','admin','user'], true))
<div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.roles.update', $role) }}" method="POST" class="modal-content">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">Renommer « {{ $role->name }} »</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="text" name="name" class="form-control" value="{{ $role->name }}" required maxlength="50"></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
</form></div></div>
@endif
@endforeach
@endsection
