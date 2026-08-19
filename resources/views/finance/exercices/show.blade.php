@extends('layouts.app')

@section('title', 'D&eacute;tail exercice')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.exercices.index') }}">Exercices</a></li>
        <li class="breadcrumb-item active">{{ $exercice->libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
    <div>
        <h1 class="h3 mb-1">{{ $exercice->libelle }}</h1>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $exercice->statut_couleur }}">{{ $exercice->statut_libelle }}</span>
            @if($exercice->type_planification)
                <span class="badge bg-{{ $exercice->type_planification_couleur }}">
                    <i class="fas {{ $exercice->type_planification_icone }} me-1"></i>{{ ucfirst($exercice->type_planification) }}
                </span>
            @endif
            @if($exercice->validation_statut == 1)
                <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> En attente validation top management</span>
            @elseif($exercice->validation_statut == 2)
                <span class="badge bg-success"><i class="fas fa-check me-1"></i> Planification validée</span>
            @endif
            @if($exercice->en_attente_cloture)
                <span class="badge bg-danger"><i class="fas fa-triangle-exclamation me-1"></i> En attente de clôture</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update:exercice')
            {{-- Bouton planification : toujours visible, libellé adapté à l'état --}}
            @php
                $planifLabel = match (true) {
                    $exercice->peut_planifier        => ['libellé' => 'Planifier le budget',          'classe' => 'btn-primary',       'icone' => 'fa-clipboard-list'],
                    (int)$exercice->validation_statut === 1 => ['libellé' => 'Voir planification (soumise)',     'classe' => 'btn-outline-info',  'icone' => 'fa-eye'],
                    $exercice->statut == 2           => ['libellé' => 'Voir budget (en exécution)',    'classe' => 'btn-outline-success','icone' => 'fa-table'],
                    $exercice->statut == 3           => ['libellé' => 'Voir budget (clôturé)',         'classe' => 'btn-outline-secondary','icone' => 'fa-archive'],
                    default                          => ['libellé' => 'Voir la planification',         'classe' => 'btn-outline-primary','icone' => 'fa-clipboard-list'],
                };
            @endphp
            <a href="{{ route('finance.exercices.planification', $exercice) }}" class="btn {{ $planifLabel['classe'] }}">
                <i class="fas {{ $planifLabel['icone'] }} me-1"></i> {{ $planifLabel['libellé'] }}
            </a>
            @if($exercice->peutEtreModifie())
                <a href="{{ route('finance.exercices.edit', $exercice) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-pen me-1"></i> Modifier l'exercice
                </a>
            @elseif(auth()->user()->hasRole('super-admin'))
                <a href="{{ route('finance.exercices.edit', $exercice) }}" class="btn btn-outline-warning"
                   title="[Super-admin] État verrouillé pour les utilisateurs standards. Vous pouvez modifier — la trace d'audit sera préservée.">
                    <i class="fas fa-shield-halved me-1"></i> Forcer modification
                </a>
            @endif
            @if($exercice->peutEtreSupprime())
                <form action="{{ route('finance.exercices.destroy', $exercice) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer cet exercice ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> Supprimer</button>
                </form>
            @elseif(auth()->user()->hasRole('super-admin'))
                <form action="{{ route('finance.exercices.destroy', $exercice) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('[SUPER-ADMIN] Cet exercice est en état {{ $exercice->statut_libelle }}. Sa suppression forcera aussi la purge de toutes ses lignes budgétaires. Cette opération est IRRÉVERSIBLE. Confirmez-vous ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger" title="[Super-admin] Suppression forcée — état normalement verrouillé.">
                        <i class="fas fa-shield-halved me-1"></i> Forcer suppression
                    </button>
                </form>
            @endif
        @endcan
        @can('validate:exercice')
            @if($exercice->peut_valider)
                <form action="{{ route('finance.exercices.valider', $exercice) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Valider la planification ? L\'exercice passera immédiatement en EXÉCUTION.');">@csrf
                    <button class="btn btn-success"><i class="fas fa-check me-1"></i> Valider (top management)</button>
                </form>
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalRejet">
                    <i class="fas fa-times me-1"></i> Rejeter
                </button>
            @endif
            @if($exercice->peut_cloturer)
                <form action="{{ route('finance.exercices.cloturer', $exercice) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Clôturer définitivement l\'exercice ? Cette action est irréversible.');">@csrf
                    <button class="btn btn-dark"><i class="fas fa-lock me-1"></i> Clôturer l'exercice</button>
                </form>
            @endif
            @if($exercice->peutEtreAnnule())
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalAnnuler">
                    <i class="fas fa-ban me-1"></i> Annuler l'exercice
                </button>
            @endif
        @endcan
        <a href="{{ route('finance.exercices.index') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

{{-- ═════════ STEPPER DU WORKFLOW EXERCICE ═════════ --}}
@php
    $s   = (int) $exercice->statut;
    $vs  = (int) $exercice->validation_statut;
    $STAT = \App\Models\Exercice::class;
    // Détermine l'état de chaque étape : done | current | pending | skipped
    $etapes = [
        ['id' => 'creation',     'lbl' => 'Création',              'icone' => 'fa-file-circle-plus',
         'etat' => 'done'],
        ['id' => 'planification','lbl' => 'Planification',         'icone' => 'fa-clipboard-list',
         'etat' => $s === $STAT::STATUT_PLANIFICATION && $vs === 0 ? 'current'
                  : ($s === $STAT::STATUT_PLANIFICATION && $vs === 0 ? 'current' : 'done')],
        ['id' => 'soumission',   'lbl' => 'Soumission',            'icone' => 'fa-paper-plane',
         'etat' => $vs === 1 ? 'current' : ($vs >= 2 || $s >= 2 ? 'done' : 'pending')],
        ['id' => 'execution',    'lbl' => 'En exécution',          'icone' => 'fa-play',
         'etat' => $s === $STAT::STATUT_EN_EXECUTION ? 'current'
                  : ($s === $STAT::STATUT_CLOTURE ? 'done' : 'pending')],
        ['id' => 'cloture',      'lbl' => 'Clôturé',               'icone' => 'fa-lock',
         'etat' => $s === $STAT::STATUT_CLOTURE ? 'done'
                  : ($s === $STAT::STATUT_ANNULE ? 'skipped' : 'pending')],
    ];
    // Prochaine action recommandée
    $prochaineAction = null;
    if ($s === $STAT::STATUT_PLANIFICATION && $vs === 0) {
        $prochaineAction = ['label' => 'Renseignez les lignes budgétaires puis soumettez la planification au top management.',
                            'cta'   => 'Ouvrir la planification',
                            'href'  => route('finance.exercices.planification', $exercice),
                            'icon'  => 'fa-clipboard-list'];
    } elseif ($vs === 1) {
        $prochaineAction = ['label' => 'La planification est soumise. Un membre du top management doit la valider (ou la rejeter).',
                            'cta'   => null, 'icon' => 'fa-hourglass-half'];
    } elseif ($s === $STAT::STATUT_EN_EXECUTION) {
        $prochaineAction = ['label' => 'L\'exercice est en exécution. Vous pouvez le clôturer (fin normale) ou l\'annuler (avec motif).',
                            'cta'   => null, 'icon' => 'fa-lock'];
    } elseif ($s === $STAT::STATUT_CLOTURE) {
        $prochaineAction = ['label' => 'Exercice clôturé — archivé.', 'cta' => null, 'icon' => 'fa-check-double'];
    } elseif ($s === $STAT::STATUT_ANNULE) {
        $prochaineAction = ['label' => 'Exercice annulé — plus aucune action possible.', 'cta' => null, 'icon' => 'fa-ban'];
    }
@endphp

<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="workflow-stepper d-flex flex-wrap align-items-center gap-2">
                @foreach($etapes as $i => $etape)
                    @php
                        $classe = match ($etape['etat']) {
                            'done'    => 'bg-success text-white border-success',
                            'current' => 'bg-primary text-white border-primary',
                            'skipped' => 'bg-warning text-dark border-warning',
                            default   => 'bg-white text-muted border-secondary-subtle',
                        };
                    @endphp
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center border {{ $classe }}"
                             style="width:32px;height:32px;font-size:.85em;" title="{{ $etape['lbl'] }}">
                            <i class="fas {{ $etape['icone'] }}"></i>
                        </div>
                        <span class="small {{ $etape['etat'] === 'current' ? 'fw-bold' : 'text-muted' }}">
                            {{ $etape['lbl'] }}
                        </span>
                        @if(!$loop->last)
                            <i class="fas fa-chevron-right text-muted small mx-1"></i>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @if($prochaineAction)
            <div class="alert alert-info mb-0 d-flex align-items-center flex-wrap gap-3">
                <i class="fas {{ $prochaineAction['icon'] }} fa-lg"></i>
                <div class="flex-grow-1"><strong>Prochaine étape :</strong> {{ $prochaineAction['label'] }}</div>
                @if(!empty($prochaineAction['cta']) && !empty($prochaineAction['href']))
                    <a href="{{ $prochaineAction['href'] }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right me-1"></i> {{ $prochaineAction['cta'] }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Alerte motif de rejet --}}
@if($exercice->motif_rejet && $exercice->validation_statut == 0)
    <div class="alert alert-danger mt-3">
        <strong><i class="fas fa-exclamation-triangle me-1"></i> Planification précédemment rejetée :</strong> {{ $exercice->motif_rejet }}
    </div>
@endif

{{-- Bandeau annulation --}}
@if((int) $exercice->statut === \App\Models\Exercice::STATUT_ANNULE)
    <div class="alert alert-warning mt-3">
        <h6 class="mb-1"><i class="fas fa-ban me-1"></i> Exercice annulé le {{ $exercice->annule_at?->format('d/m/Y H:i') }} par {{ $exercice->annulePar?->name ?? '—' }}</h6>
        <div class="mt-1"><strong>Motif :</strong> {{ $exercice->motif_annulation }}</div>
    </div>
@endif

{{-- Bandeau workflow --}}
@if($exercice->soumis_at || $exercice->valide_at || $exercice->cloture_at)
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row text-center">
            @if($exercice->soumis_at)
                <div class="col-md-4">
                    <small class="text-muted d-block">Soumis pour validation</small>
                    <strong>{{ $exercice->soumis_at->translatedFormat('d M Y H:i') }}</strong>
                    <br><small class="text-muted">par {{ $exercice->soumetteur?->name ?? '—' }}</small>
                </div>
            @endif
            @if($exercice->valide_at)
                <div class="col-md-4">
                    <small class="text-muted d-block">{{ $exercice->validation_statut == 2 ? 'Validé par' : 'Rejeté par' }}</small>
                    <strong>{{ $exercice->valide_at->translatedFormat('d M Y H:i') }}</strong>
                    <br><small class="text-muted">par {{ $exercice->validateur?->name ?? '—' }}</small>
                </div>
            @endif
            @if($exercice->cloture_at)
                <div class="col-md-4">
                    <small class="text-muted d-block">Clôturé</small>
                    <strong>{{ $exercice->cloture_at->translatedFormat('d M Y H:i') }}</strong>
                    <br><small class="text-muted">par {{ $exercice->cloturePar?->name ?? '—' }}</small>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Modal rejet --}}
