@extends('layouts.app')
@section('title', $projet->nom . ' — Parties prenantes')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Parties prenantes</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'parties-prenantes'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-users-rectangle"></i></span>
                Parties prenantes — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Registre des parties prenantes et analyse de leur engagement</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalPartie">
            <i class="fas fa-plus me-2"></i> Ajouter une partie prenante
        </button>
    </div>

    @if($partiesPrenantes->count())
    <div class="row g-3">
        @foreach($partiesPrenantes as $pp)
        @php
            $scorePouvoir = $pp->interet * $pp->influence;
            $categorieColors = ['interne' => '#0D9488', 'externe' => '#6366F1', 'cle' => '#DC2626', 'secondaire' => '#6B7280'];
            $engagementLabels = [1 => 'Résistant', 2 => 'Neutre', 3 => 'Supporteur', 4 => 'Champion', 5 => 'Leader'];
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="contact-detail-card" style="border-top:3px solid {{ $categorieColors[$pp->categorie] ?? '#0D9488' }};">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1" style="font-size:.95rem;">{{ $pp->nom }}</h5>
                        <small class="text-muted">{{ $pp->organisation ?? '' }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="opp-stage-badge" style="background:{{ $categorieColors[$pp->categorie] ?? '#6B7280' }}; font-size:.7rem;">
                            {{ ucfirst($pp->categorie) }}
                        </span>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editPartie({{ $pp->id }}, {{ json_encode([
                                    'nom' => $pp->nom, 'organisation' => $pp->organisation, 'role' => $pp->role,
                                    'categorie' => $pp->categorie, 'interet' => $pp->interet, 'influence' => $pp->influence,
                                    'engagement_actuel' => $pp->engagement_actuel, 'engagement_desire' => $pp->engagement_desire,
                                    'strategie_engagement' => $pp->strategie_engagement, 'email' => $pp->email, 'telephone' => $pp->telephone
                                ]) }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.parties-prenantes.destroy', [$projet, $pp]) }}" method="POST" onsubmit="return confirm('Retirer cette partie prenante ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if($pp->role)
                <div class="mb-2" style="font-size:.82rem;"><i class="fas fa-user-tag me-1" style="color:#0D9488;"></i> {{ $pp->role }}</div>
                @endif

                <div class="row g-2 mb-2" style="font-size:.8rem;">
                    <div class="col-4 text-center">
                        <div class="text-muted" style="font-size:.7rem;">Intérêt</div>
                        <div style="font-weight:600; color:#0D9488;">{{ $pp->interet }}/5</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-muted" style="font-size:.7rem;">Influence</div>
                        <div style="font-weight:600; color:#0F766E;">{{ $pp->influence }}/5</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-muted" style="font-size:.7rem;">Score pouvoir</div>
                        <div style="font-weight:600; color:{{ $scorePouvoir >= 16 ? '#DC2626' : ($scorePouvoir >= 9 ? '#F59E0B' : '#0891B2') }};">{{ $scorePouvoir }}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center" style="font-size:.78rem; padding-top:8px; border-top:1px solid #E5E7EB;">
                    <div>
                        <span class="text-muted">Actuel:</span>
                        <span class="opp-stage-badge" style="background:{{ match($pp->engagement_actuel) {
                            1 => '#DC2626', 2 => '#F59E0B', 3 => '#0891B2', 4 => '#16A34A', 5 => '#0D9488', default => '#6B7280'
                        } }}; font-size:.68rem;">{{ $engagementLabels[$pp->engagement_actuel] ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-muted">Désiré:</span>
                        <span class="opp-stage-badge" style="background:{{ match($pp->engagement_desire) {
                            1 => '#DC2626', 2 => '#F59E0B', 3 => '#0891B2', 4 => '#16A34A', 5 => '#0D9488', default => '#6B7280'
                        } }}; font-size:.68rem;">{{ $engagementLabels[$pp->engagement_desire] ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-users-rectangle" style="color:#0D9488;"></i></div>
        <h3>Aucune partie prenante</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalPartie">
            <i class="fas fa-plus me-2"></i> Ajouter
        </button>
    </div>
    @endif
