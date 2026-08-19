@extends('layouts.app')

@section('title', 'Planification budgétaire — ' . ($exercice->libelle ?? $exercice->exercice))

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.exercices.index') }}">Exercices</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.exercices.show', $exercice) }}">{{ $exercice->libelle }}</a></li>
        <li class="breadcrumb-item active">Planification budgétaire</li>
    </ol>
@endsection

@push('styles')
<style>
    /* Grille de planification : compact, scrollable, en-têtes sticky */
    .planif-table {
        font-size: .87rem;
    }
    .planif-table thead th {
        position: sticky; top: 0; z-index: 5;
        background: #f8fafc;
    }
    .planif-table .titre-row td {
        background: linear-gradient(90deg, #eff6ff, #fff);
        font-weight: 700;
        color: #1e3a8a;
        padding-top: .6rem;
        padding-bottom: .6rem;
    }
    .planif-table .titre-row .titre-imputation {
        background: #1e3a8a;
        color: #fff;
        padding: .15rem .5rem;
        border-radius: 4px;
        font-family: monospace;
        font-size: .75rem;
        margin-right: .5rem;
    }
    .planif-table .ligne-row td { padding: .35rem .55rem; }
    .planif-table input.form-control-sm {
        font-size: .82rem; padding: .2rem .45rem;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }
    .planif-table input[type="text"] { text-align: left; }
    .planif-table .total-cell {
        background: #fefce8;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .planif-table .subtotal-row td {
        background: #f1f5f9;
        font-weight: 700;
        border-top: 2px solid #cbd5e1;
    }
    .planif-table .row-vide {
        opacity: .55;
    }
    .planif-table .row-vide:hover {
        opacity: 1;
        background: #fef9c3;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-th me-2 text-muted"></i>Planification budgétaire — grille</h1>
        <p class="text-muted mb-0">
            <strong>{{ $exercice->libelle ?? $exercice->exercice }}</strong> ·
            {{ $exercice->datedebut ? \Carbon\Carbon::parse($exercice->datedebut)->format('d/m/Y') : '—' }}
            au {{ $exercice->datefin ? \Carbon\Carbon::parse($exercice->datefin)->format('d/m/Y') : '—' }}
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $exercice->statut_couleur }}">{{ $exercice->statut_libelle }}</span>
        @if($exercice->type_planification)
            <span class="badge bg-{{ $exercice->type_planification_couleur }}" title="{{ $exercice->type_planification_libelle }}">
                <i class="fas {{ $exercice->type_planification_icone }} me-1"></i>{{ ucfirst($exercice->type_planification) }}
            </span>
        @endif
        @if($exercice->validation_statut == 1)
            <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Soumis au top management</span>
        @endif
        @if($exercice->peut_planifier)
        <form action="{{ route('finance.exercices.planification.sync-referentiel', $exercice) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Ajouter les lignes du référentiel absentes de la planification ?');">
            @csrf
            <button class="btn btn-outline-info" title="Ajoute les lignes du référentiel qui ne sont pas encore présentes">
                <i class="fas fa-rotate me-1"></i> Sync référentiel
            </button>
        </form>
        @endif
        <a href="{{ route('finance.exercices.show', $exercice) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour fiche
        </a>
    </div>
</div>

@if($exercice->motif_rejet && $exercice->validation_statut == 0)
    <div class="alert alert-danger">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Planification précédemment rejetée</h6>
        <p class="mb-0"><strong>Motif :</strong> {{ $exercice->motif_rejet }}</p>
    </div>
@endif

@if(!$exercice->peut_planifier)
    <div class="alert alert-info">
        <i class="fas fa-lock me-1"></i>
        @if($exercice->validation_statut == 1)
            Planification <strong>verrouillée</strong> : en attente de validation du top management.
        @elseif($exercice->statut == 2)
            Exercice en <strong>exécution</strong> : modifications budgétaires uniquement via les <a href="{{ route('finance.modifications-budgetaires.index') }}">modifications budgétaires</a>.
        @elseif($exercice->statut == 3)
            Exercice <strong>clôturé</strong>.
        @endif
    </div>
@endif

