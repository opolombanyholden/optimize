@extends('layouts.app')
@section('title', 'Annuaire — Équipes')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item active">Équipes</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #7C3AED;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#7C3AED,#5B21B6);"><i class="fas fa-people-group"></i></span>
                Annuaire — Équipes
            </h1>
            <p class="page-subtitle">{{ $equipes->count() }} équipes · Groupes transversaux</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" class="btn btn-light"><i class="fas fa-users me-1"></i> Collaborateurs</a>
            <a href="{{ route('intranet.annuaire.services.index') }}" class="btn btn-light"><i class="fas fa-building me-1"></i> Services</a>
            <button class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);" data-bs-toggle="modal" data-bs-target="#modalEquipe">
                <i class="fas fa-plus me-2"></i> Nouvelle équipe
            </button>
        </div>
    </div>

    @if($equipes->count())
    <div class="row g-3">
        @foreach($equipes as $e)
        <div class="col-md-4">
            <div class="form-card h-100" style="border-left:4px solid {{ $e->couleur ?? '#7C3AED' }};">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div style="width:40px;height:40px;border-radius:8px;background:{{ $e->couleur ?? '#7C3AED' }};display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                        <i class="fas {{ $e->icone ?? 'fa-people-group' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('intranet.annuaire.equipes.show', $e) }}" style="text-decoration:none;color:inherit;">
                            <strong style="font-size:.9rem;color:#0F172A;">{{ $e->nom }}</strong>
                        </a>
                        <div style="font-size:.65rem;color:#94A3B8;">par {{ $e->auteur?->prenoms ?? '—' }} · {{ $e->created_at->format('d/m/Y') }}</div>
                    </div>
                    <div class="dropdown">
                        <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                            <li><a class="dropdown-item" href="{{ route('intranet.annuaire.equipes.show', $e) }}"><i class="fas fa-eye"></i> Voir</a></li>
                            <li><button class="dropdown-item" onclick='editEquipe(@json($e))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('intranet.annuaire.equipes.destroy', $e) }}" method="POST" onsubmit="return confirm('Supprimer cette équipe ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form></li>
                        </ul>
                    </div>
                </div>
                @if($e->description)<p style="font-size:.75rem;color:#64748B;margin-bottom:.5rem;">{{ Str::limit($e->description, 90) }}</p>@endif
                <div style="font-size:.7rem;color:#475569;">
                    <i class="fas fa-users me-1"></i>{{ $e->membres_count ?? 0 }} membre(s)
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F5F3FF;"><i class="fas fa-people-group" style="color:#7C3AED;"></i></div>
        <h3>Aucune équipe</h3>
        <p>Créez des équipes transversales pour grouper des collaborateurs (taskforce, comité, communauté de pratique...)</p>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);" data-bs-toggle="modal" data-bs-target="#modalEquipe">
            <i class="fas fa-plus me-2"></i> Créer la première
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalEquipe" tabindex="-1">
    <div class="modal-dialog">
        <form id="equipeForm" method="POST" class="modal-content" action="{{ route('intranet.annuaire.equipes.store') }}">
            @csrf
            <input type="hidden" name="_method" id="equipeMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="equipeModalTitle">Nouvelle équipe</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="eqNom" class="form-control" required placeholder="Comité de direction, Taskforce migration..."></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="eqDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="eqCouleur" class="form-control form-control-color" value="#7C3AED"></div>
                    <div class="col-md-8"><label class="form-label">Icône</label>
                        <input type="text" name="icone" id="eqIcone" class="form-control" placeholder="fa-people-group"></div>
                </div>
                <div class="mt-3" id="eqMembresWrap">
                    <label class="form-label">Membres initiaux</label>
                    <select name="membres[]" class="form-select" multiple size="6">
                        @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                    </select>
                    <small class="form-hint">Ctrl+clic pour sélection multiple</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);"><span id="eqSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editEquipe(e) {
    document.getElementById('equipeModalTitle').textContent = 'Modifier l\'équipe';
    document.getElementById('eqNom').value = e.nom || '';
    document.getElementById('eqDesc').value = e.description || '';
    document.getElementById('eqCouleur').value = e.couleur || '#7C3AED';
    document.getElementById('eqIcone').value = e.icone || '';
    document.getElementById('eqMembresWrap').style.display = 'none';
    document.getElementById('equipeMethod').value = 'PUT';
    document.getElementById('eqSubmitText').textContent = 'Enregistrer';
    document.getElementById('equipeForm').action = '/intranet/annuaire/equipes/' + e.id;
    new bootstrap.Modal(document.getElementById('modalEquipe')).show();
}

document.getElementById('modalEquipe')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('equipeModalTitle').textContent = 'Nouvelle équipe';
    ['eqNom','eqDesc','eqIcone'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('eqCouleur').value = '#7C3AED';
    document.getElementById('eqMembresWrap').style.display = 'block';
    document.getElementById('equipeMethod').value = 'POST';
    document.getElementById('eqSubmitText').textContent = 'Créer';
    document.getElementById('equipeForm').action = '{{ route("intranet.annuaire.equipes.store") }}';
});
</script>
@endpush
@endsection
