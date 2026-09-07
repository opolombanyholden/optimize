@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item active">Utilisateurs</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-users-cog me-2" style="color:#475569;"></i>Utilisateurs</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">{{ $stats['total'] }} au total · {{ $stats['actifs'] }} actifs · {{ $stats['inactifs'] }} désactivés</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="fas fa-user-plus me-1"></i> Nouvel utilisateur</button>
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label mb-1" style="font-size:.75rem;">Recherche</label><input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nom, prénom, email, matricule…"></div>
            <div class="col-md-3"><label class="form-label mb-1" style="font-size:.75rem;">Rôle</label>
                <select name="role" class="form-select"><option value="">Tous</option>@foreach($roles as $r)<option value="{{ $r->name }}" @selected(request('role')===$r->name)>{{ $r->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-2"><label class="form-label mb-1" style="font-size:.75rem;">Statut</label>
                <select name="statut" class="form-select"><option value="">Tous</option><option value="1" @selected(request('statut')==='1')>Actif</option><option value="0" @selected(request('statut')==='0')>Désactivé</option></select>
            </div>
            <div class="col-md-1"><button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.85rem;">
            <thead style="background:#F8FAFC;"><tr>
                <th class="ps-3">Utilisateur</th><th>Email</th><th>Matricule</th><th>Rôles</th>
                <th class="text-center">Statut</th><th class="text-end pe-3">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="ps-3">
                        <strong>{{ $u->prenoms }} {{ $u->name }}</strong>
                        @if($u->poste)<div class="text-muted" style="font-size:.72rem;">{{ $u->poste }}</div>@endif
                    </td>
                    <td><code style="background:#F1F5F9; padding:.1rem .35rem; border-radius:3px;">{{ $u->email }}</code></td>
                    <td>{{ $u->matricule ?: '—' }}</td>
                    <td>
                        @forelse($u->roles as $r)
                            <span class="badge" style="background:#DBEAFE;color:#1E40AF;">{{ $r->name }}</span>
                        @empty
                            <span class="text-muted" style="font-size:.75rem;">Aucun</span>
                        @endforelse
                    </td>
                    <td class="text-center">
                        @if($u->statut)<span class="badge bg-success">Actif</span>
                        @else<span class="badge bg-secondary">Désactivé</span>@endif
                        @if($u->must_change_password)<span class="badge bg-warning text-dark ms-1" title="Mot de passe à changer au prochain login">MDP</span>@endif
                    </td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $u->id }}" title="Modifier"><i class="fas fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetUserModal-{{ $u->id }}" title="Reset MDP"><i class="fas fa-key"></i></button>
                        @if($u->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle', $u) }}" method="POST" class="d-inline">@csrf
                            <button class="btn btn-sm btn-outline-{{ $u->statut ? 'secondary' : 'success' }}" title="{{ $u->statut ? 'Désactiver' : 'Activer' }}">
                                <i class="fas fa-{{ $u->statut ? 'user-slash' : 'user-check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement cet utilisateur ?');">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
</div>

{{-- ══ Modale CRÉATION ══ --}}
<div class="modal fade" id="createUserModal" tabindex="-1"><div class="modal-dialog modal-lg"><form action="{{ route('admin.users.store') }}" method="POST" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nouvel utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Prénoms</label><input type="text" name="prenoms" class="form-control" maxlength="100"></div>
            <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required maxlength="100"></div>
            <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required maxlength="150"></div>
            <div class="col-md-6"><label class="form-label">Matricule</label><input type="text" name="matricule" class="form-control" maxlength="50"></div>
            <div class="col-md-6"><label class="form-label">Poste</label><input type="text" name="poste" class="form-control" maxlength="150"></div>
            <div class="col-md-6"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control" maxlength="30"></div>
            <div class="col-12"><label class="form-label">Mot de passe <span class="text-muted small">(vide = généré automatiquement)</span></label><input type="text" name="password" class="form-control" minlength="8" placeholder="Laisser vide pour génération auto"></div>
            <div class="col-12"><label class="form-label">Rôles</label>
                <div class="d-flex flex-wrap gap-3">@foreach($roles as $r)<div class="form-check"><input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->name }}" id="createRole-{{ $r->id }}"><label class="form-check-label" for="createRole-{{ $r->id }}">{{ $r->name }}</label></div>@endforeach</div>
            </div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="must_change_password" value="1" id="createMustChange" checked><label class="form-check-label" for="createMustChange">Forcer changement de mot de passe au 1<sup>er</sup> login</label></div></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Créer</button></div>
</form></div></div>

{{-- ══ Modales ÉDITION + RESET par utilisateur ══ --}}
@foreach($users as $u)
    <div class="modal fade" id="editUserModal-{{ $u->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><form action="{{ route('admin.users.update', $u) }}" method="POST" class="modal-content">@csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Modifier — {{ $u->prenoms }} {{ $u->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Prénoms</label><input type="text" name="prenoms" class="form-control" value="{{ $u->prenoms }}"></div>
                <div class="col-md-6"><label class="form-label">Nom *</label><input type="text" name="name" class="form-control" value="{{ $u->name }}" required></div>
                <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ $u->email }}" required></div>
                <div class="col-md-6"><label class="form-label">Matricule</label><input type="text" name="matricule" class="form-control" value="{{ $u->matricule }}"></div>
                <div class="col-md-6"><label class="form-label">Poste</label><input type="text" name="poste" class="form-control" value="{{ $u->poste }}"></div>
                <div class="col-md-6"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control" value="{{ $u->contact }}"></div>
                <div class="col-12"><label class="form-label">Rôles</label>
                    <div class="d-flex flex-wrap gap-3">@foreach($roles as $r)@php $checked = $u->roles->contains('name', $r->name); @endphp<div class="form-check"><input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r->name }}" id="editRole-{{ $u->id }}-{{ $r->id }}" @checked($checked)><label class="form-check-label" for="editRole-{{ $u->id }}-{{ $r->id }}">{{ $r->name }}</label></div>@endforeach</div>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
    </form></div></div>

    <div class="modal fade" id="resetUserModal-{{ $u->id }}" tabindex="-1"><div class="modal-dialog"><form action="{{ route('admin.users.reset', $u) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-key me-2 text-warning"></i>Reset MDP — {{ $u->prenoms }} {{ $u->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-info" style="font-size:.85rem;">L'utilisateur sera contraint de changer son mot de passe au prochain login.</div>
            <label class="form-label">Nouveau mot de passe <span class="text-muted small">(vide = généré aléatoirement)</span></label>
            <input type="text" name="nouveau_mdp" class="form-control" minlength="8" placeholder="Laisser vide pour génération auto">
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-warning">Réinitialiser</button></div>
    </form></div></div>
@endforeach
@endsection
