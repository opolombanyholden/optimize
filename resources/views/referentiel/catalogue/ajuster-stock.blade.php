@extends('layouts.app')
@section('title', 'Ajustement stock — ' . $article->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.index') }}">Catalogue</a></li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.show', $article) }}">{{ $article->designation }}</a></li>
    <li class="breadcrumb-item active">Ajustement stock</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-plus-minus text-muted me-2"></i> Ajustement manuel de stock</h1>
        <p class="text-muted mb-0">
            <strong>{{ $article->designation }}</strong>
            @if($article->code)<code class="small ms-1">{{ $article->code }}</code>@endif
            · Stock total actuel : <strong>{{ number_format((float) $article->stock_actuel, 3, ',', ' ') }}</strong>{{ $article->unite_mesure ? ' ' . $article->unite_mesure : '' }}
        </p>
    </div>
    <a href="{{ route('referentiel.catalogue.show', $article) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour à la fiche</a>
</div>

<div class="alert alert-info small">
    <i class="fas fa-info-circle me-1"></i>
    Un ajustement génère un mouvement de stock <strong>traçable</strong> (type <em>ajustement</em>). Utilisez cette action pour corriger le stock d'un emplacement (casse, don, retour, correction spontanée…).
    Pour les mouvements liés à une commande ou une livraison, utilisez plutôt les workflows dédiés.
</div>

<form action="{{ route('referentiel.catalogue.ajuster-stock', $article) }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-pen me-2"></i> Détail de l'ajustement</h6></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Emplacement <span class="text-danger">*</span></label>
                        <select name="emplacement_id" class="form-select" required>
                            <option value="">— Sélectionner un emplacement</option>
                            @foreach($emplacements as $e)
                                <option value="{{ $e->id }}"
                                        @selected(old('emplacement_id', $article->emplacement_id) == $e->id)>
                                    {{ $e->chemin }} — actuellement : {{ number_format($article->stockDans($e->id), 3, ',', ' ') }} {{ $article->unite_mesure }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Sélectionnez l'emplacement où appliquer l'ajustement.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delta (± quantité) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" name="delta" class="form-control text-end"
                               value="{{ old('delta') }}" required placeholder="+5 ou -2">
                        <small class="text-muted">Positif = entrée. Négatif = sortie.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Motif de l'ajustement <span class="text-danger">*</span></label>
                        <input type="text" name="motif" class="form-control"
                               value="{{ old('motif') }}" required minlength="3" maxlength="255"
                               placeholder="Ex : Correction inventaire spontané / Casse constatée / Retour agent…">
                    </div>
                </div></div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Enregistrer l'ajustement</button>
                <a href="{{ route('referentiel.catalogue.show', $article) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-warehouse me-2"></i> Stock actuel par emplacement</h6></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($article->stocksParEmplacement as $s)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $s->emplacement?->libelle ?? '—' }}
                                    @if($article->emplacement_id === $s->emplacement_id)<span class="badge bg-primary ms-1">★</span>@endif
                                </span>
                                <strong>{{ number_format((float) $s->quantite, 3, ',', ' ') }}</strong>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">Aucun stock ventilé.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
