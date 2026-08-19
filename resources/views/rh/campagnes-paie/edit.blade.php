@extends('layouts.app')

@section('title', 'Édition campagne — ' . $campagne->code)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.campagnes-paie.index') }}">Campagnes de paie</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.campagnes-paie.show', $campagne) }}">{{ $campagne->code }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Éditer la campagne « {{ $campagne->code }} »</h1>
        <p class="text-muted mb-0">Période : {{ $campagne->periode_libelle }} ·
            <span class="badge bg-{{ $campagne->statut_couleur }}">{{ $campagne->statut_libelle }}</span>
        </p>
    </div>
    <a href="{{ route('rh.campagnes-paie.show', $campagne) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle me-1"></i>
            Seuls le libellé, le commentaire et la date de paiement prévue sont modifiables.
            Pour changer la période, la périodicité ou les employés inclus, créez une nouvelle campagne.
        </div>

        <form action="{{ route('rh.campagnes-paie.update', $campagne) }}" method="POST">
            @csrf @method('PUT')

            <div class="card data-card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="libelle" required maxlength="255"
                               class="form-control @error('libelle') is-invalid @enderror"
                               value="{{ old('libelle', $campagne->libelle) }}">
                        @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_paiement_prevue" class="form-label">Date de paiement prévue</label>
                        <input type="date" name="date_paiement_prevue" id="date_paiement_prevue"
                               class="form-control @error('date_paiement_prevue') is-invalid @enderror"
                               value="{{ old('date_paiement_prevue', $campagne->date_paiement_prevue?->toDateString()) }}">
                        @error('date_paiement_prevue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea name="commentaire" id="commentaire" rows="4" maxlength="2000"
                                  class="form-control @error('commentaire') is-invalid @enderror"
                                  placeholder="Notes internes, contexte…">{{ old('commentaire', $campagne->commentaire) }}</textarea>
                        @error('commentaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <div class="text-muted small">
                        Champs verrouillés :
                        {{ $campagne->annee }}/{{ str_pad($campagne->mois, 2, '0', STR_PAD_LEFT) }} ·
                        {{ $campagne->periodicite }} ·
                        {{ $campagne->simulation ? 'simulation' : 'réelle' }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('rh.campagnes-paie.show', $campagne) }}" class="btn btn-outline-secondary">Annuler</a>
                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Mettre à jour</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
