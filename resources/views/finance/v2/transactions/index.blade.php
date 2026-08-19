@extends('layouts.app')
@section('title', 'Transactions')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.dashboard') }}">Finance V2</a></li>
        <li class="breadcrumb-item active">Transactions</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exchange-alt me-2 text-muted"></i>Transactions</h1>
        <p class="text-muted mb-0">Dépenses et recettes conformes au schéma initial.</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('finance.v2.transactions.create', ['type' => 0]) }}" class="btn btn-outline-danger"><i class="fas fa-arrow-down me-1"></i> Dépense</a>
        <a href="{{ route('finance.v2.transactions.create', ['type' => 1]) }}" class="btn btn-outline-success"><i class="fas fa-arrow-up me-1"></i> Recette</a>
    </div>
</div>

<div class="card data-card mb-3"><div class="card-body"><form method="GET" class="row g-3 align-items-end">
    <div class="col-md-2"><label class="form-label">Type</label>
        <select name="type" class="form-select">
            <option value="">Tous</option>
            <option value="0" @selected(request('type') === '0')>Dépense</option>
            <option value="1" @selected(request('type') === '1')>Recette</option>
        </select></div>
    <div class="col-md-3"><label class="form-label">Statut</label>
        <select name="status" class="form-select">
            <option value="">Tous</option>
            @foreach(\App\Models\Finance\Transaction::STATUTS as $k => $v)
                <option value="{{ $k }}" @selected(request('status') !== null && request('status') !== '' && (int) request('status') === $k)>{{ $v }}</option>
            @endforeach
        </select></div>
    <div class="col-md-3"><label class="form-label">Exercice</label>
        <select name="exercice_id" class="form-select">
            <option value="">Tous</option>
            @foreach($exercices as $ex)
                <option value="{{ $ex->id }}" @selected(request('exercice_id') == $ex->id)>{{ $ex->label ?? $ex->libelle }}</option>
            @endforeach
        </select></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrer</button></div>
</form></div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Date</th><th>Code</th><th>Type</th><th>Libellé</th><th>Ligne</th><th class="text-end">Montant</th><th class="text-end">Restant</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        @forelse($transactions as $tx)
            <tr>
                <td><small>{{ $tx->date?->format('d/m/Y') }}</small></td>
                <td><code>{{ $tx->code ?? '—' }}</code></td>
                <td>
                    @if($tx->type == 0)<span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Dépense</span>
                    @else<span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Recette</span>@endif
                </td>
                <td>{{ \Illuminate\Support\Str::limit($tx->label, 40) }}</td>
                <td><small>{{ $tx->ligne?->code ?? '—' }}</small></td>
                <td class="text-end fw-bold">{{ number_format((float) $tx->montant, 0, ',', ' ') }}</td>
                <td class="text-end">
                    @if((float) $tx->montant_restant > 0)
                        <span class="text-warning">{{ number_format((float) $tx->montant_restant, 0, ',', ' ') }}</span>
                    @else
                        <i class="fas fa-check text-success"></i>
                    @endif
                </td>
                <td><span class="badge bg-{{ $tx->status_couleur }}">{{ $tx->status_libelle }}</span></td>
                <td><a href="{{ route('finance.v2.transactions.show', $tx) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Aucune transaction.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($transactions->hasPages())<div class="card-footer">{{ $transactions->links() }}</div>@endif
</div>
@endsection
