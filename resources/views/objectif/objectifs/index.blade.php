@extends('layouts.app')
@section('title', 'Objectifs')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item active">{{ $type === 'strategique' ? 'Stratégiques' : ($type === 'operationnel' ? 'Opérationnels' : 'Tous') }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #DB2777;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#DB2777,#BE185D);"><i class="fas fa-bullseye"></i></span>
                @if($type === 'strategique') Objectifs stratégiques
                @elseif($type === 'operationnel') Objectifs opérationnels
                @else Tous les objectifs
                @endif
            </h1>
            <p class="page-subtitle">{{ $objectifs->count() }} objectif(s)</p>
        </div>
        <div class="d-flex gap-2">
            <div class="btn-group">
                <a href="{{ route('objectifs.objectifs.index') }}" class="btn btn-sm {{ ! $type ? 'btn-dark' : 'btn-light' }}">Tous</a>
                <a href="{{ route('objectifs.objectifs.index', ['type' => 'strategique']) }}" class="btn btn-sm {{ $type === 'strategique' ? 'btn-dark' : 'btn-light' }}">Stratégiques</a>
                <a href="{{ route('objectifs.objectifs.index', ['type' => 'operationnel']) }}" class="btn btn-sm {{ $type === 'operationnel' ? 'btn-dark' : 'btn-light' }}">Opérationnels</a>
            </div>
            <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalObjectif">
                <i class="fas fa-plus me-2"></i> Nouvel objectif
            </button>
            <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtresAvances" aria-expanded="false">
                <i class="fas fa-filter me-1"></i> Filtres
                @php
                    $filtresActifs = collect(['q','statut','portee','responsable_id','service_id','date_du','date_au','en_retard'])
                        ->filter(fn($k) => request($k) !== null && request($k) !== '')->count();
                @endphp
                @if($filtresActifs > 0)
                    <span class="badge bg-danger ms-1">{{ $filtresActifs }}</span>
                @endif
            </button>
        </div>
    </div>

    {{-- Panneau filtres avancés (repliable) --}}
    <div class="collapse mt-3 {{ $filtresActifs > 0 ? 'show' : '' }}" id="filtresAvances">
        <div class="contact-detail-card">
            <form method="GET" class="row g-3">
                @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                <div class="col-md-4">
                    <label class="form-label small">Recherche</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Titre, code, description…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach(['actif'=>'En cours','atteint'=>'Atteint','non_atteint'=>'Non atteint','abandonne'=>'Abandonné'] as $k => $lib)
                            <option value="{{ $k }}" @selected(request('statut') === $k)>{{ $lib }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Portée</label>
                    <select name="portee" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach(['organisation'=>'Organisation','service'=>'Service','equipe'=>'Équipe','individuel'=>'Individuel'] as $k => $lib)
                            <option value="{{ $k }}" @selected(request('portee') === $k)>{{ $lib }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Responsable</label>
                    <select name="responsable_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('responsable_id') == $u->id)>{{ $u->prenoms }} {{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Service</label>
                    <select name="service_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" @selected(request('service_id') == $s->id)>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Échéance du</label>
                    <input type="date" name="date_du" class="form-control form-control-sm" value="{{ request('date_du') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">au</label>
                    <input type="date" name="date_au" class="form-control form-control-sm" value="{{ request('date_au') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="en_retard" value="1" class="form-check-input" id="enRetard" @checked(request()->boolean('en_retard'))>
                        <label class="form-check-label small" for="enRetard">En retard uniquement</label>
                    </div>
                </div>
                <div class="col-md-5 d-flex align-items-end justify-content-end gap-2">
                    <a href="{{ route('objectifs.objectifs.index', $type ? ['type' => $type] : []) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-times me-1"></i> Réinitialiser
                    </a>
                    <button class="btn btn-sm btn-dark"><i class="fas fa-search me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    @if($objectifs->count())
    <div class="row g-3">
        @foreach($objectifs as $o)
        <div class="col-md-6 col-lg-4">
            <div class="form-card h-100" style="border-left:4px solid {{ $o->couleur_affichage }};">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $o->couleur_affichage }};display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                        <i class="fas {{ $o->icone_affichage }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('objectifs.objectifs.show', $o) }}" style="text-decoration:none;color:inherit;">
                            <strong style="font-size:.85rem;color:#0F172A;">{{ $o->titre }}</strong>
                        </a>
                        @if($o->code)<div><code style="font-size:.65rem;">{{ $o->code }}</code></div>@endif
                    </div>
                    <div class="dropdown">
                        <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                            <li><a class="dropdown-item" href="{{ route('objectifs.objectifs.show', $o) }}"><i class="fas fa-eye"></i> Voir</a></li>
                            <li><button class="dropdown-item" onclick='editObjectif(@json($o))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('objectifs.objectifs.destroy', $o) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form></li>
                        </ul>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="opp-stage-badge" style="background:{{ $o->statut_couleur }};font-size:.6rem;">{{ $o->statut_libelle }}</span>
                    <span class="opp-stage-badge" style="background:#7C3AED;font-size:.6rem;">{{ $o->portee_libelle }}</span>
                    @if($o->parent)<span class="opp-stage-badge" style="background:#94A3B8;font-size:.6rem;">↳ {{ Str::limit($o->parent->titre, 20) }}</span>@endif
                    @if($o->estEnRetard())<span class="opp-stage-badge" style="background:#DC2626;font-size:.55rem;">Retard</span>@endif
                </div>

                @if($o->description)<p style="font-size:.72rem;color:#64748B;margin-bottom:.5rem;">{{ Str::limit($o->description, 80) }}</p>@endif

                <div class="tache-progress-bar mb-2">
                    <div class="tache-progress-fill" style="width:{{ $o->progression }}%;background:{{ $o->couleur_affichage }};"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center" style="font-size:.7rem;color:#475569;">
                    <span><strong>{{ $o->progression }}%</strong> de progression</span>
                    @if($o->date_fin)<span><i class="far fa-calendar"></i> {{ $o->date_fin->format('d/m/Y') }}</span>@endif
                </div>

                <div class="d-flex gap-2 mt-2" style="font-size:.65rem;color:#94A3B8;">
                    @if($o->responsable)<span><i class="fas fa-user"></i> {{ $o->responsable->prenoms }}</span>@endif
                    <span><i class="fas fa-bullseye"></i> {{ $o->sousObjectifs->count() }}</span>
                    <span><i class="fas fa-chart-line"></i> {{ $o->kpi->count() }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#FCE7F3;"><i class="fas fa-bullseye" style="color:#DB2777;"></i></div>
        <h3>Aucun objectif</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalObjectif">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalObjectif" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="objForm" method="POST" class="modal-content" action="{{ route('objectifs.objectifs.store') }}">
            @csrf
            <input type="hidden" name="_method" id="objMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="objModalTitle">Nouvel objectif</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-9"><label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" id="objTitre" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Code</label>
                        <input type="text" name="code" id="objCode" class="form-control" maxlength="30" placeholder="OKR-2026-01"></div>
                </div>
                <div class="mb-3 mt-2"><label class="form-label">Description</label>
                    <textarea name="description" id="objDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Portée <span class="text-danger">*</span></label>
                        <select name="portee" id="objPortee" class="form-select" required>
                            <option value="organisation">Organisation</option>
                            <option value="service">Service</option>
                            <option value="equipe">Équipe</option>
                            <option value="individuel">Individuel</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Statut <span class="text-danger">*</span></label>
                        <select name="statut" id="objStatut" class="form-select" required>
                            <option value="actif">En cours</option>
                            <option value="atteint">Atteint</option>
                            <option value="non_atteint">Non atteint</option>
                            <option value="abandonne">Abandonné</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Pondération (%)</label>
                        <input type="number" name="ponderation" id="objPond" class="form-control" min="0" max="100"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Objectif parent</label>
                        <select name="parent_id" id="objParent" class="form-select">
                            <option value="">— Stratégique (racine) —</option>
                            @foreach($strategiques as $s)<option value="{{ $s->id }}">{{ $s->titre }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Service</label>
                        <select name="service_id" id="objService" class="form-select">
                            <option value="">—</option>
                            @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="objResp" class="form-select">
                            <option value="">—</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>@endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Date début</label>
                        <input type="date" name="date_debut" id="objDateDebut" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Échéance</label>
                        <input type="date" name="date_fin" id="objDateFin" class="form-control"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="objCouleur" class="form-control form-control-color" value="#DB2777"></div>
                    <div class="col-md-6"><label class="form-label">Icône</label>
                        <input type="text" name="icone" id="objIcone" class="form-control" placeholder="fa-bullseye"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);"><span id="objSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editObjectif(o) {
    document.getElementById('objModalTitle').textContent = 'Modifier l\'objectif';
    document.getElementById('objTitre').value = o.titre || '';
    document.getElementById('objCode').value = o.code || '';
    document.getElementById('objDesc').value = o.description || '';
    document.getElementById('objPortee').value = o.portee || 'organisation';
    document.getElementById('objStatut').value = o.statut || 'actif';
    document.getElementById('objPond').value = o.ponderation || '';
    document.getElementById('objParent').value = o.parent_id || '';
    document.getElementById('objService').value = o.service_id || '';
    document.getElementById('objResp').value = o.responsable_id || '';
    document.getElementById('objDateDebut').value = o.date_debut?.substring(0, 10) || '';
    document.getElementById('objDateFin').value = o.date_fin?.substring(0, 10) || '';
    document.getElementById('objCouleur').value = o.couleur || '#DB2777';
    document.getElementById('objIcone').value = o.icone || '';
    document.getElementById('objMethod').value = 'PUT';
    document.getElementById('objSubmitText').textContent = 'Enregistrer';
    document.getElementById('objForm').action = '/objectifs/objectifs/' + o.id;
    new bootstrap.Modal(document.getElementById('modalObjectif')).show();
}

document.getElementById('modalObjectif')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('objModalTitle').textContent = 'Nouvel objectif';
    ['objTitre','objCode','objDesc','objPond','objDateDebut','objDateFin','objIcone'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('objCouleur').value = '#DB2777';
    document.getElementById('objPortee').value = 'organisation';
    document.getElementById('objStatut').value = 'actif';
    document.getElementById('objParent').value = '';
    document.getElementById('objService').value = '';
    document.getElementById('objResp').value = '';
    document.getElementById('objMethod').value = 'POST';
    document.getElementById('objSubmitText').textContent = 'Créer';
    document.getElementById('objForm').action = '{{ route("objectifs.objectifs.store") }}';
});
</script>
@endpush
@endsection
