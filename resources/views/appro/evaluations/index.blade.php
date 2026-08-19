@extends('layouts.app')
@section('title', 'Évaluations prestataires')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div><h1 class="h3 mb-1"><i class="fas fa-star text-muted me-2"></i> Évaluations prestataires</h1>
        <p class="text-muted mb-0">Historique des évaluations post-livraison et de campagne.</p></div>
    <div class="d-flex gap-2">
        <a href="{{ route('appro.campagnes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-bullhorn me-1"></i> Campagnes</a>
        <a href="{{ route('appro.evaluations.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle évaluation</a>
    </div>
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label small mb-1">Prestataire</label>
            <select name="prestataire" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($prestataires as $p)<option value="{{ $p->id }}" @selected(request('prestataire') == $p->id)>{{ $p->raison_sociale ?: $p->nom }}</option>@endforeach
            </select></div>
        <div class="col-md-3"><label class="form-label small mb-1">Source</label>
            <select name="source" class="form-select form-select-sm">
                <option value="">Toutes</option>
                <option value="livraison" @selected(request('source') === 'livraison')>Post-livraison</option>
                <option value="campagne" @selected(request('source') === 'campagne')>Campagne</option>
                <option value="ad_hoc" @selected(request('source') === 'ad_hoc')>Ad hoc</option>
            </select></div>
        <div class="col-md-3"><label class="form-label small mb-1">Campagne</label>
            <select name="campagne" class="form-select form-select-sm">
                <option value="">Toutes</option>
                @foreach($campagnes as $c)<option value="{{ $c->id }}" @selected(request('campagne') == $c->id)>{{ $c->libelle }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>Prestataire</th><th>Source</th><th>Campagne</th><th>Date</th><th>Évaluateur</th>
            <th class="text-end">Note globale /5</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($evaluations as $e)
            <tr>
                <td><strong>{{ $e->prestataire?->raison_sociale ?: $e->prestataire?->nom }}</strong></td>
                <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $e->source)) }}</span></td>
                <td><small>{{ $e->campagne?->libelle ?? '—' }}</small></td>
                <td><small>{{ $e->date_evaluation?->format('d/m/Y') }}</small></td>
                <td><small>{{ $e->evaluateur?->name }}</small></td>
                <td class="text-end fw-bold">{{ number_format((float) $e->note_globale, 2, ',', ' ') }}</td>
                <td class="text-end"><a href="{{ route('appro.evaluations.show', $e) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucune évaluation.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($evaluations->hasPages())<div class="card-footer">{{ $evaluations->links() }}</div>@endif
</div>
@endsection