</div>

{{-- Modale partie prenante --}}
<div class="modal fade" id="modalPartie" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="partieForm" method="POST" class="modal-content" action="{{ route('projet.parties-prenantes.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="partieMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="partieModalTitle">Ajouter une partie prenante</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="partieNom" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Organisation</label>
                        <input type="text" name="organisation" id="partieOrg" class="form-control"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Rôle</label>
                        <input type="text" name="role" id="partieRole" class="form-control" placeholder="Sponsor, Utilisateur final..."></div>
                    <div class="col-md-6"><label class="form-label">Catégorie</label>
                        <select name="categorie" id="partieCategorie" class="form-select">
                            <option value="interne">Interne</option>
                            <option value="externe">Externe</option>
                            <option value="cle">Clé</option>
                            <option value="secondaire">Secondaire</option>
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3"><label class="form-label">Intérêt (1-5) <span class="text-danger">*</span></label>
                        <select name="interet" id="partieInteret" class="form-select" required>
                            @for($i=1; $i<=5; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Influence (1-5) <span class="text-danger">*</span></label>
                        <select name="influence" id="partieInfluence" class="form-select" required>
                            @for($i=1; $i<=5; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Engagement actuel</label>
                        <select name="engagement_actuel" id="partieEngActuel" class="form-select">
                            <option value="">—</option>
                            <option value="1">1 - Résistant</option>
                            <option value="2">2 - Neutre</option>
                            <option value="3">3 - Supporteur</option>
                            <option value="4">4 - Champion</option>
                            <option value="5">5 - Leader</option>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Engagement désiré</label>
                        <select name="engagement_desire" id="partieEngDesire" class="form-select">
                            <option value="">—</option>
                            <option value="1">1 - Résistant</option>
                            <option value="2">2 - Neutre</option>
                            <option value="3">3 - Supporteur</option>
                            <option value="4">4 - Champion</option>
                            <option value="5">5 - Leader</option>
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Email</label>
                        <input type="email" name="email" id="partieEmail" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" id="partieTel" class="form-control"></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Stratégie d'engagement</label>
                    <textarea name="strategie_engagement" id="partieStrategie" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-users me-2"></i> <span id="partieSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editPartie(id, data) {
    document.getElementById('partieModalTitle').textContent = 'Modifier la partie prenante';
    document.getElementById('partieNom').value = data.nom;
    document.getElementById('partieOrg').value = data.organisation || '';
    document.getElementById('partieRole').value = data.role || '';
    document.getElementById('partieCategorie').value = data.categorie || 'interne';
    document.getElementById('partieInteret').value = data.interet;
    document.getElementById('partieInfluence').value = data.influence;
    document.getElementById('partieEngActuel').value = data.engagement_actuel || '';
    document.getElementById('partieEngDesire').value = data.engagement_desire || '';
    document.getElementById('partieEmail').value = data.email || '';
    document.getElementById('partieTel').value = data.telephone || '';
    document.getElementById('partieStrategie').value = data.strategie_engagement || '';
    document.getElementById('partieMethod').value = 'PUT';
    document.getElementById('partieSubmitText').textContent = 'Enregistrer';
    document.getElementById('partieForm').action = '/projet/{{ $projet->id }}/parties-prenantes/' + id;
    new bootstrap.Modal(document.getElementById('modalPartie')).show();
}

document.getElementById('modalPartie')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('partieModalTitle').textContent = 'Ajouter une partie prenante';
    ['partieNom','partieOrg','partieRole','partieEmail','partieTel','partieStrategie'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('partieCategorie').value = 'interne';
    document.getElementById('partieInteret').value = '1';
    document.getElementById('partieInfluence').value = '1';
    document.getElementById('partieEngActuel').value = '';
    document.getElementById('partieEngDesire').value = '';
    document.getElementById('partieMethod').value = 'POST';
    document.getElementById('partieSubmitText').textContent = 'Enregistrer';
    document.getElementById('partieForm').action = '{{ route("projet.parties-prenantes.store", $projet) }}';
});
</script>
@endpush
@endsection
