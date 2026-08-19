@extends('layouts.app')
@section('title', 'Opérations financières')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Opérations financières</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-money-bill-transfer me-2 text-muted"></i>Opérations financières</h1>
        <p class="text-muted mb-0">Historique unifié des ordres de dépense et de recette.</p>
    </div>
    @can('create:operation')
        <div class="btn-group">
            <a href="{{ route('finance.operations.create', ['type' => 'depense']) }}" class="btn btn-outline-danger">
                <i class="fas fa-arrow-down me-1"></i> Ordre de dépense
            </a>
            <a href="{{ route('finance.operations.create', ['type' => 'recette']) }}" class="btn btn-outline-success">
                <i class="fas fa-arrow-up me-1"></i> Ordre de recette
            </a>
        </div>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Numéro, objet…"></div>
            <div class="col-md-2"><label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous</option>
                    <option value="depense" @selected(request('type') === 'depense')>Dépense</option>
                    <option value="recette" @selected(request('type') === 'recette')>Recette</option>
                </select></div>
            <div class="col-md-2"><label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\OperationFinanciere::STATUTS as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-3"><label class="form-label">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(request('exercice_id') == $e->id)>{{ $e->libelle ?? $e->exercice }}</option>
                    @endforeach
                </select></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filtrer</button></div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>N°</th><th>Date</th><th>Type</th><th>Objet</th>
                    <th>Ligne budget</th><th class="text-end">Montant</th>
                    <th>Statut</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($operations as $op)
                <tr>
                    <td><code>{{ $op->numero }}</code></td>
                    <td><small>{{ $op->date_operation?->format('d/m/Y') }}</small></td>
                    <td>
                        @if($op->type_operation === 'depense')
                            <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Dépense</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Recette</span>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($op->objet, 40) }}</td>
                    <td><small>{{ $op->budgetLigne?->id_budgetligne ?? '—' }}</small></td>
                    <td class="text-end fw-bold">{{ number_format((float) $op->montant, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $op->statut_couleur }}">{{ $op->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('finance.operations.show', $op) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucune opération.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($operations->hasPages())<div class="card-footer">{{ $operations->links() }}</div>@endif
</div>
@endsection
