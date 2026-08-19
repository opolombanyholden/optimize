@extends('layouts.app')
@section('title', $titre->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><code>{{ $titre->imputation }}</code> {{ $titre->libelle }}</h1>
        <p class="text-muted mb-0">{{ ucfirst($titre->type_ligne ?? '—') }} · Seuil : {{ number_format((float) $titre->seuil, 0, ',', ' ') }} XAF</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.referentiels.titres.index') }}" class="btn btn-outline-secondary">Retour</a>
        @can('update:titre')<a href="{{ route('finance.referentiels.titres.edit', $titre) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>@endcan
        @can('delete:titre')
            @php $nbLignesTitre = $titre->lignes->count(); @endphp
            <form action="{{ route('finance.referentiels.titres.destroy', $titre) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Supprimer le titre « {{ addslashes($titre->libelle) }} » ? Cette action est irréversible.');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"
                        @if($nbLignesTitre > 0) disabled title="Impossible : {{ $nbLignesTitre }} ligne(s) rattachée(s)"@else title="Supprimer"@endif>
                    <i class="fas fa-trash me-1"></i> Supprimer
                </button>
            </form>
        @endcan
    </div>
</div>

@if($titre->description)
    <div class="alert alert-light border">{{ $titre->description }}</div>
@endif

<div class="card data-card">
    <div class="card-header"><strong>Lignes rattachées ({{ $titre->lignes->count() }})</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Code</th><th>Libellé</th><th>Seuil</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($titre->lignes as $l)
                <tr>
                    <td><code>{{ $l->id_codeanalytique }}</code></td>
                    <td>{{ $l->libelle }}</td>
                    <td><small>{{ number_format((float) $l->seuil, 0, ',', ' ') }}</small></td>
                    <td class="text-end"><a href="{{ route('finance.referentiels.lignes.show', $l) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Aucune ligne.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
