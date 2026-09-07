@extends('layouts.app')
@section('title', 'Rôle — ' . $role->name)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">Administration</li>
        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Rôles</a></li>
        <li class="breadcrumb-item active">{{ $role->name }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-key me-2" style="color:#475569;"></i>Permissions du rôle <code style="background:#DBEAFE;color:#1E40AF;padding:.1rem .5rem;border-radius:4px;">{{ $role->name }}</code></h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Cochez / décochez les permissions puis « Enregistrer les changements ». <strong>{{ count($actives) }}</strong> permission(s) actives actuellement.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
</div>

@if($role->name === 'super-admin')
<div class="alert alert-warning"><i class="fas fa-crown me-1"></i><strong>super-admin</strong> conserve toujours toutes les permissions par défaut. Les modifications sont sans effet.</div>
@endif

<form action="{{ route('admin.roles.permissions.sync', $role) }}" method="POST">@csrf @method('PUT')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="document.querySelectorAll('.perm-cb').forEach(c=>c.checked=true)"><i class="fas fa-check-double me-1"></i>Tout cocher</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.querySelectorAll('.perm-cb').forEach(c=>c.checked=false)"><i class="fas fa-square me-1"></i>Tout décocher</button>
        </div>
        <button class="btn btn-primary" @disabled($role->name === 'super-admin')><i class="fas fa-save me-1"></i>Enregistrer les changements</button>
    </div>

    <div class="row g-3">
        @foreach($groupes as $entite => $perms)
        <div class="col-md-6 col-xl-4">
            <div class="card data-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#F8FAFC;">
                    <h6 class="mb-0" style="font-size:.85rem; font-weight:700; text-transform:capitalize;"><i class="fas fa-cube me-1 text-muted"></i>{{ str_replace('_', ' ', $entite) }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="font-size:.7rem; padding:.15rem .5rem;" onclick="document.querySelectorAll('[data-groupe=&quot;{{ $entite }}&quot;]').forEach(c=>c.checked=true)">Tout</button>
                </div>
                <div class="card-body">
                    @foreach($perms as $p)
                    @php [$action,] = array_pad(explode(':', $p->name, 2), 2, null); @endphp
                    <div class="form-check">
                        <input class="form-check-input perm-cb" type="checkbox" name="permissions[]" value="{{ $p->name }}"
                               id="perm-{{ $p->id }}" data-groupe="{{ $entite }}" @checked(in_array($p->name, $actives))>
                        <label class="form-check-label" for="perm-{{ $p->id }}" style="font-size:.82rem;">
                            <strong>{{ $action }}</strong> <span class="text-muted" style="font-size:.7rem;">({{ $p->name }})</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button class="btn btn-primary" @disabled($role->name === 'super-admin')><i class="fas fa-save me-1"></i>Enregistrer les changements</button>
    </div>
</form>
@endsection
