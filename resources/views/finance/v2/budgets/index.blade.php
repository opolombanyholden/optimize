@extends('layouts.app')
@section('title', 'Budgets V2')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.dashboard') }}">Finance V2</a></li>
        <li class="breadcrumb-item active">Budgets</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-wallet me-2 text-muted"></i>Budgets par ligne × exercice</h1>
        <p class="text-muted mb-0">Vue synthétique. Cliquez sur « Planifier » pour éditer en grille.</p>
    </div>
    @if($exerciceId)
        <a href="{{ route('finance.v2.budgets.planification', $exerciceId) }}" class="btn btn-primary">
            <i class="fas fa-th me-1"></i> Planifier ({{ optional($exercices->firstWhere('id', $exerciceId))->label ?? optional($exercices->firstWhere('id', $exerciceId))->libelle }})
        </a>
    @endif
</div>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Exercice</label>
            <select name="exercice_id" class="form-select">
                @foreach($exercices as $ex)
                    <option value="{{ $ex->id }}" @selected($exerciceId == $ex->id)>{{ $ex->label ?? $ex->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrer</button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr>
            <th>Ligne</th><th>Libellé</th><th>Titre</th>
            @foreach($sources as $s)<th class="text-end">{{ $s->code }}</th>@endforeach
            <th class="text-end">Total</th><th>Statut</th>
        </tr></thead>
        <tbody>
        @php $grandTotal = 0; $totauxParSource = []; @endphp
        @forelse($budgets as $b)
            @php $totalLigne = 0; @endphp
            <tr>
                <td><code>{{ $b->ligne?->code ?? '—' }}</code></td>
                <td><strong>{{ $b->label }}</strong></td>
                <td><small>{{ $b->ligne?->titre?->label ?? $b->ligne?->titre?->libelle ?? '—' }}</small></td>
                @foreach($sources as $src)
                    @php
                        $bs = $bsMap->get($b->ligne_id . '_' . $src->id)?->first();
                        $mt = $bs ? (float) $bs->montant : 0;
                        $totalLigne += $mt;
                        $totauxParSource[$src->id] = ($totauxParSource[$src->id] ?? 0) + $mt;
                    @endphp
                    <td class="text-end">{{ $mt > 0 ? number_format($mt, 0, ',', ' ') : '—' }}</td>
                @endforeach
                <td class="text-end fw-bold">{{ number_format($totalLigne, 0, ',', ' ') }}</td>
                <td><span class="badge bg-secondary">{{ $b->status_libelle }}</span></td>
            </tr>
            @php $grandTotal += $totalLigne; @endphp
        @empty
            <tr><td colspan="{{ 5 + $sources->count() }}" class="text-center text-muted py-4">Aucun budget planifié sur cet exercice.</td></tr>
        @endforelse
        </tbody>
        @if($budgets->count() > 0)
            <tfoot class="table-light"><tr class="fw-bold">
                <td colspan="3" class="text-end">TOTAUX</td>
                @foreach($sources as $s)
                    <td class="text-end">{{ number_format($totauxParSource[$s->id] ?? 0, 0, ',', ' ') }}</td>
                @endforeach
                <td class="text-end fs-5">{{ number_format($grandTotal, 0, ',', ' ') }}</td><td></td>
            </tr></tfoot>
        @endif
    </table></div>
</div>
@endsection
