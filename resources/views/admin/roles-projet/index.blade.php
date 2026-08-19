@extends('layouts.app')
@section('title', 'Rôles projet — Paramétrage')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Rôles projet</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #4F46E5;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#4F46E5,#6366F1);"><i class="fas fa-user-tag"></i></span>
                Rôles projet
            </h1>
            <p class="page-subtitle">Paramétrage des rôles attribuables aux ressources d'un projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#4F46E5,#6366F1);" data-bs-toggle="modal" data-bs-target="#modalRole">
            <i class="fas fa-plus me-2"></i> Nouveau rôle
        </button>
    </div>

    @if($roles->count())
    <div class="form-card">
        <table class="opp-table" id="rolesTable">
            <thead>
                <tr>
                    <th style="width:30px;"></th>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Couleur</th>
                    <th>Description</th>
                    <th>Utilisations</th>
                    <th>Actif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="rolesBody">
                @foreach($roles as $r)
                <tr data-id="{{ $r->id }}">
                    <td><i class="fas fa-grip-vertical" style="cursor:grab;color:#94A3B8;"></i></td>
                    <td><strong>{{ $r->libelle }}</strong></td>
                    <td><code>{{ $r->code }}</code></td>
                    <td><span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:{{ $r->couleur ?? '#64748B' }};"></span></td>
                    <td style="font-size:.78rem;color:#64748B;">{{ Str::limit($r->description, 50) }}</td>
                    <td>{{ $r->ressources_count ?? $r->ressources()->count() }}</td>
                    <td>
                        @if($r->est_actif)<span class="badge-client">Actif</span>
                        @else<span class="badge-prospect">Inactif</span>@endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editRole({{ $r->id }}, '{{ addslashes($r->libelle) }}', '{{ $r->code }}', '{{ $r->couleur }}', '{{ addslashes($r->description ?? '') }}', {{ $r->est_actif ? 'true' : 'false' }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('admin.roles-projet.destroy', $r) }}" method="POST" onsubmit="return confirm('Supprimer ce rôle ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#EEF2FF;"><i class="fas fa-user-tag" style="color:#4F46E5;"></i></div>
        <h3>Aucun rôle défini</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#4F46E5,#6366F1);" data-bs-toggle="modal" data-bs-target="#modalRole">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalRole" tabindex="-1">
    <div class="modal-dialog">
        <form id="roleForm" method="POST" class="modal-content" action="{{ route('admin.roles-projet.store') }}">
            @csrf
            <input type="hidden" name="_method" id="roleMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="roleModalTitle">Nouveau rôle projet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="roleLibelle" class="form-control" required placeholder="Chef de projet, Analyste..."></div>
                    <div class="col-md-4"><label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="roleCode" class="form-control" required placeholder="chef_projet" maxlength="50"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="roleCouleur" class="form-control form-control-color" value="#6366F1"></div>
                    <div class="col-md-8" id="roleActifWrap" style="display:none;">
                        <label class="form-label">Statut</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="est_actif" id="roleActif" value="1" checked>
                            <label class="form-check-label" for="roleActif">Rôle actif (visible dans les formulaires)</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3"><label class="form-label">Description</label>
                    <textarea name="description" id="roleDescription" rows="2" class="form-control" placeholder="Description du rôle..."></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#4F46E5;"><i class="fas fa-plus me-2"></i> <span id="roleSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function editRole(id, libelle, code, couleur, description, actif) {
    document.getElementById('roleModalTitle').textContent = 'Modifier le rôle';
    document.getElementById('roleLibelle').value = libelle;
    document.getElementById('roleCode').value = code;
    document.getElementById('roleCouleur').value = couleur || '#6366F1';
    document.getElementById('roleDescription').value = description;
    document.getElementById('roleActif').checked = actif;
    document.getElementById('roleActifWrap').style.display = 'block';
    document.getElementById('roleMethod').value = 'PUT';
    document.getElementById('roleSubmitText').textContent = 'Enregistrer';
    document.getElementById('roleForm').action = '/admin/roles-projet/' + id;
    new bootstrap.Modal(document.getElementById('modalRole')).show();
}

document.getElementById('modalRole')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('roleModalTitle').textContent = 'Nouveau rôle projet';
    ['roleLibelle','roleCode','roleDescription'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('roleCouleur').value = '#6366F1';
    document.getElementById('roleActif').checked = true;
    document.getElementById('roleActifWrap').style.display = 'none';
    document.getElementById('roleMethod').value = 'POST';
    document.getElementById('roleSubmitText').textContent = 'Créer';
    document.getElementById('roleForm').action = '{{ route("admin.roles-projet.store") }}';
});

// Auto-générer le code depuis le libellé
document.getElementById('roleLibelle')?.addEventListener('input', function () {
    const codeEl = document.getElementById('roleCode');
    if (document.getElementById('roleMethod').value === 'POST') {
        codeEl.value = this.value.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '').substring(0, 50);
    }
});

// Drag & drop reorder
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('rolesBody');
    if (tbody) {
        new Sortable(tbody, {
            handle: '.fa-grip-vertical',
            animation: 180,
            ghostClass: 'kanban-ghost',
            onEnd: function () {
                const order = Array.from(tbody.querySelectorAll('tr')).map(el => el.dataset.id);
                fetch('{{ route("admin.roles-projet.reorder") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order }),
                });
            },
        });
    }
});
</script>
@endpush
@endsection