@can('validate:exercice')
@if($exercice->peut_rejeter)
<div class="modal fade" id="modalRejet" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.exercices.rejeter', $exercice) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title">Rejeter la planification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>L'exercice retournera en mode planification. Le créateur pourra corriger et resoumettre.</p>
                <div class="mb-0">
                    <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                    <textarea name="motif_rejet" class="form-control" rows="3" required maxlength="500"
                              placeholder="Ex : budget total dépasse la dotation, lignes mal réparties…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-danger">Rejeter</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Modal annulation --}}
@can('validate:exercice')
@if($exercice->peutEtreAnnule())
<div class="modal fade" id="modalAnnuler" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.exercices.annuler', $exercice) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban text-warning me-2"></i> Annuler l'exercice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>{{ $exercice->libelle }}</strong> passera au statut <strong>Annulé</strong>.
                    Cette action est <strong>irréversible</strong>. L'exercice conservera sa trace pour l'audit,
                    mais ne pourra plus être exécuté ni clôturé.
                </div>
                <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
                <textarea name="motif_annulation" class="form-control" rows="4" required minlength="10" maxlength="500"
                          placeholder="Justifier l'annulation de l'exercice (min. 10 caractères)…"></textarea>
                <small class="text-muted">Le motif sera consigné pour l'audit.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-warning"><i class="fas fa-ban me-1"></i> Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Informations --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-primary-soft">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Budget initial</div>
                    <div class="fw-bold fs-5">{{ number_format($exercice->budgetglobalinitial ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-success-soft">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Dotation globale</div>
                    <div class="fw-bold fs-5">{{ number_format($exercice->dotationglobale ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-info-soft">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">Fond propre global</div>
                    <div class="fw-bold fs-5">{{ number_format($exercice->fondpropreglobal ?? 0, 0, ',', ' ') }} F</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stats-icon bg-warning-soft">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.8rem;">État</div>
                    <div class="fw-bold">
                        <span class="badge bg-{{ $exercice->statut_couleur }}">{{ $exercice->statut_libelle }}</span>
                        @if((int) $exercice->validation_statut === 1)
                            <br><small class="text-warning">⏳ En attente validation</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════ PLANIFICATION BUDGÉTAIRE ════════ --}}
@php
    $lignes = $exercice->budgetLignes;
    $nbLignes = $lignes->count();
    $budgetCumule = $lignes->sum(fn($l) => (float) $l->budget_total);
    $engagementCumule = $lignes->sum(fn($l) => (float) $l->engagement);
@endphp

<div class="card data-card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong><i class="fas fa-clipboard-list me-1 text-muted"></i> Planification budgétaire</strong>
            @if($nbLignes > 0)
                <span class="badge bg-info ms-2">{{ $nbLignes }} ligne(s)</span>
            @endif
        </div>
        @can('update:exercice')
            <a href="{{ route('finance.exercices.planification', $exercice) }}" class="btn btn-sm {{ $exercice->peut_planifier ? 'btn-primary' : 'btn-outline-secondary' }}">
                @if($exercice->peut_planifier)
                    <i class="fas fa-edit me-1"></i> Éditer la planification
                @else
                    <i class="fas fa-eye me-1"></i> Voir la planification
                @endif
            </a>
        @endcan
    </div>
    <div class="card-body">
        @if($nbLignes === 0)
            {{-- Call-to-action : aucune planification --}}
            <div class="text-center py-4">
                <i class="fas fa-clipboard-list fs-1 text-muted opacity-50 d-block mb-3"></i>
                <h5 class="mb-2">Aucune ligne budgétaire planifiée</h5>
                <p class="text-muted mb-3">
                    Démarrez la planification pour répartir le budget de l'exercice sur les différentes lignes analytiques.
                </p>
                @can('update:exercice')
                    @if($exercice->peut_planifier)
                        <a href="{{ route('finance.exercices.planification', $exercice) }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-1"></i> Démarrer la planification
                        </a>
                    @endif
                @endcan
            </div>
        @else
            {{-- Synthèse + aperçu des lignes --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="border-start border-3 border-primary ps-3">
                        <small class="text-muted d-block">Budget total planifié</small>
                        <strong class="fs-4">{{ number_format($budgetCumule, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-start border-3 border-warning ps-3">
                        <small class="text-muted d-block">Engagé</small>
                        <strong class="fs-4">{{ number_format($engagementCumule, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF
                            @if($budgetCumule > 0)
                                ({{ round($engagementCumule / $budgetCumule * 100, 1) }}%)
                            @endif
                        </small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-start border-3 border-success ps-3">
                        <small class="text-muted d-block">Disponible</small>
                        <strong class="fs-4">{{ number_format($budgetCumule - $engagementCumule, 0, ',', ' ') }}</strong>
                        <small class="text-muted">XAF</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Titre</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Engagé</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lignes->take(8) as $l)
                            <tr>
                                <td><code class="small">{{ $l->id_budgetligne }}</code></td>
                                <td>{{ \Illuminate\Support\Str::limit($l->commentaire ?? $l->ligne?->libelle ?? '—', 40) }}</td>
                                <td><small>{{ $l->ligne?->titre?->libelle ?? '—' }}</small></td>
                                <td class="text-end">{{ number_format($l->budget_total, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format((float) $l->engagement, 0, ',', ' ') }}</td>
                                <td>
                                    @if((int) $l->isvalide === 1)
                                        <span class="badge bg-success">Validée</span>
                                    @else
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($lignes->count() > 8)
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            ... et {{ $lignes->count() - 8 }} ligne(s) supplémentaire(s).
                            @can('update:exercice')
                                <a href="{{ route('finance.exercices.planification', $exercice) }}">Voir tout →</a>
                            @endcan
                        </small>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- D&eacute;tails --}}
<div class="card data-card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-info-circle me-2 text-muted"></i>Informations d&eacute;taill&eacute;es</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Exercice</span>
                    <strong>{{ $exercice->exercice ?? '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <span class="text-muted small d-block">Date de d&eacute;but</span>
                    <strong>{{ $exercice->datedebut ? \Carbon\Carbon::parse($exercice->datedebut)->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <span class="text-muted small d-block">Date de fin</span>
                    <strong>{{ $exercice->datefin ? \Carbon\Carbon::parse($exercice->datefin)->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Report budg&eacute;taire</span>
                    <strong>{{ number_format($exercice->reportbudgetare ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <span class="text-muted small d-block">Report tr&eacute;sorerie global</span>
                    <strong>{{ number_format($exercice->reporttresorerieglobal ?? 0, 0, ',', ' ') }} F</strong>
                </div>
            </div>
            @if($exercice->commentaire)
            <div class="col-12">
                <div class="mb-3">
                    <span class="text-muted small d-block">Commentaire</span>
                    <p class="mb-0">{{ $exercice->commentaire }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Lignes budg&eacute;taires --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-list me-2 text-muted"></i>Lignes budg&eacute;taires</h5>
        <a href="{{ route('finance.budgets.create') }}?exercice_id={{ $exercice->id }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>Ajouter une ligne
        </a>
    </div>
    <div class="card-body p-0">
        @if(isset($exercice->budgetLignes) && $exercice->budgetLignes->count())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ligne</th>
                            <th>Montant initial</th>
                            <th>Montant r&eacute;vis&eacute;</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exercice->budgetLignes as $ligne)
                        <tr>
                            <td>{{ $ligne->ligne->libelle ?? $ligne->ligne_id }}</td>
                            <td>{{ number_format($ligne->montant_initial ?? 0, 0, ',', ' ') }} F</td>
                            <td>{{ number_format($ligne->montant_revise ?? 0, 0, ',', ' ') }} F</td>
                            <td>
                                @if($ligne->statut == 1)
                                    <span class="badge badge-status badge-actif">Actif</span>
                                @elseif($ligne->statut == 2)
                                    <span class="badge badge-status badge-inactif">Cl&ocirc;tur&eacute;</span>
                                @else
                                    <span class="badge badge-status badge-brouillon">Brouillon</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('finance.budgets.show', $ligne) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('finance.budgets.edit', $ligne) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune ligne budg&eacute;taire pour cet exercice</p>
            </div>
        @endif
    </div>
</div>
@endsection
