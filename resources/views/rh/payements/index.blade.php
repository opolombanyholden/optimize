@extends('layouts.app')

@section('title', 'Payements / Reglements')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Payements</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-money-bill-transfer me-2 text-muted"></i>Payements / Reglements</h1>
        <p class="text-muted mb-0">Suivi des reglements de salaires et bulletins de paie</p>
    </div>
    <a href="{{ route('rh.payements.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouveau paiement
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.payements.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="employee_id" class="form-label">Employe</label>
                <input type="text" name="employee_id" id="employee_id" class="form-control"
                       value="{{ request('employee_id') }}" placeholder="ID employe">
            </div>
            <div class="col-12 col-md-3">
                <label for="mode_payement" class="form-label">Mode de paiement</label>
                <select name="mode_payement" id="mode_payement" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Payement::MODES as $key => $label)
                        <option value="{{ $key }}" {{ request('mode_payement') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="0" {{ request('statut') === '0' ? 'selected' : '' }}>En attente</option>
                    <option value="1" {{ request('statut') === '1' ? 'selected' : '' }}>Valide</option>
                    <option value="2" {{ request('statut') === '2' ? 'selected' : '' }}>Annule</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label for="du" class="form-label">Du</label>
                <input type="date" name="du" id="du" class="form-control" value="{{ request('du') }}">
            </div>
            <div class="col-6 col-md-2">
                <label for="au" class="form-label">Au</label>
                <input type="date" name="au" id="au" class="form-control" value="{{ request('au') }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.payements.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-list me-2 text-muted"></i>Liste des payements</h5>
        <span class="text-muted">{{ $payements->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($payements->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucun paiement trouve</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employe</th>
                            <th>Bulletin</th>
                            <th class="text-end">Montant</th>
                            <th>Mode</th>
                            <th>Reference</th>
                            <th>Executant</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payements as $payement)
                        <tr>
                            <td>{{ $payement->date_payement?->format('d/m/Y') }}</td>
                            <td>{{ $payement->employee?->noms }} {{ $payement->employee?->prenoms }}</td>
                            <td>{{ $payement->paie?->label ?? '-' }}</td>
                            <td class="text-end">
                                <strong>{{ number_format($payement->montant, 0, ',', ' ') }} XAF</strong>
                            </td>
                            <td>
                                @php
                                    $modeColors = [
                                        'virement' => 'bg-info-subtle text-info',
                                        'cheque' => 'bg-secondary-subtle text-secondary',
                                        'especes' => 'bg-success-subtle text-success',
                                        'mobile_money' => 'bg-warning-subtle text-warning',
                                    ];
                                    $cls = $modeColors[$payement->mode_payement] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge {{ $cls }}">
                                    {{ \App\Models\Payement::MODES[$payement->mode_payement] ?? $payement->mode_payement }}
                                </span>
                            </td>
                            <td>{{ $payement->reference_payement ?? '-' }}</td>
                            <td>{{ $payement->executeur?->name ?? '-' }}</td>
                            <td>
                                @if($payement->statut == 0)
                                    <span class="badge badge-status badge-en-attente">En attente</span>
                                @elseif($payement->statut == 1)
                                    <span class="badge badge-status badge-valide">Valide</span>
                                @elseif($payement->statut == 2)
                                    <span class="badge badge-status badge-rejete">Annule</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.payements.show', $payement) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('rh.payements.destroy', $payement) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression de ce paiement ?');">
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
@if($payements->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $payements->withQueryString()->links() }}
</div>
@endif
@endsection
