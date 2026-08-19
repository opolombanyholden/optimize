@extends('layouts.app')

@section('title', 'Pointages — ' . \Carbon\Carbon::create(null, $mois, 1)->translatedFormat('F') . ' ' . $annee)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Pointages</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-stopwatch me-2 text-muted"></i>Pointages</h1>
        <p class="text-muted mb-0">Saisie des heures travaillées · {{ \Carbon\Carbon::create($annee, $mois, 1)->translatedFormat('F Y') }}</p>
    </div>
    @can('create:paie')
        <div class="d-flex gap-2">
            <a href="{{ route('rh.pointages.create') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Saisie unitaire
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-table me-1"></i> Grille mensuelle
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($employees->take(20) as $e)
                        <li><a class="dropdown-item small" href="{{ route('rh.pointages.grille-employe', ['employee' => $e->id, 'mois' => $mois, 'annee' => $annee]) }}">
                            {{ $e->noms }} {{ $e->prenoms }}
                        </a></li>
                    @endforeach
                    @if($employees->count() > 20)
                        <li><hr class="dropdown-divider"></li>
                        <li><small class="text-muted px-3">+{{ $employees->count() - 20 }} autres — utilisez le filtre</small></li>
                    @endif
                </ul>
            </div>
        </div>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Mois</label>
                <select name="mois" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($mois === $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }} — {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Année</label>
                <input type="number" name="annee" class="form-control" value="{{ $annee }}" min="2020" max="2100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Employé</label>
                <select name="employee_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" @selected(request('employee_id') == $e->id)>{{ $e->noms }} {{ $e->prenoms }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Pointage::STATUTS as $c => $l)
                        <option value="{{ $c }}" @selected(request('statut') !== null && request('statut') !== '' && (int)request('statut') === $c)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<form action="{{ route('rh.pointages.valider') }}" method="POST">
    @csrf
    <div class="card data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:30px;"></th>
                        <th>Date</th>
                        <th>Employé</th>
                        <th class="text-end">H. norm.</th>
                        <th class="text-end">H. sup</th>
                        <th class="text-end">H. nuit</th>
                        <th class="text-end">H. dim.</th>
                        <th class="text-end">Total</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pointages as $p)
                        <tr>
                            <td>
                                @if($p->statut === 0)
                                    <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="form-check-input">
                                @endif
                            </td>
                            <td>{{ $p->date->translatedFormat('D d M') }}</td>
                            <td>
                                <strong>{{ $p->employee->noms }}</strong> {{ $p->employee->prenoms }}
                                <br><small class="text-muted">{{ $p->employee->matricule }}</small>
                            </td>
                            <td class="text-end">{{ number_format($p->h_normales, 2, ',', ' ') }}</td>
                            <td class="text-end {{ $p->h_sup > 0 ? 'text-warning fw-bold' : '' }}">{{ number_format($p->h_sup, 2, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($p->h_nuit, 2, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($p->h_dimanche, 2, ',', ' ') }}</td>
                            <td class="text-end"><strong>{{ number_format($p->total, 2, ',', ' ') }}</strong></td>
                            <td><span class="badge bg-{{ $p->statut_couleur }}">{{ $p->statut_libelle }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('rh.pointages.show', $p) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                @if($p->est_modifiable)
                                    <a href="{{ route('rh.pointages.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">
                            Aucun pointage pour cette période.
                            <a href="{{ route('rh.pointages.create') }}">Saisir le premier →</a>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pointages->hasPages())
            <div class="card-footer">{{ $pointages->links() }}</div>
        @endif
        @if($pointages->where('statut', 0)->count() > 0)
            @can('update:paie')
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success" onclick="return confirm('Valider tous les pointages cochés ? Ils ne seront plus modifiables.');">
                        <i class="fas fa-check me-1"></i> Valider les pointages cochés
                    </button>
                </div>
            @endcan
        @endif
    </div>
</form>
@endsection
