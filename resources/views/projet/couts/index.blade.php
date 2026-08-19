@extends('layouts.app')
@section('title', $projet->nom . ' — Coûts')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Coûts</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'couts'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-coins"></i></span>
                Coûts — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Suivi budgétaire en cascade : Tâches → Phases → Projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalCout">
            <i class="fas fa-plus me-2"></i> Nouveau coût
        </button>
    </div>

    {{-- KPI Cascade --}}
    @php
        $totalEstime  = $projet->cout_total_estime;
        $totalReel    = $projet->budget_consomme;
        $ecartGlobal  = $totalReel - $totalEstime;
        $budgetAppr   = $projet->budget_approuve ?? 0;
        $budgetRestant = $budgetAppr - $totalReel;
        $pctBudget    = $budgetAppr > 0 ? round($totalReel / $budgetAppr * 100, 1) : 0;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #0D9488;">
                <div class="stat-label">Total estimé (cascade)</div>
                <div class="stat-value" style="color:#0D9488;">{{ number_format($totalEstime, 0, ',', ' ') }} {{ $projet->devise }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #0F766E;">
                <div class="stat-label">Total réel (cascade)</div>
                <div class="stat-value" style="color:#0F766E;">{{ number_format($totalReel, 0, ',', ' ') }} {{ $projet->devise }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid {{ $ecartGlobal > 0 ? '#DC2626' : '#16A34A' }};">
                <div class="stat-label">Écart global</div>
                <div class="stat-value" style="color:{{ $ecartGlobal > 0 ? '#DC2626' : '#16A34A' }};">
                    {{ $ecartGlobal > 0 ? '+' : '' }}{{ number_format($ecartGlobal, 0, ',', ' ') }} {{ $projet->devise }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #6366F1;">
                <div class="stat-label">Budget approuvé</div>
                <div class="stat-value" style="color:#6366F1;">{{ number_format($budgetAppr, 0, ',', ' ') }} {{ $projet->devise }}</div>
                @if($budgetAppr > 0)
                <div class="tache-progress-bar mt-1" style="height:6px;">
                    <div class="tache-progress-fill" style="width:{{ min($pctBudget, 100) }}%;background:{{ $pctBudget > 100 ? '#DC2626' : ($pctBudget > 80 ? '#F59E0B' : '#16A34A') }};"></div>
                </div>
                <small style="font-size:.68rem;color:#64748B;">{{ $pctBudget }}% consommé · Restant : {{ number_format($budgetRestant, 0, ',', ' ') }} {{ $projet->devise }}</small>
                @endif
            </div>
        </div>
    </div>

    {{-- Répartition par phase (cascade) --}}
    @if($phases->count())
    <div class="form-card mb-4">
        <h6 style="font-size:.82rem;font-weight:700;color:#0F172A;margin-bottom:.8rem;">
            <i class="fas fa-sitemap me-2" style="color:#0D9488;"></i> Répartition des coûts par phase
        </h6>
        <table class="opp-table" style="font-size:.78rem;">
            <thead>
                <tr>
                    <th>Phase</th>
                    <th style="text-align:right;">Coût estimé</th>
                    <th style="text-align:right;">Coût réel</th>
                    <th style="text-align:right;">Écart</th>
                    <th style="text-align:center;">Tâches</th>
                    <th style="width:120px;">% du budget</th>
                </tr>
            </thead>
            <tbody>
                @foreach($phases as $phase)
                @php
                    $phEstime = $phase->cout_estime;
                    $phReel   = $phase->cout_reel;
                    $phEcart  = $phReel - $phEstime;
                    $phPct    = $budgetAppr > 0 ? round($phReel / $budgetAppr * 100, 1) : 0;
                @endphp
                <tr>
                    <td>
                        <span class="wbs-phase-code" style="font-size:.7rem;background:{{ $phase->couleur ?? '#0D9488' }};color:#fff;padding:.15em .5em;border-radius:4px;">{{ $phase->code_wbs ?? 'WBS-' . $phase->ordre }}</span>
                        <strong class="ms-1">{{ $phase->nom }}</strong>
                    </td>
                    <td style="text-align:right;">{{ number_format($phEstime, 0, ',', ' ') }}</td>
                    <td style="text-align:right;">{{ number_format($phReel, 0, ',', ' ') }}</td>
                    <td style="text-align:right;color:{{ $phEcart > 0 ? '#DC2626' : '#16A34A' }};">
                        {{ $phEcart > 0 ? '+' : '' }}{{ number_format($phEcart, 0, ',', ' ') }}
                    </td>
                    <td style="text-align:center;">{{ $phase->taches->count() }}</td>
                    <td>
                        <div class="tache-progress-bar" style="height:6px;">
                            <div class="tache-progress-fill" style="width:{{ min($phPct, 100) }}%;background:{{ $phase->couleur ?? '#0D9488' }};"></div>
                        </div>
                        <small style="font-size:.65rem;color:#64748B;">{{ $phPct }}%</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:700;border-top:2px solid #E2E8F0;">
                    <td>Total phases</td>
                    <td style="text-align:right;">{{ number_format($phases->sum(fn($p) => $p->cout_estime), 0, ',', ' ') }}</td>
                    <td style="text-align:right;">{{ number_format($phases->sum(fn($p) => $p->cout_reel), 0, ',', ' ') }}</td>
                    <td style="text-align:right;color:{{ $phases->sum(fn($p) => $p->ecart_cout) > 0 ? '#DC2626' : '#16A34A' }};">
                        @php $ecartPhases = $phases->sum(fn($p) => $p->ecart_cout); @endphp
                        {{ $ecartPhases > 0 ? '+' : '' }}{{ number_format($ecartPhases, 0, ',', ' ') }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Détail des lignes de coût --}}
    @if($couts->count())
    <div class="form-card">
        <h6 style="font-size:.82rem;font-weight:700;color:#0F172A;margin-bottom:.8rem;">
            <i class="fas fa-list me-2" style="color:#0D9488;"></i> Détail des lignes de coût
        </h6>
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Catégorie</th>
                    <th>Phase</th>
                    <th>Tâche</th>
                    <th style="text-align:right;">Estimé</th>
                    <th style="text-align:right;">Réel</th>
                    <th style="text-align:right;">Écart</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($couts->sortBy('date_cout') as $cout)
                <tr>
                    <td><strong>{{ $cout->libelle }}</strong></td>
                    <td><span class="opp-stage-badge" style="background:#0D9488;">{{ $cout->categorie ?? '—' }}</span></td>
                    <td>
                        @if($cout->phase)
                        <span style="font-size:.72rem;background:{{ $cout->phase->couleur ?? '#0D9488' }};color:#fff;padding:.1em .4em;border-radius:3px;">{{ $cout->phase->code_wbs ?? $cout->phase->nom }}</span>
                        @else
                        <span style="color:#94A3B8;font-size:.72rem;">Direct</span>
                        @endif
                    </td>
                    <td>{{ $cout->tache?->titre ?? '—' }}</td>
                    <td style="text-align:right;">{{ number_format($cout->montant_estime, 0, ',', ' ') }}</td>
                    <td style="text-align:right;">{{ number_format($cout->montant_reel, 0, ',', ' ') }}</td>
                    <td style="text-align:right;">
                        @php $ecart = $cout->montant_reel - $cout->montant_estime; @endphp
                        <span style="color:{{ $ecart > 0 ? '#DC2626' : '#16A34A' }};">
                            {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }}
                        </span>
                    </td>
                    <td>{{ $cout->date_cout?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editCout({{ $cout->id }}, '{{ addslashes($cout->libelle) }}', '{{ addslashes($cout->categorie ?? '') }}', '{{ $cout->phase_id }}', '{{ $cout->tache_id }}', '{{ $cout->montant_estime }}', '{{ $cout->montant_reel }}', '{{ $cout->date_cout?->format('Y-m-d') }}', '{{ addslashes($cout->notes ?? '') }}')">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.couts.destroy', [$projet, $cout]) }}" method="POST" onsubmit="return confirm('Supprimer ce coût ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:700;border-top:2px solid #E2E8F0;">
                    <td colspan="4">Total lignes de coût</td>
                    <td style="text-align:right;">{{ number_format($couts->sum('montant_estime'), 0, ',', ' ') }}</td>
                    <td style="text-align:right;">{{ number_format($couts->sum('montant_reel'), 0, ',', ' ') }}</td>
                    <td style="text-align:right;">
                        @php $ecartTotal = $couts->sum('montant_reel') - $couts->sum('montant_estime'); @endphp
                        <span style="color:{{ $ecartTotal > 0 ? '#DC2626' : '#16A34A' }};">
                            {{ $ecartTotal > 0 ? '+' : '' }}{{ number_format($ecartTotal, 0, ',', ' ') }}
                        </span>
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-coins" style="color:#0D9488;"></i></div>
        <h3>Aucun coût enregistré</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalCout">
            <i class="fas fa-plus me-2"></i> Ajouter un coût
        </button>
    </div>
    @endif
</div>

{{-- Modale coût --}}
<div class="modal fade" id="modalCout" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="coutForm" method="POST" class="modal-content" action="{{ route('projet.couts.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="coutMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="coutModalTitle">Nouveau coût</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="coutLibelle" class="form-control" required></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Catégorie</label>
                        <select name="categorie" id="coutCategorie" class="form-select">
                            <option value="">— Aucune —</option>
                            <option value="main_oeuvre">Main d'oeuvre</option>
                            <option value="materiel">Matériel</option>
                            <option value="logiciel">Logiciel</option>
                            <option value="sous_traitance">Sous-traitance</option>
                            <option value="formation">Formation</option>
                            <option value="deplacement">Déplacement</option>
                            <option value="autre">Autre</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Phase</label>
                        <select name="phase_id" id="coutPhase" class="form-select">
                            <option value="">— Coût direct projet —</option>
                            @foreach($phases as $ph)
                                <option value="{{ $ph->id }}">{{ $ph->code_wbs ?? 'WBS-'.$ph->ordre }} — {{ $ph->nom }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Tâche</label>
                        <select name="tache_id" id="coutTache" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($projet->taches ?? [] as $t)
                                <option value="{{ $t->id }}" data-phase="{{ $t->phase_id }}">{{ $t->titre }}</option>
                            @endforeach
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Montant estimé</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="montant_estime" id="coutEstime" class="form-control" value="0">
                            <span class="input-group-text" style="font-size:.75rem;">{{ $projet->devise }}</span>
                        </div></div>
                    <div class="col-md-4"><label class="form-label">Montant réel</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="montant_reel" id="coutReel" class="form-control" value="0">
                            <span class="input-group-text" style="font-size:.75rem;">{{ $projet->devise }}</span>
                        </div></div>
                    <div class="col-md-4"><label class="form-label">Date</label>
                        <input type="date" name="date_cout" id="coutDate" class="form-control"></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Notes</label>
                    <textarea name="notes" id="coutNotes" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-coins me-2"></i> <span id="coutSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto-sélection de la phase quand une tâche est choisie
document.getElementById('coutTache')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const phaseId = opt?.dataset?.phase || '';
    if (phaseId) {
        document.getElementById('coutPhase').value = phaseId;
    }
});

function editCout(id, libelle, categorie, phaseId, tacheId, estime, reel, date, notes) {
    document.getElementById('coutModalTitle').textContent = 'Modifier le coût';
    document.getElementById('coutLibelle').value = libelle;
    document.getElementById('coutCategorie').value = categorie;
    document.getElementById('coutPhase').value = phaseId || '';
    document.getElementById('coutTache').value = tacheId || '';
    document.getElementById('coutEstime').value = estime;
    document.getElementById('coutReel').value = reel;
    document.getElementById('coutDate').value = date || '';
    document.getElementById('coutNotes').value = notes;
    document.getElementById('coutMethod').value = 'PUT';
    document.getElementById('coutSubmitText').textContent = 'Enregistrer';
    document.getElementById('coutForm').action = '/projet/{{ $projet->id }}/couts/' + id;
    new bootstrap.Modal(document.getElementById('modalCout')).show();
}

document.getElementById('modalCout')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('coutModalTitle').textContent = 'Nouveau coût';
    ['coutLibelle','coutDate','coutNotes'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('coutEstime').value = '0';
    document.getElementById('coutReel').value = '0';
    document.getElementById('coutCategorie').value = '';
    document.getElementById('coutPhase').value = '';
    document.getElementById('coutTache').value = '';
    document.getElementById('coutMethod').value = 'POST';
    document.getElementById('coutSubmitText').textContent = 'Enregistrer';
    document.getElementById('coutForm').action = '{{ route("projet.couts.store", $projet) }}';
});
</script>
@endpush
@endsection
