@extends('layouts.app')
@section('title', $rubrique->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><code>{{ $rubrique->code }}</code> {{ $rubrique->libelle }}</h1>
        <p class="text-muted mb-0">
            Ligne : <strong>{{ $rubrique->ligne?->libelle ?? '—' }}</strong> ·
            Sens : {{ $rubrique->sens_libelle }} ·
            @if($rubrique->statut)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Archivé</span>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.referentiels.rubriques.index') }}" class="btn btn-outline-secondary">Retour</a>
        @can('update:rubrique_operation')<a href="{{ route('finance.referentiels.rubriques.edit', $rubrique) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>@endcan
    </div>
</div>

@if($rubrique->description)<div class="alert alert-light border">{{ $rubrique->description }}</div>@endif

<div class="card data-card">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3 text-muted">Catégorie</dt><dd class="col-sm-9">{{ $rubrique->categorie ?? '—' }}</dd>
            <dt class="col-sm-3 text-muted">Code analytique parent</dt>
            <dd class="col-sm-9">
                @if($rubrique->ligne)
                    <a href="{{ route('finance.referentiels.lignes.show', $rubrique->ligne) }}">
                        <code>{{ $rubrique->ligne->id_codeanalytique }}</code> {{ $rubrique->ligne->libelle }}
                    </a>
                    @if($rubrique->ligne->titre)
                        <br><small class="text-muted">Titre : {{ $rubrique->ligne->titre->libelle }}</small>
                    @endif
                @else
                    <span class="text-muted">—</span>
                @endif
            </dd>
            <dt class="col-sm-3 text-muted">Ordre d'affichage</dt><dd class="col-sm-9">{{ $rubrique->ordre_affichage }}</dd>
        </dl>
    </div>
</div>
@endsection
