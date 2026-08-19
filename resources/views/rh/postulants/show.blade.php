@extends('layouts.app')

@section('title', $postulant->noms . ' ' . $postulant->prenoms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.postulants.index') }}">Candidatures</a></li>
        <li class="breadcrumb-item active">{{ $postulant->noms }} {{ $postulant->prenoms }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $postulant->noms }} {{ $postulant->prenoms }}</h1>
        <p class="text-muted mb-0">
            {{ $postulant->email }}
            @if($postulant->contact) · {{ $postulant->contact }}@endif
            · <span class="badge bg-{{ $statutCouleurs[$postulant->statut] ?? 'secondary' }}">{{ $statuts[$postulant->statut] ?? '—' }}</span>
            @if($postulant->embauche)
                <span class="badge bg-success ms-1"><i class="fas fa-user-check me-1"></i>Embauché</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.postulants.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @can('update:postulant')
            <a href="{{ route('rh.postulants.edit', $postulant) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card mb-3">
            <div class="card-header"><strong><i class="fas fa-id-card me-1"></i> Profil candidat</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Âge</dt>
                    <dd class="col-sm-8">{{ $postulant->age ?: '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Date de naissance</dt>
                    <dd class="col-sm-8">{{ $postulant->date_naissance ?: '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Campagne</dt>
                    <dd class="col-sm-8">
                        @if($postulant->recrutement)
                            <a href="{{ route('rh.recrutements.show', $postulant->recrutement) }}">{{ $postulant->recrutement->label }}</a>
                        @else
                            <span class="text-muted">Candidature spontanée</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4 text-muted">Profil postulé</dt>
                    <dd class="col-sm-8">{{ $postulant->profil?->label ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Reçu le</dt>
                    <dd class="col-sm-8">{{ $postulant->created_at?->translatedFormat('d M Y H:i') }}</dd>

                    @if($postulant->fichiersjoin)
                        <dt class="col-sm-4 text-muted">Fichiers</dt>
                        <dd class="col-sm-8"><small>{{ $postulant->fichiersjoin }}</small></dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($postulant->embauche)
            <div class="card data-card border-success mb-3">
                <div class="card-header bg-success text-white">
                    <strong><i class="fas fa-user-check me-1"></i> Embauche réalisée</strong>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Employé créé :</strong>
                        @if($postulant->embauche->employee)
                            <a href="{{ route('rh.employees.show', $postulant->embauche->employee) }}">
                                {{ $postulant->embauche->employee->noms }} {{ $postulant->embauche->employee->prenoms }}
                                ({{ $postulant->embauche->employee->matricule }})
                            </a>
                        @else
                            <span class="text-muted">Fiche supprimée</span>
                        @endif
                    </p>
                    <p class="text-muted small mb-0">Embauche #{{ $postulant->embauche->id }} créée le {{ $postulant->embauche->created_at?->format('d/m/Y') }}.</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        {{-- Changement rapide de statut --}}
        @can('update:postulant')
        <div class="card data-card mb-3">
            <div class="card-header"><strong><i class="fas fa-route me-1"></i> Workflow</strong></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Faire évoluer la candidature :</p>
                <form action="{{ route('rh.postulants.statut', $postulant) }}" method="POST" class="d-flex gap-2 align-items-center">
                    @csrf
                    <select name="statut" class="form-select form-select-sm">
                        @foreach($statuts as $code => $lib)
                            <option value="{{ $code }}" @selected($postulant->statut === $code)>{{ $lib }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">Appliquer</button>
                </form>
            </div>
        </div>
        @endcan

        {{-- Embaucher (si retenu) --}}
        @can('create:employee')
        @if($postulant->statut === 2 && !$postulant->embauche)
            <div class="card data-card border-success">
                <div class="card-header bg-success text-white">
                    <strong><i class="fas fa-user-plus me-1"></i> Procéder à l'embauche</strong>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Cette action crée :
                    <ul class="small text-muted ps-3">
                        <li>une fiche employé liée au candidat ;</li>
                        <li>un compte utilisateur (le candidat devra définir son mot de passe) ;</li>
                        <li>une trace d'embauche (auditable).</li>
                    </ul>
                    </p>
                    <form action="{{ route('rh.postulants.embaucher', $postulant) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small mb-1">Matricule <span class="text-danger">*</span></label>
                            <input type="text" name="matricule" class="form-control form-control-sm" required maxlength="50" placeholder="Ex : EMP-042">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Date d'embauche <span class="text-danger">*</span></label>
                            <input type="date" name="date_embauche" class="form-control form-control-sm" required value="{{ now()->toDateString() }}">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Type contrat <span class="text-danger">*</span></label>
                                <select name="type_contrat" class="form-select form-select-sm" required>
                                    <option value="CDI">CDI</option>
                                    <option value="CDD">CDD</option>
                                    <option value="Stage">Stage</option>
                                    <option value="Mission">Mission</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Salaire base (XAF) <span class="text-danger">*</span></label>
                                <input type="number" name="salaire_base" class="form-control form-control-sm" required min="0" step="1000" placeholder="500000">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Poste</label>
                                <input type="text" name="poste" class="form-control form-control-sm" maxlength="255">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">Département</label>
                                <input type="text" name="departement" class="form-control form-control-sm" maxlength="255">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Commentaire</label>
                            <textarea name="commentaire" rows="2" class="form-control form-control-sm" maxlength="500"></textarea>
                        </div>
                        <button class="btn btn-success w-100"
                                onclick="return confirm('Embaucher ce candidat ? Un employé et un compte utilisateur seront créés.');">
                            <i class="fas fa-user-plus me-1"></i> Embaucher
                        </button>
                    </form>
                </div>
            </div>
        @elseif($postulant->statut !== 2 && !$postulant->embauche)
            <div class="alert alert-info small mb-0">
                <i class="fas fa-info-circle me-1"></i> Pour embaucher ce candidat, faites d'abord évoluer son statut vers <strong>« Retenu »</strong>.
            </div>
        @endif
        @endcan
    </div>
</div>
@endsection
