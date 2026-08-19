@extends('layouts.app')
@section('title', 'Annuaire — Entités')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item active">Entités</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-building"></i></span>
                Annuaire — Entités
            </h1>
            <p class="page-subtitle">{{ $services->count() }} entité(s) · Organisation interne (directions / services / départements / unités…)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" class="btn btn-light"><i class="fas fa-users me-1"></i> Collaborateurs</a>
            <a href="{{ route('intranet.annuaire.equipes.index') }}" class="btn btn-light"><i class="fas fa-people-group me-1"></i> Équipes</a>
            <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalService">
                <i class="fas fa-plus me-2"></i> Nouvelle entité
            </button>
        </div>
    </div>

    @if($services->count())
    <div class="row g-3">
        @foreach($services as $s)
        <div class="col-md-4">
            <div class="form-card h-100" style="border-left:4px solid {{ $s->couleur ?? '#0D9488' }};">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div style="width:40px;height:40px;border-radius:8px;background:{{ $s->couleur ?? '#0D9488' }};display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                        <i class="fas {{ $s->icone ?? 'fa-building' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('intranet.annuaire.services.show', $s) }}" style="text-decoration:none;color:inherit;">
                            <strong style="font-size:.9rem;color:#0F172A;">{{ $s->nom }}</strong>
                        </a>
                        <div class="d-flex gap-1 align-items-center mt-1">
                            @if($s->code)<code style="font-size:.62rem;">{{ $s->code }}</code>@endif
                            <span class="opp-stage-badge" style="background:#475569;font-size:.55rem;">{{ $s->type_entite_libelle }}</span>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                            <li><a class="dropdown-item" href="{{ route('intranet.annuaire.services.show', $s) }}"><i class="fas fa-eye"></i> Voir</a></li>
                            <li><button class="dropdown-item" onclick='editService(@json($s))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('intranet.annuaire.services.destroy', $s) }}" method="POST" onsubmit="return confirm('Supprimer ce service ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form></li>
                        </ul>
                    </div>
                </div>

                @if($s->description)<p style="font-size:.75rem;color:#64748B;margin-bottom:.5rem;">{{ Str::limit($s->description, 100) }}</p>@endif

                <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:.7rem;color:#475569;">
                    @if($s->chef)<span><i class="fas fa-user-tie me-1"></i>{{ $s->chef->prenoms }} {{ $s->chef->name }}</span>@endif
                    <span><i class="fas fa-users me-1"></i>{{ $s->membres_count ?? 0 }} membre(s)</span>
                    @if($s->localisation)<span><i class="fas fa-location-dot me-1"></i>{{ $s->localisation }}</span>@endif
                </div>

                @if($s->parent)
                <div style="font-size:.65rem;color:#94A3B8;">
                    Sous-service de <strong>{{ $s->parent->nom }}</strong>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-building" style="color:#0D9488;"></i></div>
        <h3>Aucune entité défini</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalService">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalService" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="serviceForm" method="POST" class="modal-content" action="{{ route('intranet.annuaire.services.store') }}">
            @csrf
            <input type="hidden" name="_method" id="serviceMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="serviceModalTitle">Nouvelle entité</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="srvNom" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Code</label>
                        <input type="text" name="code" id="srvCode" class="form-control" maxlength="20" placeholder="DSI, RH..."></div>
                    <div class="col-md-3"><label class="form-label">Type d'entité</label>
                        <select name="type_entite" id="srvType" class="form-select">
                            @foreach(\App\Models\Intranet\Service::TYPES_ENTITE as $k => $lbl)
                            <option value="{{ $k }}" @selected($k === 'service')>{{ $lbl }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="mb-3 mt-2"><label class="form-label">Description</label>
                    <textarea name="description" id="srvDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Chef de service</label>
                        <select name="chef_du_service_id" id="srvChef" class="form-select">
                            <option value="">—</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Service parent</label>
                        <select name="parent_id" id="srvParent" class="form-select">
                            <option value="">— Racine —</option>
                            @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="srvCouleur" class="form-control form-control-color" value="#0D9488"></div>
                    <div class="col-md-3"><label class="form-label">Icône</label>
                        <input type="text" name="icone" id="srvIcone" class="form-control" placeholder="fa-building"></div>
                    <div class="col-md-3"><label class="form-label">Email</label>
                        <input type="email" name="email" id="srvEmail" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" id="srvTel" class="form-control"></div>
                </div>
                <div class="mt-3"><label class="form-label">Localisation</label>
                    <input type="text" name="localisation" id="srvLoc" class="form-control" placeholder="Bâtiment, étage..."></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><span id="srvSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editService(s) {
    document.getElementById('serviceModalTitle').textContent = 'Modifier l&#039;entité';
    document.getElementById('srvNom').value = s.nom || '';
    document.getElementById('srvCode').value = s.code || '';
    document.getElementById('srvDesc').value = s.description || '';
    document.getElementById('srvChef').value = s.chef_du_service_id || '';
    document.getElementById('srvParent').value = s.parent_id || '';
    document.getElementById('srvCouleur').value = s.couleur || '#0D9488';
    document.getElementById('srvIcone').value = s.icone || '';
    document.getElementById('srvEmail').value = s.email || '';
    document.getElementById('srvTel').value = s.telephone || '';
    document.getElementById('srvLoc').value = s.localisation || '';
    document.getElementById('srvType').value = s.type_entite || 'service';
    document.getElementById('serviceMethod').value = 'PUT';
    document.getElementById('srvSubmitText').textContent = 'Enregistrer';
    document.getElementById('serviceForm').action = '/intranet/annuaire/services/' + s.id;
    new bootstrap.Modal(document.getElementById('modalService')).show();
}

document.getElementById('modalService')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('serviceModalTitle').textContent = 'Nouvelle entité';
    ['srvNom','srvCode','srvDesc','srvIcone','srvEmail','srvTel','srvLoc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('srvCouleur').value = '#0D9488';
    document.getElementById('srvChef').value = '';
    document.getElementById('srvParent').value = '';
    document.getElementById('srvType').value = 'service';
    document.getElementById('serviceMethod').value = 'POST';
    document.getElementById('srvSubmitText').textContent = 'Créer';
    document.getElementById('serviceForm').action = '{{ route("intranet.annuaire.services.store") }}';
});
</script>
@endpush
@endsection
