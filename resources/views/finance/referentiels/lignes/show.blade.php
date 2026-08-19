@extends('layouts.app')
@section('title', $ligne->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><code>{{ $ligne->id_codeanalytique }}</code> {{ $ligne->libelle }}</h1>
        <p class="text-muted mb-0">Titre : <strong>{{ $ligne->titre?->libelle ?? '—' }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.referentiels.lignes.index') }}" class="btn btn-outline-secondary">Retour</a>
        @can('update:ligne')<a href="{{ route('finance.referentiels.lignes.edit', $ligne) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>@endcan
        @can('create:rubrique_operation')
            <a href="{{ route('finance.referentiels.rubriques.create', ['ligne_id' => $ligne->id]) }}" class="btn btn-success"><i class="fas fa-plus me-1"></i> Ajouter une rubrique</a>
        @endcan
    </div>
</div>

@if($ligne->description)<div class="alert alert-light border">{{ $ligne->description }}</div>@endif

<div class="card data-card">
    <div class="card-header"><strong>Rubriques attachées ({{ $rubriques->count() }})</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Code</th><th>Libellé</th><th>Sens</th><th>Catégorie</th><th>Statut</th></tr></thead>
            <tbody>
            @forelse($rubriques as $r)
                <tr>
                    <td><code>{{ $r->code }}</code></td>
                    <td><a href="{{ route('finance.referentiels.rubriques.show', $r) }}">{{ $r->libelle }}</a></td>
                    <td><small>{{ $r->sens_libelle }}</small></td>
                    <td><small>{{ $r->categorie ?? '—' }}</small></td>
                    <td>
                        @if($r->statut)<span class="badge bg-success">Actif</span>
                        @else <span class="badge bg-secondary">Archivé</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">
                    Aucune rubrique sur cette ligne. @can('create:rubrique_operation')<a href="{{ route('finance.referentiels.rubriques.create', ['ligne_id' => $ligne->id]) }}">En ajouter une →</a>@endcan
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
