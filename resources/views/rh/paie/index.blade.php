@extends('layouts.app')

@section('title', 'Paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Paie</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Paie</h1>
        <p class="text-muted mb-0">Gestion des bulletins de paie</p>
    </div>
    <a href="{{ route('rh.paie.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau bulletin
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.paie.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="exercice_id" class="form-label">Exercice</label>
                <select name="exercice_id" id="exercice_id" class="form-select">
                    <option value="">Tous les exercices</option>
                    @foreach(\App\Models\Exercice::orderByDesc('datedebut')->get() as $exercice)
                        <option value="{{ $exercice->id }}" {{ request('exercice_id') == $exercice->id ? 'selected' : '' }}>{{ $exercice->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="mois" class="form-label">Mois</label>
                <select name="mois" id="mois" class="form-select">
                    <option value="">Tous les mois</option>
                    <option value="1" {{ request('mois') == '1' ? 'selected' : '' }}>Janvier</option>
                    <option value="2" {{ request('mois') == '2' ? 'selected' : '' }}>Fevrier</option>
                    <option value="3" {{ request('mois') == '3' ? 'selected' : '' }}>Mars</option>
                    <option value="4" {{ request('mois') == '4' ? 'selected' : '' }}>Avril</option>
                    <option value="5" {{ request('mois') == '5' ? 'selected' : '' }}>Mai</option>
                    <option value="6" {{ request('mois') == '6' ? 'selected' : '' }}>Juin</option>
                    <option value="7" {{ request('mois') == '7' ? 'selected' : '' }}>Juillet</option>
                    <option value="8" {{ request('mois') == '8' ? 'selected' : '' }}>Aout</option>
                    <option value="9" {{ request('mois') == '9' ? 'selected' : '' }}>Septembre</option>
                    <option value="10" {{ request('mois') == '10' ? 'selected' : '' }}>Octobre</option>
                    <option value="11" {{ request('mois') == '11' ? 'selected' : '' }}>Novembre</option>
                    <option value="12" {{ request('mois') == '12' ? 'selected' : '' }}>Decembre</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>Brouillon</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Valide</option>
                    <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Paye</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Bulletins de paie</h5>
        <span class="text-muted">{{ $paies->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($paies->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun bulletin de paie trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Periode</th>
                            <th class="text-end">Salaire base</th>
                            <th class="text-end">Brut</th>
                            <th class="text-end">Net a payer</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paies as $paie)
                        <tr>
                            <td>{{ $paie->employee?->noms }} {{ $paie->employee?->prenoms }}</td>
                            <td>{{ $paie->debut?->format('m/Y') ?? '-' }}</td>
                            <td class="text-end">{{ number_format($paie->salaire_base ?? 0, 0, ',', ' ') }} F</td>
                            <td class="text-end">{{ number_format($paie->brut ?? 0, 0, ',', ' ') }} F</td>
                            <td class="text-end"><strong>{{ number_format($paie->net_a_payer ?? 0, 0, ',', ' ') }} F</strong></td>
                            <td>
                                @if($paie->statut == 0)
                                    <span class="badge badge-status badge-brouillon">Brouillon</span>
                                @elseif($paie->statut == 1)
                                    <span class="badge badge-status badge-valide">Valide</span>
                                @elseif($paie->statut == 2)
                                    <span class="badge badge-status badge-actif">Paye</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.paie.show', $paie) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.paie.pdf', $paie) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="{{ route('rh.paie.edit', $paie) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.paie.destroy', $paie) }}" method="POST" class="d-inline" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce bulletin ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
@if($paies->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $paies->withQueryString()->links() }}
</div>
@endif
@endsection
