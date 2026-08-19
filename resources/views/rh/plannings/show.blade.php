@extends('layouts.app')

@section('title', 'Planning - ' . ($planning->date_jour?->format('d/m/Y') ?? ''))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.plannings.index') }}">Plannings & Emplois du temps</a></li>
        <li class="breadcrumb-item active">{{ $planning->date_jour?->format('d/m/Y') }} &mdash; {{ $planning->employee?->noms }}</li>
    </ol>
@endsection

@section('content')
@php
    $couleursType = [
        'travail' => 'primary',
        'repos'   => 'secondary',
        'ferie'   => 'info',
        'conge'   => 'success',
        'absence' => 'danger',
    ];
    $color = $couleursType[$planning->type_journee] ?? 'secondary';

    // Calcul du total des heures effectivement travaillees (debut->fin - pause)
    $totalCalc = null;
    if ($planning->heure_debut && $planning->heure_fin) {
        try {
            $debutH = \Carbon\Carbon::parse($planning->heure_debut);
            $finH   = \Carbon\Carbon::parse($planning->heure_fin);
            $minutes = $finH->diffInMinutes($debutH);
            if ($planning->heure_debut_pause && $planning->heure_fin_pause) {
                $debutP = \Carbon\Carbon::parse($planning->heure_debut_pause);
                $finP   = \Carbon\Carbon::parse($planning->heure_fin_pause);
                $minutes -= $finP->diffInMinutes($debutP);
            }
            $totalCalc = max(0, round($minutes / 60, 2));
        } catch (\Throwable $e) {
            $totalCalc = null;
        }
    }
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Planning du {{ $planning->date_jour?->format('d/m/Y') }}</h1>
        <p class="text-muted mb-0">{{ $planning->employee?->noms }} {{ $planning->employee?->prenoms }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.plannings.edit', $planning) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <form action="{{ route('rh.plannings.destroy', $planning) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.plannings.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-week me-2 text-muted"></i>Details du planning</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Employe</td>
                        <td>
                            @if($planning->employee_id)
                                <a href="{{ route('rh.employees.show', $planning->employee_id) }}">
                                    {{ $planning->employee?->noms }} {{ $planning->employee?->prenoms }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date du jour</td>
                        <td><strong>{{ $planning->date_jour?->isoFormat('dddd D MMMM YYYY') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type de journee</td>
                        <td>
                            <span class="badge bg-{{ $color }}">
                                {{ \App\Models\Planning::TYPES_JOURNEE[$planning->type_journee] ?? $planning->type_journee }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Heure debut</td>
                        <td>{{ $planning->heure_debut ? \Illuminate\Support\Str::limit($planning->heure_debut, 5, '') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Heure fin</td>
                        <td>{{ $planning->heure_fin ? \Illuminate\Support\Str::limit($planning->heure_fin, 5, '') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pause</td>
                        <td>
                            @if($planning->heure_debut_pause && $planning->heure_fin_pause)
                                {{ \Illuminate\Support\Str::limit($planning->heure_debut_pause, 5, '') }} - {{ \Illuminate\Support\Str::limit($planning->heure_fin_pause, 5, '') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lieu</td>
                        <td>{{ $planning->lieu ? ucfirst($planning->lieu) : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($planning->statut == 0)
                                <span class="badge badge-status badge-rejete">Annule</span>
                            @elseif($planning->statut == 1)
                                <span class="badge badge-status badge-en-attente">Planifie</span>
                            @elseif($planning->statut == 2)
                                <span class="badge badge-status badge-valide">Realise</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($planning->notes)
        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-align-left me-2 text-muted"></i>Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $planning->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-stopwatch me-2 text-muted"></i>Compteur d'heures</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Heures prevues</td>
                        <td class="text-end"><strong>{{ $planning->heures_prevues ?? '-' }} h</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Heures reelles</td>
                        <td class="text-end"><strong>{{ $planning->heures_reelles ?? '-' }} h</strong></td>
                    </tr>
                    @if($totalCalc !== null)
                    <tr>
                        <td class="text-muted">Total calcule (debut/fin - pause)</td>
                        <td class="text-end"><strong>{{ $totalCalc }} h</strong></td>
                    </tr>
                    @endif
                    @if($planning->heures_reelles !== null && $planning->heures_prevues !== null)
                    <tr>
                        <td class="text-muted">Ecart</td>
                        <td class="text-end">
                            @php $ecart = (float)$planning->heures_reelles - (float)$planning->heures_prevues; @endphp
                            <strong class="{{ $ecart < 0 ? 'text-danger' : ($ecart > 0 ? 'text-success' : '') }}">
                                {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 2, ',', ' ') }} h
                            </strong>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
