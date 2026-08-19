@extends('layouts.app')
@section('title', 'Planification budgétaire V2')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.dashboard') }}">Finance V2</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.budgets.index') }}">Budgets</a></li>
        <li class="breadcrumb-item active">Planification</li>
    </ol>
@endsection

@push('styles')
<style>
    .planif-v2-table { font-size: .87rem; }
    .planif-v2-table thead th { background: #f8fafc; position: sticky; top: 0; z-index: 3; }
    .planif-v2-table .titre-row td {
        background: linear-gradient(90deg, #eff6ff, #fff);
        font-weight: 700; color: #1e3a8a; padding: .6rem;
    }
    .planif-v2-table .titre-code {
        background: #1e3a8a; color: #fff; padding: .15rem .5rem;
        border-radius: 4px; font-family: monospace; font-size: .75rem; margin-right: .5rem;
    }
    .planif-v2-table input.form-control-sm {
        font-size: .82rem; padding: .2rem .45rem;
        font-variant-numeric: tabular-nums; text-align: right;
    }
    .planif-v2-table input[type="text"] { text-align: left; }
    .planif-v2-table .cell-total {
        background: #fefce8; font-weight: 700; font-variant-numeric: tabular-nums;
    }
    .planif-v2-table .row-vide { opacity: .55; }
    .planif-v2-table .row-vide:hover { opacity: 1; background: #fef9c3; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-th me-2 text-muted"></i>Planification budgétaire — V2</h1>
        <p class="text-muted mb-0">
            Exercice : <strong>{{ $exercice->label ?? $exercice->libelle }}</strong> ·
            Modèle conforme au CdC initial (répartition Ligne × Source × Exercice)
        </p>
    </div>
    <a href="{{ route('finance.v2.dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('finance.v2.budgets.planification.save', $exercice) }}" method="POST" id="formPlanif">
    @csrf

    <div class="card data-card mb-3">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <div>
                <label class="form-check-label small mb-0">
                    <input type="checkbox" class="form-check-input" id="filtreVides">
                    Masquer lignes non renseignées
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Enregistrer</button>
        </div>
    </div>

    <div class="card data-card">
        <div class="table-responsive" style="max-height: calc(100vh - 300px); overflow-y: auto;">
            <table class="table table-sm planif-v2-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:10%;">Code</th>
                        <th style="width:22%;">Libellé</th>
                        @foreach($sources as $src)
                            <th class="text-end" style="width:{{ 45 / $sources->count() }}%;">
                                <span class="badge bg-info">{{ $src->code }}</span><br>
                                <small>{{ $src->label }}</small>
                            </th>
                        @endforeach
                        <th class="text-end" style="width:13%;">Total ligne</th>
                        <th style="width:10%;">Seuil</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($titres as $titre)
                    @if($titre->lignes->isEmpty()) @continue @endif
                    <tr class="titre-row">
                        <td colspan="{{ 4 + $sources->count() }}">
                            <span class="titre-code">{{ $titre->code ?: '—' }}</span>
                            {{ $titre->label ?? $titre->libelle }}
                            <small class="text-muted ms-2">({{ $titre->lignes->count() }} ligne(s))</small>
                        </td>
                    </tr>
                    @foreach($titre->lignes as $ligne)
                        @php
                            $bl = $budgetsByLigne->get($ligne->id);
                            $rowTotal = 0;
                            foreach ($sources as $src) {
                                $bs = $bsMap->get($ligne->id . '_' . $src->id)?->first();
                                $rowTotal += $bs ? (float) $bs->montant : 0;
                            }
                        @endphp
                        <tr class="ligne-row {{ $rowTotal <= 0 ? 'row-vide' : '' }}">
                            <td><code class="small">{{ $ligne->code }}</code></td>
                            <td>
                                <input type="text" name="lignes[{{ $ligne->id }}][label]"
                                       class="form-control form-control-sm"
                                       value="{{ $bl?->label ?? $ligne->label ?? $ligne->libelle }}">
                            </td>
                            @foreach($sources as $src)
                                @php
                                    $bs = $bsMap->get($ligne->id . '_' . $src->id)?->first();
                                    $mt = $bs ? (float) $bs->montant : 0;
                                @endphp
                                <td class="text-end">
                                    <input type="number" step="0.01" min="0"
                                           name="lignes[{{ $ligne->id }}][sources][{{ $src->id }}]"
                                           class="form-control form-control-sm numeric input-src"
                                           data-ligne="{{ $ligne->id }}"
                                           value="{{ $mt ?: '' }}" placeholder="0">
                                </td>
                            @endforeach
                            <td class="text-end cell-total" data-total-ligne="{{ $ligne->id }}">{{ number_format($rowTotal, 0, ',', ' ') }}</td>
                            <td>
                                <input type="number" step="0.01" min="0"
                                       name="lignes[{{ $ligne->id }}][seuil]"
                                       class="form-control form-control-sm"
                                       value="{{ $bl?->seuil ?: '' }}" placeholder="—">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">TOTAUX PAR SOURCE</td>
                        @foreach($sources as $src)
                            <td class="text-end" data-total-source="{{ $src->id }}">0</td>
                        @endforeach
                        <td class="text-end fs-5 text-primary" id="grandTotal">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer la planification</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const tbody = document.querySelector('.planif-v2-table');
    const filtreVides = document.getElementById('filtreVides');

    function fmt(n) { return Number(n || 0).toLocaleString('fr-FR', {maximumFractionDigits: 0}); }
    function val(inp) { return parseFloat(inp?.value || '') || 0; }

    function recalculer() {
        const totauxSrc = {};
        let grandTotal = 0;

        tbody.querySelectorAll('tr.ligne-row').forEach(tr => {
            let rowTotal = 0;
            tr.querySelectorAll('input.input-src').forEach(inp => {
                const src = inp.name.match(/\[(\d+)\]\]$/)?.[1] || inp.name.match(/\[sources\]\[(\d+)\]/)?.[1];
                const v = val(inp);
                rowTotal += v;
                if (src) totauxSrc[src] = (totauxSrc[src] || 0) + v;
            });
            const ligneId = tr.querySelector('input.input-src')?.dataset.ligne;
            if (ligneId) {
                const cell = document.querySelector(`[data-total-ligne="${ligneId}"]`);
                if (cell) cell.textContent = fmt(rowTotal);
            }
            grandTotal += rowTotal;
            tr.classList.toggle('row-vide', rowTotal <= 0);
        });

        Object.keys(totauxSrc).forEach(sid => {
            const cell = document.querySelector(`[data-total-source="${sid}"]`);
            if (cell) cell.textContent = fmt(totauxSrc[sid]);
        });
        document.getElementById('grandTotal').textContent = fmt(grandTotal);
    }

    tbody.querySelectorAll('input.numeric').forEach(inp => inp.addEventListener('input', recalculer));

    filtreVides?.addEventListener('change', () => {
        tbody.querySelectorAll('tr.ligne-row').forEach(tr => {
            tr.style.display = (filtreVides.checked && tr.classList.contains('row-vide')) ? 'none' : '';
        });
    });

    recalculer();
})();
</script>
@endpush
@endsection