{{-- ═════════ KPIs LIVE ═════════ --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-success">
            <div class="card-body">
                <div class="text-muted small">Dotation État</div>
                <div class="h5 mb-0" id="kpiDot">0</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-warning">
            <div class="card-body">
                <div class="text-muted small">Fonds propres</div>
                <div class="h5 mb-0" id="kpiFP">0</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-secondary">
            <div class="card-body">
                <div class="text-muted small">Reports</div>
                <div class="h5 mb-0" id="kpiRep">0</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card data-card border-start border-3 border-info">
            <div class="card-body">
                <div class="text-muted small">Budget total</div>
                <div class="h4 mb-0 fw-bold" id="kpiTotal">0</div>
                <small class="text-muted">XAF</small>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('finance.exercices.planification.save', $exercice) }}" method="POST" id="formPlanif">
    @csrf

    {{-- ═════════ TOOLBAR ═════════ --}}
    <div class="card data-card mb-2">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex gap-3 align-items-center">
                <label class="form-check-label small mb-0">
                    <input type="checkbox" class="form-check-input" id="filtreVides">
                    Masquer les lignes à budget nul
                </label>
                <span class="text-muted small">
                    Total lignes : <strong>{{ $titres->sum(fn($t) => $t->lignes->count()) }}</strong> ·
                    Renseignées : <strong id="nbRenseignees">0</strong>
                </span>
            </div>
            @if($exercice->peut_planifier)
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Enregistrer</button>
                    @if($exercice->peut_soumettre || $exercice->budgetLignes()->exists())
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSoumettre">
                            <i class="fas fa-paper-plane me-1"></i> Soumettre
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- ═════════ GRILLE PRINCIPALE ═════════ --}}
    <div class="card data-card">
        <div class="table-responsive" style="max-height: calc(100vh - 380px); overflow-y: auto;">
            <table class="table table-sm planif-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:8%;">Code</th>
                        <th style="width:24%;">Libellé / Commentaire</th>
                        <th class="text-end" style="width:13%;">Dotation État</th>
                        <th class="text-end" style="width:13%;">Fonds propres</th>
                        <th class="text-end" style="width:11%;">Reports budg.</th>
                        <th class="text-end" style="width:11%;">Reports trés.</th>
                        <th class="text-end" style="width:14%;">Total ligne</th>
                        <th style="width:6%;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($titres as $titre)
                    @php $lignesTitre = $titre->lignes; @endphp
                    @if($lignesTitre->isEmpty()) @continue @endif

                    {{-- En-tête du titre --}}
                    <tr class="titre-row">
                        <td colspan="8">
                            <span class="titre-imputation">{{ $titre->imputation }}</span>
                            {{ $titre->libelle }}
                            @if($titre->type_ligne)
                                <span class="badge bg-{{ $titre->type_ligne === 'recette' ? 'success' : ($titre->type_ligne === 'depense' ? 'danger' : 'info') }} ms-2">
                                    {{ ucfirst($titre->type_ligne) }}
                                </span>
                            @endif
                            <small class="text-muted ms-2">({{ $lignesTitre->count() }} ligne(s))</small>
                        </td>
                    </tr>

                    {{-- Lignes du titre --}}
                    @foreach($lignesTitre as $ligne)
                        @php
                            $bl = $budgetLignesIndex->get($ligne->id);
                            $dot  = $bl ? (float) $bl->dotation_etat : 0;
                            $fp   = $bl ? (float) $bl->fonds_propres : 0;
                            $repB = $bl ? (float) $bl->reports_budgetaire : 0;
                            $repT = $bl ? (float) $bl->reports_tresorerie : 0;
                            $total = $dot + $fp + $repB + $repT;
                            $hasEngagement = $bl && (float) $bl->engagement > 0;
                            $rowClass = $total <= 0 ? 'row-vide' : '';
                        @endphp
                        <tr class="ligne-row {{ $rowClass }}" data-titre="{{ $titre->imputation }}">
                            <td><code class="small">{{ $ligne->id_codeanalytique }}</code></td>
                            <td>
                                @if($exercice->peut_planifier)
                                    <input type="text" name="ref[{{ $ligne->id }}][commentaire]"
                                           class="form-control form-control-sm"
                                           value="{{ $bl?->commentaire ?? $ligne->libelle }}"
                                           placeholder="{{ $ligne->libelle }}">
                                @else
                                    <strong>{{ $bl?->commentaire ?? $ligne->libelle }}</strong>
                                    @if($bl?->commentaire) <small class="text-muted d-block">({{ $ligne->libelle }})</small>@endif
                                @endif
                            </td>
                            <td class="text-end">
                                @if($exercice->peut_planifier)
                                    <input type="number" step="0.01" min="0" name="ref[{{ $ligne->id }}][dotation_etat]"
                                           class="form-control form-control-sm numeric input-dot" value="{{ $dot ?: '' }}" placeholder="0">
                                @else
                                    {{ number_format($dot, 0, ',', ' ') }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if($exercice->peut_planifier)
                                    <input type="number" step="0.01" min="0" name="ref[{{ $ligne->id }}][fonds_propres]"
                                           class="form-control form-control-sm numeric input-fp" value="{{ $fp ?: '' }}" placeholder="0">
                                @else
                                    {{ number_format($fp, 0, ',', ' ') }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if($exercice->peut_planifier)
                                    <input type="number" step="0.01" min="0" name="ref[{{ $ligne->id }}][reports_budgetaire]"
                                           class="form-control form-control-sm numeric input-repb" value="{{ $repB ?: '' }}" placeholder="0">
                                @else
                                    {{ number_format($repB, 0, ',', ' ') }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if($exercice->peut_planifier)
                                    <input type="number" step="0.01" min="0" name="ref[{{ $ligne->id }}][reports_tresorerie]"
                                           class="form-control form-control-sm numeric input-rept" value="{{ $repT ?: '' }}" placeholder="0">
                                @else
                                    {{ number_format($repT, 0, ',', ' ') }}
                                @endif
                            </td>
                            <td class="text-end total-cell cell-total">{{ number_format($total, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @if($hasEngagement)
                                    <i class="fas fa-lock text-warning" title="Cette ligne a des engagements"></i>
                                @elseif($exercice->peut_planifier && $bl)
                                    <form action="{{ route('finance.exercices.planification.lignes.retirer', [$exercice, $bl]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Retirer cette ligne de la planification ? Elle sera masquée mais restera récupérable.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Retirer de la planification">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- Sous-total du titre --}}
                    <tr class="subtotal-row" data-subtotal-titre="{{ $titre->imputation }}">
                        <td colspan="2" class="text-end">Sous-total {{ $titre->imputation }}</td>
                        <td class="text-end" data-st="dot">0</td>
                        <td class="text-end" data-st="fp">0</td>
                        <td class="text-end" data-st="repb">0</td>
                        <td class="text-end" data-st="rept">0</td>
                        <td class="text-end" data-st="total">0</td>
                        <td></td>
                    </tr>
                @endforeach

                {{-- ─── LIGNES LIBRES (hors référentiel) ─── --}}
                @if($lignesLibres->isNotEmpty() || $exercice->peut_planifier)
                    <tr class="titre-row">
                        <td colspan="8">
                            <span class="titre-imputation" style="background:#f59e0b;">Libre</span>
                            Lignes budgétaires libres (hors référentiel)
                        </td>
                    </tr>
                    <tbody id="libresBody">
                    @foreach($lignesLibres as $idx => $bl)
                        @php
                            $dot = (float) $bl->dotation_etat;
                            $fp  = (float) $bl->fonds_propres;
                            $rb  = (float) $bl->reports_budgetaire;
                            $rt  = (float) $bl->reports_tresorerie;
                            $tot = $dot + $fp + $rb + $rt;
                        @endphp
                        <tr class="ligne-row libre-row">
                            <td>
                                <input type="hidden" name="libres[{{ $idx }}][id]" value="{{ $bl->id }}" class="input-id">
                                <input type="hidden" name="libres[{{ $idx }}][_delete]" value="0" class="input-delete">
                                <input type="text" name="libres[{{ $idx }}][id_budgetligne]" class="form-control form-control-sm" value="{{ $bl->id_budgetligne }}">
                            </td>
                            <td><input type="text" name="libres[{{ $idx }}][commentaire]" class="form-control form-control-sm" value="{{ $bl->commentaire }}"></td>
                            <td class="text-end"><input type="number" step="0.01" min="0" name="libres[{{ $idx }}][dotation_etat]" class="form-control form-control-sm numeric input-dot" value="{{ $dot }}"></td>
                            <td class="text-end"><input type="number" step="0.01" min="0" name="libres[{{ $idx }}][fonds_propres]" class="form-control form-control-sm numeric input-fp" value="{{ $fp }}"></td>
                            <td class="text-end"><input type="number" step="0.01" min="0" name="libres[{{ $idx }}][reports_budgetaire]" class="form-control form-control-sm numeric input-repb" value="{{ $rb }}"></td>
                            <td class="text-end"><input type="number" step="0.01" min="0" name="libres[{{ $idx }}][reports_tresorerie]" class="form-control form-control-sm numeric input-rept" value="{{ $rt }}"></td>
                            <td class="text-end total-cell cell-total">{{ number_format($tot, 0, ',', ' ') }}</td>
                            <td><button type="button" class="btn btn-sm btn-link text-danger btn-supprimer-libre" title="Supprimer"><i class="fas fa-times"></i></button></td>
                        </tr>
                    @endforeach
                    </tbody>
                    @if($exercice->peut_planifier)
                        <tr>
                            <td colspan="8" class="text-center text-muted py-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAjouterLibre">
                                    <i class="fas fa-plus me-1"></i> Ajouter une ligne libre
                                </button>
                            </td>
                        </tr>
                    @endif
                @endif
                </tbody>

                <tfoot class="table-light">
                    <tr class="fw-bold fs-6">
                        <td colspan="2" class="text-end">GRAND TOTAL</td>
                        <td class="text-end" id="gtDot">0</td>
                        <td class="text-end" id="gtFP">0</td>
                        <td class="text-end" id="gtRepB">0</td>
                        <td class="text-end" id="gtRepT">0</td>
                        <td class="text-end fs-5 text-primary" id="gtTotal">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($exercice->peut_planifier)
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> Toutes les lignes du référentiel sont matérialisées à la création de l'exercice ; vous pouvez retirer une ligne non pertinente via l'icône <i class="fas fa-eye-slash"></i>.
                </small>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer la planification</button>
                </div>
            </div>
        @endif
    </div>
</form>

{{-- ═════════ LIGNES RETIRÉES DE LA PLANIFICATION ═════════ --}}
@if($lignesRetirees->isNotEmpty())
<div class="card data-card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-eye-slash text-muted me-2"></i> Lignes retirées de la planification ({{ $lignesRetirees->count() }})</h6>
        <small class="text-muted">Ces lignes du référentiel sont masquées de la grille active. Elles restent récupérables.</small>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:12%">Code</th>
                    <th>Libellé</th>
                    <th>Titre</th>
                    @if($exercice->peut_planifier)<th class="text-end" style="width:14%">Action</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($lignesRetirees as $bl)
                    <tr>
                        <td><code class="small">{{ $bl->ligne?->id_codeanalytique ?? '—' }}</code></td>
                        <td>{{ $bl->commentaire ?: $bl->ligne?->libelle }}</td>
                        <td class="text-muted">{{ $bl->ligne?->titre?->libelle ?? '—' }}</td>
                        @if($exercice->peut_planifier)
                        <td class="text-end">
                            <form action="{{ route('finance.exercices.planification.lignes.remettre', [$exercice, $bl]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-arrow-rotate-left me-1"></i> Remettre
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($exercice->peut_soumettre || $exercice->budgetLignes()->exists())
<div class="modal fade" id="modalSoumettre" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.exercices.soumettre', $exercice) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title">Soumettre la planification pour validation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Une fois soumise, vous ne pourrez plus modifier les lignes tant que le top management n'aura pas validé ou rejeté.</p>
                <p class="mb-0"><strong>Pensez à enregistrer vos modifications avant de soumettre.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Confirmer la soumission</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    const filtreVides = document.getElementById('filtreVides');
    const tbody = document.querySelector('.planif-table');

    function fmt(n) { return Number(n || 0).toLocaleString('fr-FR', {maximumFractionDigits: 0}); }
    function readVal(input) { return parseFloat(input?.value || '') || 0; }

    // Recalcul d'une ligne
    function recalculerLigne(tr) {
        const dot = readVal(tr.querySelector('.input-dot'));
        const fp  = readVal(tr.querySelector('.input-fp'));
        const rb  = readVal(tr.querySelector('.input-repb'));
        const rt  = readVal(tr.querySelector('.input-rept'));
        const total = dot + fp + rb + rt;
        const cell = tr.querySelector('.cell-total');
        if (cell) cell.textContent = fmt(total);
        // Visuel : ligne vide
        if (total <= 0) tr.classList.add('row-vide');
        else tr.classList.remove('row-vide');
        return { dot, fp, rb, rt, total };
    }

    // Recalcul global
    function recalculerTout() {
        const sousTotaux = {};
        let gDot=0, gFP=0, gRB=0, gRT=0, gTotal=0, nbRenseignees=0;

        tbody.querySelectorAll('tr.ligne-row').forEach(tr => {
            const { dot, fp, rb, rt, total } = recalculerLigne(tr);
            if (total > 0) nbRenseignees++;
            gDot+=dot; gFP+=fp; gRB+=rb; gRT+=rt; gTotal+=total;

            const titre = tr.dataset.titre;
            if (titre) {
                if (!sousTotaux[titre]) sousTotaux[titre] = { dot:0, fp:0, rb:0, rt:0, total:0 };
                sousTotaux[titre].dot   += dot;
                sousTotaux[titre].fp    += fp;
                sousTotaux[titre].rb    += rb;
                sousTotaux[titre].rt    += rt;
                sousTotaux[titre].total += total;
            }
        });

        // Maj sous-totaux par titre
        Object.keys(sousTotaux).forEach(t => {
            const tr = document.querySelector(`tr[data-subtotal-titre="${t}"]`);
            if (!tr) return;
            tr.querySelector('[data-st="dot"]').textContent   = fmt(sousTotaux[t].dot);
            tr.querySelector('[data-st="fp"]').textContent    = fmt(sousTotaux[t].fp);
            tr.querySelector('[data-st="repb"]').textContent  = fmt(sousTotaux[t].rb);
            tr.querySelector('[data-st="rept"]').textContent  = fmt(sousTotaux[t].rt);
            tr.querySelector('[data-st="total"]').textContent = fmt(sousTotaux[t].total);
        });

        // Maj grand total + KPIs
        document.getElementById('gtDot').textContent  = fmt(gDot);
        document.getElementById('gtFP').textContent   = fmt(gFP);
        document.getElementById('gtRepB').textContent = fmt(gRB);
        document.getElementById('gtRepT').textContent = fmt(gRT);
        document.getElementById('gtTotal').textContent= fmt(gTotal);
        document.getElementById('kpiDot').textContent   = fmt(gDot);
        document.getElementById('kpiFP').textContent    = fmt(gFP);
        document.getElementById('kpiRep').textContent   = fmt(gRB + gRT);
        document.getElementById('kpiTotal').textContent = fmt(gTotal);
        document.getElementById('nbRenseignees').textContent = nbRenseignees;
    }

    // Listeners
    tbody.querySelectorAll('input.numeric').forEach(inp => inp.addEventListener('input', recalculerTout));

    // Filtre lignes vides
    if (filtreVides) {
        filtreVides.addEventListener('change', () => {
            tbody.querySelectorAll('tr.ligne-row').forEach(tr => {
                if (filtreVides.checked && tr.classList.contains('row-vide')) tr.style.display = 'none';
                else tr.style.display = '';
            });
        });
    }

    // Suppression de ligne libre
    document.querySelectorAll('.btn-supprimer-libre').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const idInput = tr.querySelector('.input-id');
            if (idInput && idInput.value) {
                tr.querySelector('.input-delete').value = '1';
                tr.style.display = 'none';
            } else {
                tr.remove();
            }
            recalculerTout();
        });
    });

    @if($exercice->peut_planifier)
    // Ajout de ligne libre
    document.getElementById('btnAjouterLibre')?.addEventListener('click', () => {
        const body = document.getElementById('libresBody');
        const idx = body.querySelectorAll('tr').length + 99;
        const tr = document.createElement('tr');
        tr.className = 'ligne-row libre-row';

        function inp(name, type, cls) {
            const i = document.createElement('input');
            i.type = type; i.name = `libres[${idx}][${name}]`;
            i.className = `form-control form-control-sm ${cls || ''}`;
            if (type === 'number') { i.step = '0.01'; i.min = '0'; }
            return i;
        }

        const tdCode = document.createElement('td');
        tdCode.appendChild(inp('id_budgetligne', 'text'));
        const tdLib = document.createElement('td');
        tdLib.appendChild(inp('commentaire', 'text'));
        const tdDot  = document.createElement('td'); tdDot.className  = 'text-end'; tdDot.appendChild(inp('dotation_etat','number','numeric input-dot'));
        const tdFP   = document.createElement('td'); tdFP.className   = 'text-end'; tdFP.appendChild(inp('fonds_propres','number','numeric input-fp'));
        const tdRepB = document.createElement('td'); tdRepB.className = 'text-end'; tdRepB.appendChild(inp('reports_budgetaire','number','numeric input-repb'));
        const tdRepT = document.createElement('td'); tdRepT.className = 'text-end'; tdRepT.appendChild(inp('reports_tresorerie','number','numeric input-rept'));
        const tdTot = document.createElement('td'); tdTot.className = 'text-end total-cell cell-total'; tdTot.textContent = '0';
        const tdSup = document.createElement('td');
        const btnSup = document.createElement('button');
        btnSup.type = 'button'; btnSup.className = 'btn btn-sm btn-link text-danger';
        const icoSup = document.createElement('i'); icoSup.className = 'fas fa-times';
        btnSup.appendChild(icoSup);
        btnSup.addEventListener('click', () => { tr.remove(); recalculerTout(); });
        tdSup.appendChild(btnSup);

        tr.append(tdCode, tdLib, tdDot, tdFP, tdRepB, tdRepT, tdTot, tdSup);
        body.appendChild(tr);
        tr.querySelectorAll('input.numeric').forEach(i => i.addEventListener('input', recalculerTout));
    });
    @endif

    recalculerTout();
})();
</script>
@endpush
@endsection
