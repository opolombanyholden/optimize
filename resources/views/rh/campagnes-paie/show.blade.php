@extends('layouts.app')

@section('title', 'Campagne ' . $campagne->code)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.campagnes-paie.index') }}">Campagnes de paie</a></li>
    <li class="breadcrumb-item active">{{ $campagne->code }}</li>
</ol>
@endsection

@section('content')
@php $fmt = fn($n) => number_format((float)$n, 0, ',', ' '); @endphp

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1>
            <i class="fas fa-rocket me-2 text-primary"></i>
            {{ $campagne->libelle }}
            @if($campagne->simulation)
                <span class="badge bg-info ms-2">SIMULATION</span>
            @endif
        </h1>
        <p class="text-muted mb-0">
            <code>{{ $campagne->code }}</code> · {{ $campagne->periode_libelle }} ·
            <span class="badge bg-{{ $campagne->statut_couleur }}">{{ $campagne->statut_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.campagnes-paie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>

        {{-- Actions de workflow --}}
        @if(in_array($campagne->statut, [0, 1]))
            <form action="{{ route('rh.campagnes-paie.generer', $campagne) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-primary"><i class="fas fa-cogs me-1"></i> Générer les bulletins</button>
            </form>
        @endif
        @if($campagne->statut === 1)
            <form action="{{ route('rh.campagnes-paie.valider', $campagne) }}" method="POST" class="d-inline" onsubmit="return confirm('Valider la campagne ? Les bulletins passeront en statut « validé ».');">
                @csrf
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Valider la campagne</button>
            </form>
        @endif
        @if($campagne->est_cloturable)
            <form action="{{ route('rh.campagnes-paie.cloturer', $campagne) }}" method="POST" class="d-inline" onsubmit="return confirm('Clôturer définitivement la campagne ? Plus aucune modification possible.');">
                @csrf
                <button class="btn btn-dark"><i class="fas fa-lock me-1"></i> Clôturer</button>
            </form>
        @endif
        @if(in_array($campagne->statut, [2, 3, 4]))
            <div class="btn-group">
                <button class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-university me-1"></i> OV bancaire</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('rh.campagnes-paie.ov-pdf', $campagne) }}" target="_blank"><i class="fas fa-file-pdf text-danger me-1"></i> PDF (à imprimer)</a></li>
                    <li><a class="dropdown-item" href="{{ route('rh.campagnes-paie.ov-csv', $campagne) }}"><i class="fas fa-file-csv text-success me-1"></i> CSV (import générique banque)</a></li>
                    <li><a class="dropdown-item" href="{{ route('rh.campagnes-paie.ov-cfonb', $campagne) }}"><i class="fas fa-file-code text-info me-1"></i> CFONB-160 (norme AFB)</a></li>
                </ul>
            </div>
            @if(!$campagne->simulation)
                @php
                    $nbEcritures = \App\Models\GrandLivre::where('ref_piece', $campagne->code)
                        ->where('nature', 'paie')->count();
                @endphp
                @can('read:grandlivre')
                    @if($nbEcritures > 0)
                        <a href="{{ route('finance.grand-livre.index', ['ref_piece' => $campagne->code]) }}"
                           class="btn btn-outline-primary" title="Voir les écritures comptables">
                            <i class="fas fa-book me-1"></i> Écritures comptables
                            <span class="badge bg-primary ms-1">{{ $nbEcritures }}</span>
                        </a>
                    @endif
                @endcan
            @endif
        @endif
        @if($campagne->est_modifiable)
            <a href="{{ route('rh.campagnes-paie.edit', $campagne) }}" class="btn btn-outline-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
            <form action="{{ route('rh.campagnes-paie.destroy', $campagne) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer la campagne et tous ses bulletins ?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
        @endif
    </div>
</div>

{{-- KPI cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.8rem;color:#0891B2;"><i class="fas fa-file-invoice"></i></div><div class="h4 mb-0">{{ $campagne->nombre_bulletins }}</div><div class="text-muted small">Bulletins</div></div></div>
    <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.8rem;color:#059669;"><i class="fas fa-coins"></i></div><div class="h4 mb-0">{{ $fmt($campagne->masse_brute) }}</div><div class="text-muted small">Masse brute (XAF)</div></div></div>
    <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.8rem;color:#7C3AED;"><i class="fas fa-hand-holding-dollar"></i></div><div class="h4 mb-0">{{ $fmt($campagne->masse_nette) }}</div><div class="text-muted small">Masse nette (XAF)</div></div></div>
    <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.8rem;color:#DC2626;"><i class="fas fa-piggy-bank"></i></div><div class="h4 mb-0">{{ $fmt($campagne->total_cotisations_sal + $campagne->total_cotisations_pat) }}</div><div class="text-muted small">Cotisations totales</div></div></div>
</div>

<div class="row g-3">
    {{-- Métadonnées campagne --}}
    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Métadonnées</h6></div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">Code</td><td><code>{{ $campagne->code }}</code></td></tr>
                    <tr><td class="text-muted">Période</td><td>{{ $campagne->date_debut->format('d/m/Y') }} → {{ $campagne->date_fin->format('d/m/Y') }}</td></tr>
                    <tr><td class="text-muted">Périodicité</td><td>{{ ucfirst($campagne->periodicite) }}</td></tr>
                    <tr><td class="text-muted">Date paiement</td><td>{{ $campagne->date_paiement_prevue?->format('d/m/Y') ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Type</td><td>{{ $campagne->simulation ? 'Simulation' : 'Réelle' }}</td></tr>
                    <tr><td class="text-muted">Créée par</td><td>{{ $campagne->createur?->name ?? '—' }} <br><small class="text-muted">{{ $campagne->created_at->format('d/m/Y H:i') }}</small></td></tr>
                    @if($campagne->validee_par)
                    <tr><td class="text-muted">Validée par</td><td>{{ $campagne->validateur?->name }} <br><small class="text-muted">{{ $campagne->validee_at?->format('d/m/Y H:i') }}</small></td></tr>
                    @endif
                    @if($campagne->cloturee_par)
                    <tr><td class="text-muted">Clôturée par</td><td>{{ $campagne->cloturePar?->name }} <br><small class="text-muted">{{ $campagne->cloturee_at?->format('d/m/Y H:i') }}</small></td></tr>
                    @endif
                </table>
                @if($campagne->commentaire)
                    <hr>
                    <p class="small text-muted mb-0"><strong>Commentaire :</strong> {{ $campagne->commentaire }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Employés inclus --}}
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i> Employés inclus ({{ $campagne->employes->count() }})</h6>
            </div>
            <div class="card-body p-0">
                @if($campagne->employes->isEmpty())
                    <div class="empty-state"><p>Aucun employé sélectionné.</p></div>
                @else
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr><th>Matricule</th><th>Nom complet</th><th>Poste</th><th>Salaire base</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                    @foreach($campagne->employes as $e)
                        <tr>
                            <td><code>{{ $e->matricule }}</code></td>
                            <td>{{ $e->noms }} {{ $e->prenoms }}</td>
                            <td>{{ $e->poste ?? '—' }}</td>
                            <td class="text-end">{{ $fmt($e->salaire_base) }}</td>
                            <td>
                                @if($e->pivot->statut == 0)
                                    <span class="badge bg-secondary">À générer</span>
                                @elseif($e->pivot->statut == 1)
                                    <span class="badge bg-success">Bulletin généré</span>
                                @elseif($e->pivot->statut == 2)
                                    <span class="badge bg-warning">Exclu</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Bulletins générés --}}
@if($campagne->bulletins->isNotEmpty())
<div class="card data-card mt-4">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i> Bulletins générés ({{ $campagne->bulletins->count() }})</h6></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>N° Bulletin</th><th>Employé</th><th class="text-end">Brut</th><th class="text-end">Cot. sal</th><th class="text-end">Cot. pat</th><th class="text-end">IRPP</th><th class="text-end">Net à payer</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
            @foreach($campagne->bulletins as $b)
                <tr>
                    <td><code>{{ $b->numero_bulletin }}</code></td>
                    <td>{{ $b->employee?->noms }} {{ $b->employee?->prenoms }}</td>
                    <td class="text-end font-monospace">{{ $fmt($b->brut) }}</td>
                    <td class="text-end font-monospace">{{ $fmt($b->cotisations_salariales) }}</td>
                    <td class="text-end font-monospace">{{ $fmt($b->cotisations_patronales) }}</td>
                    <td class="text-end font-monospace">{{ $fmt($b->irpp) }}</td>
                    <td class="text-end font-monospace fw-bold text-success">{{ $fmt($b->net_a_payer) }}</td>
                    <td>
                        @if($b->statut == 0)<span class="badge bg-secondary">Brouillon</span>
                        @elseif($b->statut == 1)<span class="badge bg-warning text-dark">Validé</span>
                        @elseif($b->statut == 2)<span class="badge bg-success">Payé</span>
                        @endif
                    </td>
                    <td><a href="{{ route('rh.paie.show', $b) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
