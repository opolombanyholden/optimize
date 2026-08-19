@extends('layouts.app')
@section('title', 'Nouvel ordre')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.ordres.index') }}">Dépenses & Recettes</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouvel ordre</h1>
    <p class="text-muted mb-0">Choisissez d'abord le modèle : le formulaire s'adapte aux champs configurés par l'admin.</p>
</div>

{{-- ═════════ ÉTAPE 1 : SÉLECTION DU MODÈLE ═════════ --}}
@if(!$modele)
    <div class="row g-3">
        @forelse($modelesActifs as $m)
            <div class="col-md-6">
                <a href="{{ route('finance.ordres.create', ['modele' => $m->id]) }}" class="card data-card text-decoration-none h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">{{ $m->libelle }}</h5>
                                @if($m->entete_titre)<small class="text-muted d-block">{{ $m->entete_titre }}</small>@endif
                            </div>
                            <span class="badge bg-{{ $m->sens_couleur }}">
                                @if($m->sens === 'depense')<i class="fas fa-arrow-down me-1"></i>Dépense
                                @else<i class="fas fa-arrow-up me-1"></i>Recette
                                @endif
                            </span>
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-list-check me-1"></i> {{ $m->champs()->count() }} champ(s) ·
                            <i class="fas fa-signature ms-2 me-1"></i> {{ $m->signataires()->count() }} signataire(s)
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-warning">
                <i class="fas fa-triangle-exclamation me-2"></i>
                Aucun modèle actif. Créez-en un dans
                <a href="{{ route('finance.referentiels.ordres-modeles.index') }}" class="alert-link">Référentiel → Modèles d'ordre</a>.
            </div></div>
        @endforelse
    </div>
    <div class="mt-3">
        <a href="{{ route('finance.ordres.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>
@else
    {{-- ═════════ ÉTAPE 2 : FORMULAIRE DYNAMIQUE ═════════ --}}
    <form action="{{ route('finance.ordres.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="modele_id" value="{{ $modele->id }}">
        @include('finance.ordres._form', ['modele' => $modele, 'ordre' => $ordre, 'exercices' => $exercices])
    </form>
@endif
@endsection
