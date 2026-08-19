@extends('layouts.app')
@section('title', $campagne->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1">{{ $campagne->libelle }}
            <span class="badge bg-{{ ['secondary','info','dark'][$campagne->statut] }} ms-2">{{ $campagne->statut_libelle }}</span></h1>
        <p class="text-muted mb-0">{{ $campagne->date_debut?->format('d/m/Y') }} → {{ $campagne->date_fin?->format('d/m/Y') ?? '—' }}</p></div>
    <div class="d-flex gap-2">
        @if($campagne->statut === \App\Models\CampagneEvaluation::STATUT_BROUILLON)
            <a href="{{ route('appro.campagnes.edit', $campagne) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            <form action="{{ route('appro.campagnes.lancer', $campagne) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-info text-white"><i class="fas fa-play me-1"></i> Lancer</button></form>
        @elseif($campagne->statut === \App\Models\CampagneEvaluation::STATUT_EN_COURS)
            <form action="{{ route('appro.campagnes.cloturer', $campagne) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-dark"><i class="fas fa-lock me-1"></i> Clôturer</button></form>
        @endif
    </div>
</div>

@if($campagne->description)<div class="alert alert-info">{{ $campagne->description }}</div>@endif

<div class="card data-card">
    <div class="card-header"><h6 class="mb-0">Prestataires ({{ $campagne->prestataires->count() }})</h6></div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Prestataire</th><th>Évaluation</th><th class="text-end">Note</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @foreach($campagne->prestataires as $p)
                @php $eval = $campagne->evaluations->firstWhere('prestataire_id', $p->id); @endphp
                <tr>
                    <td>{{ $p->raison_sociale ?: $p->nom }}</td>
                    <td>@if($eval)<span class="badge bg-success">Faite le {{ $eval->date_evaluation->format('d/m/Y') }}</span>@else<span class="badge bg-secondary">À faire</span>@endif</td>
                    <td class="text-end">{{ $eval ? number_format((float) $eval->note_globale, 2, ',', ' ') . ' / 5' : '—' }}</td>
                    <td class="text-end">
                        @if($eval)
                            <a href="{{ route('appro.evaluations.show', $eval) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @elseif($campagne->statut === \App\Models\CampagneEvaluation::STATUT_EN_COURS)
                            <a href="{{ route('appro.evaluations.create', ['campagne_id' => $campagne->id, 'prestataire_id' => $p->id]) }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Évaluer</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endsection
