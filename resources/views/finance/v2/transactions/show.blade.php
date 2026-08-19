@extends('layouts.app')
@section('title', $transaction->label)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.transactions.index') }}">Transactions</a></li>
        <li class="breadcrumb-item active">{{ $transaction->code ?? $transaction->id }}</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $transaction->label }}</h1>
        <p class="text-muted mb-0">
            {{ $transaction->type_libelle }} · <code>{{ $transaction->code ?? '—' }}</code> ·
            <span class="badge bg-{{ $transaction->status_couleur }}">{{ $transaction->status_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.v2.transactions.index') }}" class="btn btn-outline-secondary">Retour</a>
        @if($transaction->est_soumissible)
            <form action="{{ route('finance.v2.transactions.soumettre', $transaction) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-info"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
            </form>
        @endif
        @if($transaction->est_validable)
            <form action="{{ route('finance.v2.transactions.valider', $transaction) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Valider</button>
            </form>
        @endif
        @if($transaction->est_payable)
            <form action="{{ route('finance.v2.transactions.payer', $transaction) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-success"><i class="fas fa-money-bill-transfer me-1"></i> {{ $transaction->type == 0 ? 'Payer' : 'Encaisser' }}</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header"><strong>Détails</strong></div>
            <div class="card-body"><dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Date</dt><dd class="col-sm-8">{{ $transaction->date?->format('d/m/Y') }}</dd>
                @if($transaction->beneficiaire)
                    <dt class="col-sm-4 text-muted">Bénéficiaire</dt><dd class="col-sm-8">{{ $transaction->beneficiaire }}</dd>
                @endif
                @if($transaction->ligne)
                    <dt class="col-sm-4 text-muted">Ligne</dt><dd class="col-sm-8"><code>{{ $transaction->ligne->code }}</code> {{ $transaction->ligne->label ?? $transaction->ligne->libelle }}</dd>
                @endif
                @if($transaction->exercice)
                    <dt class="col-sm-4 text-muted">Exercice</dt><dd class="col-sm-8">{{ $transaction->exercice->label ?? $transaction->exercice->libelle }}</dd>
                @endif
                @if($transaction->compte)
                    <dt class="col-sm-4 text-muted">Compte</dt><dd class="col-sm-8">{{ $transaction->compte->code }} — {{ $transaction->compte->label ?? $transaction->compte->nom }}</dd>
                @endif
                @if($transaction->modeReglement)
                    <dt class="col-sm-4 text-muted">Règlement</dt><dd class="col-sm-8">{{ $transaction->modeReglement->label ?? $transaction->modeReglement->libelle }}</dd>
                @endif
                @if($transaction->entite)
                    <dt class="col-sm-4 text-muted">Entité</dt><dd class="col-sm-8">{{ $transaction->entite->label ?? $transaction->entite->libelle }}</dd>
                @endif
                @if($transaction->description)
                    <dt class="col-sm-4 text-muted">Description</dt><dd class="col-sm-8">{{ $transaction->description }}</dd>
                @endif
            </dl></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header bg-primary text-white"><strong>Montant</strong></div>
            <div class="card-body text-center">
                <div class="fs-2 fw-bold">{{ number_format((float) $transaction->montant, 0, ',', ' ') }}</div>
                <small class="text-muted">{{ $transaction->devise }}</small>
                @if((float) $transaction->montant_restant > 0)
                    <hr>
                    <div class="text-warning">
                        Reste à {{ $transaction->type == 0 ? 'payer' : 'encaisser' }} :
                        <strong>{{ number_format((float) $transaction->montant_restant, 0, ',', ' ') }} {{ $transaction->devise }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($transaction->details->isNotEmpty())
<div class="card data-card mt-3">
    <div class="card-header"><strong>Détails / Rubriques</strong></div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Libellé</th><th>Ligne</th><th class="text-end">Qté</th><th class="text-end">Montant</th></tr></thead>
        <tbody>
        @foreach($transaction->details as $d)
            <tr>
                <td>{{ $d->label }}</td>
                <td><small>{{ $d->ligne?->code ?? '—' }}</small></td>
                <td class="text-end">{{ $d->quantity }}</td>
                <td class="text-end fw-bold">{{ number_format((float) $d->montant, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endif
@endsection
