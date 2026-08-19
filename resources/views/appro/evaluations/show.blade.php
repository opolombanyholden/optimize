@extends('layouts.app')
@section('title', 'Évaluation prestataire')
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1">Évaluation — {{ $evaluation->prestataire?->raison_sociale ?: $evaluation->prestataire?->nom }}</h1>
        <p class="text-muted mb-0">
            {{ $evaluation->date_evaluation?->format('d/m/Y') }} · par {{ $evaluation->evaluateur?->name }}
            · Source : {{ ucfirst(str_replace('_', ' ', $evaluation->source)) }}
            @if($evaluation->campagne) · Campagne : <strong>{{ $evaluation->campagne->libelle }}</strong>@endif
        </p></div>
    <div class="text-end">
        <div class="display-6 text-primary">{{ number_format((float) $evaluation->note_globale, 2, ',', ' ') }} / 5</div>
        <small class="text-muted">Note pondérée</small>
    </div>
</div>

@if($evaluation->commentaire)
    <div class="alert alert-info"><strong>Commentaire :</strong> {{ $evaluation->commentaire }}</div>
@endif

@php $parTheme = $evaluation->notes->groupBy(fn($n) => $n->critere?->theme?->libelle ?? 'Sans thème'); @endphp
@foreach($parTheme as $themeLibelle => $notes)
    <div class="card data-card mb-3">
        <div class="card-header"><h6 class="mb-0">{{ $themeLibelle }}</h6></div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Critère</th><th class="text-end">Note</th><th class="text-center">Poids</th><th>Commentaire</th></tr></thead>
            <tbody>
                @foreach($notes as $n)
                    <tr>
                        <td>{{ $n->critere?->libelle }}</td>
                        <td class="text-end fw-bold">{{ number_format((float) $n->note, 2, ',', ' ') }} / {{ $n->critere?->echelle_max }}</td>
                        <td class="text-center"><small>{{ $n->critere?->poids }}</small></td>
                        <td><small>{{ $n->commentaire ?: '—' }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
@endforeach

<div class="mt-3"><a href="{{ route('appro.evaluations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a></div>
@endsection
