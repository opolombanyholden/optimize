@extends('layouts.app')

@section('title', 'Paiement #' . $payement->id)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.payements.index') }}">Payements</a></li>
        <li class="breadcrumb-item active">#{{ $payement->id }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-money-bill-transfer me-2 text-muted"></i>Paiement #{{ $payement->id }}</h1>
        <p class="text-muted mb-0">
            Verse a {{ $payement->employee?->noms }} {{ $payement->employee?->prenoms }}
            le {{ $payement->date_payement?->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('rh.payements.destroy', $payement) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de ce paiement ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Supprimer
            </button>
        </form>
        <a href="{{ route('rh.payements.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card data-card">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-2">Montant verse</p>
                <h1 class="display-4 mb-3">{{ number_format($payement->montant, 0, ',', ' ') }} <small class="text-muted">XAF</small></h1>
                @php
                    $modeColors = [
                        'virement' => 'bg-info-subtle text-info',
                        'cheque' => 'bg-secondary-subtle text-secondary',
                        'especes' => 'bg-success-subtle text-success',
                        'mobile_money' => 'bg-warning-subtle text-warning',
                    ];
                    $cls = $modeColors[$payement->mode_payement] ?? 'bg-light text-dark';
                @endphp
                <span class="badge {{ $cls }} fs-6">
                    <i class="fas fa-credit-card me-1"></i>
                    {{ \App\Models\Payement::MODES[$payement->mode_payement] ?? $payement->mode_payement }}
                </span>
                @if($payement->statut == 0)
                    <span class="badge badge-status badge-en-attente ms-2">En attente</span>
                @elseif($payement->statut == 1)
                    <span class="badge badge-status badge-valide ms-2">Valide</span>
                @elseif($payement->statut == 2)
                    <span class="badge badge-status badge-rejete ms-2">Annule</span>
                @endif
            </div>
        </div>

        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations du paiement</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 35%;">Date de paiement</td>
                        <td>{{ $payement->date_payement?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reference</td>
                        <td>{{ $payement->reference_payement ?? '-' }}</td>
                    </tr>
                    @if($payement->mode_payement === 'virement' || $payement->banque || $payement->iban)
                    <tr>
                        <td class="text-muted">Banque</td>
                        <td>{{ $payement->banque ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IBAN / Compte</td>
                        <td>{{ $payement->iban ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Bulletin de paie</td>
                        <td>
                            @if($payement->paie_id)
                                <a href="{{ route('rh.paie.show', $payement->paie_id) }}">
                                    {{ $payement->paie?->label ?? 'Bulletin #' . $payement->paie_id }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card data-card">
            <div class="card-header">
                <h5><i class="fas fa-user me-2 text-muted"></i>Employe beneficiaire</h5>
            </div>
            <div class="card-body">
                @if($payement->employee_id)
                    <p class="mb-1">
                        <strong>{{ $payement->employee?->noms }} {{ $payement->employee?->prenoms }}</strong>
                    </p>
                    @if($payement->employee?->matricule)
                        <p class="text-muted small mb-2">Matricule : {{ $payement->employee->matricule }}</p>
                    @endif
                    <a href="{{ route('rh.employees.show', $payement->employee_id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Voir la fiche
                    </a>
                @else
                    <p class="text-muted mb-0">-</p>
                @endif
            </div>
        </div>

        <div class="card data-card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-user-check me-2 text-muted"></i>Execute par</h5>
            </div>
            <div class="card-body">
                @if($payement->executeur)
                    <p class="mb-0"><strong>{{ $payement->executeur->name }}</strong></p>
                    @if($payement->executeur->email)
                        <p class="text-muted small mb-0">{{ $payement->executeur->email }}</p>
                    @endif
                @else
                    <p class="text-muted mb-0">-</p>
                @endif
                <p class="text-muted small mb-0 mt-2">
                    Enregistre le {{ $payement->created_at?->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
