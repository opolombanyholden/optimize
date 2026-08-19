@extends('layouts.app')

@section('title', 'Modifications budgétaires')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Modifications budgétaires</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exchange-alt me-2 text-muted"></i>Modifications budgétaires</h1>
        <p class="text-muted mb-0">Transferts et apports sur les lignes budgétaires — workflow brouillon → soumission → approbation → application.</p>
    </div>
    @can('create:budget')
        <a href="{{ route('finance.modifications-budgetaires.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle modification
        </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(request('exercice_id') == $e->id)>{{ $e->libelle ?? $e->exercice }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\ModificationBudgetaire::TYPES as $k => $v)
                        <option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\ModificationBudgetaire::STATUTS as $k => $v)
                        <option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        @php
            // Helper : rend le libellé complet d'une BudgetLigne (imputation + libellé référentiel + code)
            $renderLigne = function ($bl) {
                if (!$bl) return null;
                $imputation = $bl->ligne?->titre?->imputation;
                $libelle    = $bl->ligne?->libelle ?: $bl->commentaire ?: $bl->id_budgetligne;
                return [
                    'imputation' => $imputation,
                    'libelle'    => $libelle,
                    'code'       => $bl->id_budgetligne,
                ];
            };
        @endphp
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Objet</th>
                    <th>Type</th>
                    <th style="min-width:320px;">Ligne émettrice → réceptrice</th>
                    <th class="text-end">Montant</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($modifications as $m)
                @php
                    $src = $renderLigne($m->ligneSource);
                    $dst = $renderLigne($m->ligneDestination);
                @endphp
                <tr>
                    <td><small>{{ $m->created_at?->format('d/m/Y') }}</small></td>
                    <td>
                        <strong>{{ \Illuminate\Support\Str::limit($m->objetmodification, 50) }}</strong>
                        <br><small class="text-muted">{{ $m->exercice?->libelle ?? '—' }}</small>
                    </td>
                    <td>
                        @if($m->type_modification === 'transfert')
                            <span class="badge bg-info"><i class="fas fa-exchange-alt me-1"></i>Transfert</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-plus-circle me-1"></i>Apport</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            @if($src)
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Ligne émettrice">
                                        <i class="fas fa-arrow-up-from-bracket"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold small">
                                            @if($src['imputation'])<span class="text-muted">[{{ $src['imputation'] }}]</span>@endif
                                            {{ $src['libelle'] }}
                                        </div>
                                        <div class="text-muted" style="font-size:.72rem;"><code>{{ $src['code'] }}</code></div>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted small"><em>— Apport externe (aucune émettrice)</em></div>
                            @endif
                            <div class="text-center text-muted" style="line-height:.5;">
                                <i class="fas fa-arrow-down-long"></i>
                            </div>
                            @if($dst)
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="Ligne réceptrice">
                                        <i class="fas fa-arrow-down-to-bracket"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold small">
                                            @if($dst['imputation'])<span class="text-muted">[{{ $dst['imputation'] }}]</span>@endif
                                            {{ $dst['libelle'] }}
                                        </div>
                                        <div class="text-muted" style="font-size:.72rem;"><code>{{ $dst['code'] }}</code></div>
                                    </div>
                                </div>
                            @else
                                <div class="text-danger small"><em>— Aucune réceptrice</em></div>
                            @endif
                        </div>
                    </td>
                    <td class="text-end fw-bold">{{ number_format((float) $m->montant_modification, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $m->statut_couleur }}">{{ $m->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('finance.modifications-budgetaires.show', $m) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        @can('update:budget')
                            @if($m->est_modifiable)
                                <a href="{{ route('finance.modifications-budgetaires.edit', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune modification budgétaire.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($modifications->hasPages())
        <div class="card-footer">{{ $modifications->links() }}</div>
    @endif
</div>
@endsection
