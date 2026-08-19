@extends('layouts.app')
@section('title', 'Transfert stock — ' . $article->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.index') }}">Catalogue</a></li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.show', $article) }}">{{ $article->designation }}</a></li>
    <li class="breadcrumb-item active">Transfert stock</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-shuffle text-muted me-2"></i> Transfert entre emplacements</h1>
        <p class="text-muted mb-0">
            <strong>{{ $article->designation }}</strong>
            @if($article->code)<code class="small ms-1">{{ $article->code }}</code>@endif
            · Stock total : <strong>{{ number_format((float) $article->stock_actuel, 3, ',', ' ') }}</strong>{{ $article->unite_mesure ? ' ' . $article->unite_mesure : '' }}
        </p>
    </div>
    <a href="{{ route('referentiel.catalogue.show', $article) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour à la fiche</a>
</div>

<div class="alert alert-info small">
    <i class="fas fa-info-circle me-1"></i>
    Le transfert déplace du stock d'un emplacement vers un autre <strong>sans modifier le total</strong>. Un mouvement de type <em>transfert</em> est créé pour tracer l'opération.
</div>

<form action="{{ route('referentiel.catalogue.transferer-stock', $article) }}" method="POST">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-pen me-2"></i> Détail du transfert</h6></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Emplacement source <span class="text-danger">*</span></label>
                        <select name="emplacement_source_id" class="form-select" required>
                            <option value="">— Sélectionner</option>
                            @foreach($article->stocksParEmplacement->where('quantite', '>', 0) as $s)
                                <option value="{{ $s->emplacement_id }}" @selected(old('emplacement_source_id') == $s->emplacement_id)>
                                    {{ $s->emplacement?->chemin }} (disponible : {{ number_format((float) $s->quantite, 3, ',', ' ') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Emplacement destination <span class="text-danger">*</span></label>
                        <select name="emplacement_dest_id" class="form-select" required>
                            <option value="">— Sélectionner</option>
                            @foreach($emplacements as $e)
                                <option value="{{ $e->id }}" @selected(old('emplacement_dest_id') == $e->id)>{{ $e->chemin }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Doit être différent de la source.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantité à transférer <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0.001" name="quantite" class="form-control text-end"
                               value="{{ old('quantite') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Motif (facultatif)</label>
                        <input type="text" name="motif" class="form-control" value="{{ old('motif') }}" maxlength="255" placeholder="Ex : Réapprovisionnement atelier">
                    </div>
                </div></div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Confirmer le transfert</button>
                <a href="{{ route('referentiel.catalogue.show', $article) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-warehouse me-2"></i> Stock actuel par emplacement</h6></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @foreach($article->stocksParEmplacement as $s)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $s->emplacement?->libelle ?? '—' }}
                                    @if($article->emplacement_id === $s->emplacement_id)<span class="badge bg-primary ms-1">★</span>@endif
                                </span>
                                <strong>{{ number_format((float) $s->quantite, 3, ',', ' ') }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
