@extends('layouts.app')

@section('title', 'Pointage #' . $pointage->id)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Pointage du {{ $pointage->date->translatedFormat('d M Y') }}</h1>
        <p class="text-muted mb-0">
            <strong>{{ $pointage->employee->noms }} {{ $pointage->employee->prenoms }}</strong>
            · <span class="badge bg-{{ $pointage->statut_couleur }}">{{ $pointage->statut_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @if($pointage->est_modifiable)
            <a href="{{ route('rh.pointages.edit', $pointage) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card data-card">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Heures normales</td><td class="text-end"><strong>{{ number_format($pointage->h_normales, 2, ',', ' ') }}</strong></td></tr>
                    <tr><td class="text-muted">Heures supplémentaires</td><td class="text-end text-warning"><strong>{{ number_format($pointage->h_sup, 2, ',', ' ') }}</strong></td></tr>
                    <tr><td class="text-muted">Heures de nuit</td><td class="text-end"><strong>{{ number_format($pointage->h_nuit, 2, ',', ' ') }}</strong></td></tr>
                    <tr><td class="text-muted">Heures de dimanche/férié</td><td class="text-end"><strong>{{ number_format($pointage->h_dimanche, 2, ',', ' ') }}</strong></td></tr>
                    <tr class="table-light"><td><strong>Total</strong></td><td class="text-end"><strong class="text-primary fs-5">{{ number_format($pointage->total, 2, ',', ' ') }} h</strong></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card data-card">
            <div class="card-body">
                @if($pointage->motif)
                    <p class="mb-2"><strong>Motif :</strong> {{ $pointage->motif }}</p>
                @endif
                @if($pointage->notes)
                    <p class="mb-2"><strong>Notes :</strong></p>
                    <div class="text-muted small">{{ $pointage->notes }}</div>
                @endif
                <hr>
                <p class="text-muted small mb-1">Saisi par <strong>{{ $pointage->saisiPar?->name ?? '—' }}</strong> le {{ $pointage->created_at?->translatedFormat('d M Y H:i') }}</p>
                @if($pointage->validePar)
                    <p class="text-muted small mb-0">Validé par <strong>{{ $pointage->validePar->name }}</strong> le {{ $pointage->valide_at?->translatedFormat('d M Y H:i') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
