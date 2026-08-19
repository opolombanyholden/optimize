@extends('layouts.app')
@section('title', 'Campagnes d\'évaluation')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><i class="fas fa-bullhorn text-muted me-2"></i> Campagnes d'évaluation</h1></div>
    <a href="{{ route('appro.campagnes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle campagne</a>
</div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Libellé</th><th>Période</th><th class="text-center">Prestataires</th><th class="text-center">Évaluations</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($campagnes as $c)
            <tr>
                <td><strong>{{ $c->libelle }}</strong></td>
                <td><small>{{ $c->date_debut?->format('d/m/Y') }} → {{ $c->date_fin?->format('d/m/Y') ?? '—' }}</small></td>
                <td class="text-center">{{ $c->prestataires->count() }}</td>
                <td class="text-center">{{ $c->evaluations->count() }} / {{ $c->prestataires->count() }}</td>
                <td><span class="badge bg-{{ ['secondary','info','dark'][$c->statut] }}">{{ $statuts[$c->statut] }}</span></td>
                <td class="text-end"><a href="{{ route('appro.campagnes.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune campagne.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
