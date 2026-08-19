@extends('layouts.app')

@section('title', 'Masse salariale & Agregats')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Masse salariale</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-pie me-2 text-muted"></i>Masse salariale & Agregats</h1>
        <p class="text-muted mb-0">Synthese mensuelle de la masse salariale pour l'exercice {{ $annee }}</p>
    </div>
</div>

{{-- Filtre annee --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.payements-globals.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="annee" class="form-label">Annee</label>
                <select name="annee" id="annee" class="form-select" onchange="this.form.submit()">
                    @for($y = 2024; $y <= 2027; $y++)
                        <option value="{{ $y }}" {{ $annee == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-9 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Appliquer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tableau mensuel --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-calendar-alt me-2 text-muted"></i>Synthese mensuelle {{ $annee }}</h5>
    </div>
    <div class="card-body p-0">
        @php
            $mois = [
                1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre',
            ];
            $globalsByMois = collect($globals)->keyBy('mois');
        @endphp
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th class="text-end">Bulletins</th>
                        <th class="text-end">Masse brute</th>
                        <th class="text-end">Masse nette</th>
                        <th class="text-end">Cotisations</th>
                        <th class="text-end">IRPP</th>
                        <th class="text-end">Total paye</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mois as $numMois => $libelle)
                        @php $global = $globalsByMois->get($numMois); @endphp
                        <tr>
                            <td><strong>{{ $libelle }}</strong></td>
                            @if($global)
                                @php
                                    $totalCotis = ($global->total_cotisations_salariales ?? 0) + ($global->total_cotisations_patronales ?? 0);
                                @endphp
                                <td class="text-end">{{ $global->nombre_bulletins ?? 0 }}</td>
                                <td class="text-end">{{ number_format($global->masse_salariale_brute ?? 0, 0, ',', ' ') }} XAF</td>
                                <td class="text-end">{{ number_format($global->masse_salariale_nette ?? 0, 0, ',', ' ') }} XAF</td>
                                <td class="text-end">{{ number_format($totalCotis, 0, ',', ' ') }} XAF</td>
                                <td class="text-end">{{ number_format($global->total_irpp ?? 0, 0, ',', ' ') }} XAF</td>
                                <td class="text-end"><strong>{{ number_format($global->total_paye ?? 0, 0, ',', ' ') }} XAF</strong></td>
                                <td>
                                    @if($global->statut == 0)
                                        <span class="badge badge-status badge-en-attente">Brouillon</span>
                                    @elseif($global->statut == 1)
                                        <span class="badge badge-status badge-valide">Cloture</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('rh.payements-globals.show', $global) }}" class="btn btn-sm btn-outline-primary" title="Voir detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($global->statut == 0)
                                        <form action="{{ route('rh.payements-globals.recalculer') }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Recalculer l\'agrégat de {{ $libelle }} {{ $annee }} ? Les chiffres seront mis à jour depuis les bulletins actuels.');">
                                            @csrf
                                            <input type="hidden" name="annee" value="{{ $annee }}">
                                            <input type="hidden" name="mois" value="{{ $numMois }}">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Recalculer depuis les bulletins">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @else
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td><span class="text-muted">Non calcule</span></td>
                                <td class="text-end">
                                    <form action="{{ route('rh.payements-globals.recalculer') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="annee" value="{{ $annee }}">
                                        <input type="hidden" name="mois" value="{{ $numMois }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Calculer">
                                            <i class="fas fa-calculator me-1"></i>Calculer
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
