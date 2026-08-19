@extends('layouts.app')
@section('title', $projet->nom . ' — WBS')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">WBS</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'wbs'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-sitemap"></i></span>
                WBS — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Work Breakdown Structure · Décomposition du travail en phases</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalPhase">
            <i class="fas fa-plus me-2"></i> Nouvelle phase
        </button>
    </div>

    {{-- Arbre WBS --}}
    @if($projet->phases->count())
    <div id="wbsContainer" class="wbs-tree">
        @foreach($projet->phases->sortBy('ordre') as $phase)
        <div class="wbs-phase" data-id="{{ $phase->id }}" style="--phase-color: {{ $phase->couleur ?? '#0D9488' }};">

            <div class="wbs-phase-header">
                <div class="wbs-phase-handle" title="Glisser pour réordonner"><i class="fas fa-grip-vertical"></i></div>
                <div class="wbs-phase-code">{{ $phase->code_wbs ?? 'WBS-' . $phase->ordre }}</div>
                @if($phase->statut)
                <span class="badge" style="background:{{ $phase->statut_couleur }};font-size:.65rem;padding:.2em .6em;">{{ $phase->statut->libelle }}</span>
                @endif
                {{-- Badge clôture --}}
                @if($phase->statut_cloture !== 'ouvert')
                <span class="badge" style="background:{{ $phase->cloture_couleur }};font-size:.6rem;padding:.2em .5em;">
                    <i class="fas {{ $phase->statut_cloture === 'approuve' ? 'fa-check-circle' : ($phase->statut_cloture === 'soumis' ? 'fa-clock' : ($phase->statut_cloture === 'rejete' ? 'fa-circle-xmark' : 'fa-arrows-rotate')) }} me-1"></i>{{ $phase->cloture_libelle }}
                </span>
                @endif
                <a href="{{ route('projet.wbs.show', [$projet, $phase]) }}" class="wbs-phase-title" style="text-decoration:none;color:inherit;">{{ $phase->nom }}</a>
                <div class="wbs-phase-progress">
                    <div class="tache-progress-bar">
                        <div class="tache-progress-fill" style="width:{{ $phase->avancement_real }}%;background:var(--phase-color);"></div>
                    </div>
                    <span class="tache-progress-pct">{{ $phase->avancement_real }}%</span>
                </div>
                <div class="wbs-phase-meta">
                    @if($phase->ponderation)<span title="Pondération"><i class="fas fa-weight-hanging"></i> {{ $phase->ponderation }}%</span>@endif
                    @if($phase->responsable)<span><i class="fas fa-user"></i> {{ $phase->responsable->prenoms }}</span>@endif
                    <span><i class="fas fa-list-check"></i> {{ $phase->taches->count() }} tâches</span>
                    @if($phase->date_debut && $phase->date_fin)
                    <span><i class="far fa-calendar"></i> {{ $phase->date_debut->format('d/m') }} → {{ $phase->date_fin->format('d/m') }}</span>
                    @endif
                    @if($phase->date_debut_reelle)
                    <span style="color:#16A34A;"><i class="fas fa-play"></i> {{ $phase->date_debut_reelle->format('d/m') }}</span>
                    @endif
                    @if($phase->date_fin_reelle)
                    <span style="color:#16A34A;"><i class="fas fa-stop"></i> {{ $phase->date_fin_reelle->format('d/m') }}</span>
                    @endif
                    @if($phase->valideurs->count())
                    <span title="Valideurs définis"><i class="fas fa-user-check" style="color:#6366F1;"></i> {{ $phase->valideurs->count() }}</span>
                    @endif
                </div>
                <div class="wbs-phase-actions d-flex align-items-center gap-1">
                    {{-- Boutons de validation selon l'état --}}
                    @if($phase->aDesValideurs())
                        @if($phase->statut_cloture === 'ouvert' || $phase->statut_cloture === 'rejete' || $phase->statut_cloture === 'revisions')
                        <button class="btn-action process btn-sm" title="Soumettre pour clôture"
                                onclick="ouvrirModalCloture({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', '{{ $phase->statut_cloture }}')">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        @endif
                        @if($phase->statut_cloture === 'soumis' && $phase->estValideur())
                        <button class="btn-action success btn-sm" title="Approuver la clôture"
                                onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'approuver')">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn-action danger btn-sm" title="Rejeter la clôture"
                                onclick="ouvrirModalDecision({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}', 'rejeter')">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif
                    @endif

                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        @if($phase->peutModifier())
                        <li><button class="dropdown-item" onclick="editPhase({{ $phase->id }}, '{{ addslashes($phase->nom) }}', '{{ $phase->code_wbs }}', '{{ $phase->date_debut?->format('Y-m-d') }}', '{{ $phase->date_fin?->format('Y-m-d') }}', '{{ $phase->date_debut_reelle?->format('Y-m-d') }}', '{{ $phase->date_fin_reelle?->format('Y-m-d') }}', '{{ $phase->responsable_id }}', '{{ $phase->couleur }}', {{ $phase->avancement ?? 0 }}, {{ $phase->ponderation ?? 0 }}, '{{ addslashes($phase->description ?? '') }}', '{{ $phase->statut_id }}', {{ json_encode($phase->validateursUsers->pluck('valideur_id')) }}, {{ json_encode($phase->validateursGroupes->pluck('valideur_id')) }})">
                            <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                        @else
                        <li><button class="dropdown-item text-warning" onclick="demanderModification({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}')">
                            <i class="fas fa-lock"></i> Demander modification</button></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('projet.wbs.show', [$projet, $phase]) }}"><i class="fas fa-eye"></i> Voir détail</a></li>
                        @if($phase->aDesValideurs())
                        <li><button class="dropdown-item" onclick="voirHistorique({{ $phase->id }}, 'phase')">
                            <i class="fas fa-clock-rotate-left"></i> Historique validation</button></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        @if($phase->peutSupprimer())
                        <li><form action="{{ route('projet.wbs.destroy', [$projet, $phase]) }}" method="POST" onsubmit="return confirm('Supprimer cette phase et ses tâches ?');">
                            @csrf @method('DELETE')
                            <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                        </form></li>
                        @else
                        <li><button class="dropdown-item text-danger" onclick="demanderSuppression({{ $phase->id }}, 'phase', '{{ addslashes($phase->nom) }}')">
                            <i class="fas fa-trash"></i> Demander suppression</button></li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Message rejet/révisions --}}
            @if($phase->statut_cloture === 'rejete' && $phase->motif_rejet)
            <div class="alert alert-danger py-1 px-3 mb-0 mx-3 mt-1" style="font-size:.75rem;border-radius:6px;">
                <i class="fas fa-circle-xmark me-1"></i> <strong>Rejet :</strong> {{ $phase->motif_rejet }}
            </div>
            @elseif($phase->statut_cloture === 'revisions' && $phase->motif_rejet)
            <div class="alert alert-warning py-1 px-3 mb-0 mx-3 mt-1" style="font-size:.75rem;border-radius:6px;">
                <i class="fas fa-arrows-rotate me-1"></i> <strong>Révisions demandées :</strong> {{ $phase->motif_rejet }}
            </div>
            @endif

            {{-- Tâches de la phase --}}
            @if($phase->taches->count())
            <div class="wbs-taches">
                @foreach($phase->taches->sortBy('ordre') as $t)
                <a href="{{ route('intranet.taches.show', $t) }}" class="wbs-tache {{ $t->estEnRetard() ? 'en-retard' : '' }}">
                    <span class="wbs-tache-status" style="background:{{ $t->statut_couleur }};"></span>
                    <span class="wbs-tache-title">{{ $t->titre }}</span>
                    <span class="wbs-tache-progress">{{ $t->avancement }}%</span>
                    @if($t->responsable)<span class="wbs-tache-user">{{ $t->responsable->prenoms }}</span>@endif
                </a>
                @endforeach
            </div>
            @endif

        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-sitemap" style="color:#0D9488;"></i></div>
        <h3>Aucune phase définie</h3>
        <p>Créez la première phase pour décomposer votre projet.</p>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalPhase">
            <i class="fas fa-plus me-2"></i> Créer une phase
        </button>
    </div>
    @endif
</div>

{{-- Indicateur pondération --}}
@if($projet->phases->count())
<div class="contact-detail-card mb-3">
    <div class="d-flex align-items-center gap-3">
        <strong style="font-size:.78rem;color:#475569;">Pondération allouée :</strong>
        <div class="tache-progress-bar" style="flex:1;height:8px;">
            <div class="tache-progress-fill" style="width:{{ min($ponderationAllouee, 100) }}%;background:{{ $ponderationAllouee > 100 ? '#DC2626' : ($ponderationAllouee >= 100 ? '#16A34A' : '#0D9488') }};"></div>
        </div>
        <span style="font-size:.82rem;font-weight:700;color:{{ $ponderationAllouee > 100 ? '#DC2626' : '#0F172A' }};">{{ $ponderationAllouee }}%</span>
        <span style="font-size:.68rem;color:#94A3B8;">/ 100%</span>
        @if($ponderationAllouee < 100)
        <span style="font-size:.68rem;color:#0D9488;font-weight:600;">Disponible : {{ round(100 - $ponderationAllouee, 2) }}%</span>
        @endif
    </div>
</div>
@endif

{{-- Modale phase (création/édition) --}}
<div class="modal fade" id="modalPhase" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="phaseForm" method="POST" class="modal-content" action="{{ route('projet.wbs.store', $projet) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="phaseMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="phaseModalTitle">Nouvelle phase WBS</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="phaseNom" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <div id="phaseDescEditor"></div>
                    <input type="hidden" name="description" id="phaseDesc"></div>

                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Code WBS</label>
                        <input type="text" name="code_wbs" id="phaseCode" class="form-control" placeholder="1.0"></div>
                    <div class="col-md-4"><label class="form-label">Statut</label>
                        <select name="statut_id" id="phaseStatut" class="form-select">
                            <option value="">—</option>
                            @foreach($statuts as $s)
                                <option value="{{ $s->id }}" data-couleur="{{ $s->couleur }}">{{ $s->libelle }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Pondération (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="ponderation" id="phasePonderation" class="form-control" placeholder="ex: 25">
                        <small class="form-hint" id="phasePondDisp">Disponible : {{ round(100 - $ponderationAllouee, 2) }}%</small></div>
                    <div class="col-md-2"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="phaseCouleur" class="form-control form-control-color" value="#0D9488"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="phaseResp">
                            <option value="">— Aucun —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select></div>
                </div>

                <fieldset class="mt-3" style="border:1px solid #E2E8F0;border-radius:9px;padding:.85rem;">
                    <legend style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;padding:0 .5rem;width:auto;">Dates planifiées</legend>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Début planifié</label>
                            <input type="date" name="date_debut" id="phaseDateDebut" class="form-control"
                                   min="{{ $projet->date_debut?->format('Y-m-d') }}" max="{{ $projet->date_fin?->format('Y-m-d') }}"></div>
                        <div class="col-md-6"><label class="form-label">Fin planifiée</label>
                            <input type="date" name="date_fin" id="phaseDateFin" class="form-control"
                                   min="{{ $projet->date_debut?->format('Y-m-d') }}" max="{{ $projet->date_fin?->format('Y-m-d') }}"></div>
                    </div>
                    @if($projet->date_debut && $projet->date_fin)
                    <small class="form-hint mt-1">
                        <i class="fas fa-info-circle"></i> Le projet s'étend du {{ $projet->date_debut->format('d/m/Y') }} au {{ $projet->date_fin->format('d/m/Y') }}
                    </small>
                    @endif
                </fieldset>

                <fieldset class="mt-3" style="border:1px solid #E2E8F0;border-radius:9px;padding:.85rem;" id="phaseDatesReellesWrap">
                    <legend style="font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;padding:0 .5rem;width:auto;">Dates effectives</legend>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Début effectif</label>
                            <input type="date" name="date_debut_reelle" id="phaseDateDebutReelle" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Fin effective</label>
                            <input type="date" name="date_fin_reelle" id="phaseDateFinReelle" class="form-control"></div>
                    </div>
                </fieldset>

                {{-- Valideurs de clôture --}}
                <fieldset class="mt-3" style="border:1px solid #D8B4FE;border-radius:9px;padding:.85rem;">
                    <legend style="font-size:.72rem;font-weight:700;color:#6366F1;text-transform:uppercase;letter-spacing:.04em;padding:0 .5rem;width:auto;">
                        <i class="fas fa-user-check me-1"></i> Valideurs de clôture
                    </legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Valideurs (utilisateurs)</label>
                            <select name="valideurs_users[]" id="phaseValUsers" multiple placeholder="Sélectionner...">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1)) }}{{ strtoupper(substr($u->name, 0, 1)) }}">{{ $u->prenoms }} {{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valideurs (groupes)</label>
                            <select name="valideurs_groupes[]" id="phaseValGroupes" multiple placeholder="Sélectionner...">
                                @foreach($groupes as $g)
                                    <option value="{{ $g->id }}" data-color="{{ $g->couleur ?? '#7C3AED' }}">{{ $g->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="form-hint mt-1" style="color:#6366F1;">
                        <i class="fas fa-info-circle"></i> Si des valideurs sont définis, le passage au statut "Terminé" nécessitera leur approbation.
                    </small>
                </fieldset>

                {{-- Pièces jointes --}}
                <div class="mt-3">
                    <label class="form-label"><i class="fas fa-paperclip me-1"></i> Pièces jointes</label>
                    <input type="file" name="pieces_jointes[]" class="form-control" multiple
                           accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <small class="form-hint">Images, vidéos, documents — max 50 Mo par fichier</small>
                </div>

                <div class="mt-3" id="phaseAvancementWrap" style="display:none;">
                    <label class="form-label">Avancement (%)</label>
                    <input type="number" name="avancement" id="phaseAvancement" class="form-control" min="0" max="100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-plus me-2"></i> <span id="phaseSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale soumission clôture --}}
<div class="modal fade" id="modalCloture" tabindex="-1">
    <div class="modal-dialog">
        <form id="clotureForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#6366F1,#4F46E5);">
                <h5 class="modal-title text-white"><i class="fas fa-paper-plane me-2"></i> <span id="clotureTitle">Soumettre pour clôture</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;">Vous soumettez la clôture de : <strong id="clotureNom"></strong></p>
                <div id="clotureRejetMsg" style="display:none;" class="alert alert-warning py-2 mb-3" style="font-size:.78rem;"></div>
                <div class="mb-3">
                    <label class="form-label">Justification de clôture <span class="text-danger">*</span></label>
                    <textarea name="justification" id="clotureJustification" rows="4" class="form-control" required
                              placeholder="Décrivez pourquoi cette phase/jalon/projet peut être clôturé(e) : livrables réalisés, critères remplis..."></textarea>
                    <small class="form-hint">Minimum 10 caractères</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#6366F1;"><i class="fas fa-paper-plane me-2"></i> Soumettre</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale décision valideur (approuver/rejeter) --}}
<div class="modal fade" id="modalDecision" tabindex="-1">
    <div class="modal-dialog">
        <form id="decisionForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" id="decisionHeader">
                <h5 class="modal-title text-white" id="decisionTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;" id="decisionDesc"></p>
                <div class="mb-3">
                    <label class="form-label" id="decisionLabel">Commentaire</label>
                    <textarea name="commentaire" id="decisionCommentaire" rows="3" class="form-control"
                              placeholder="Commentaire ou motif..."></textarea>
                </div>
                <input type="hidden" name="motif" id="decisionMotif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" id="decisionBtn"></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale historique validation --}}
<div class="modal fade" id="modalHistorique" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2"></i> Historique de validation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="historiqueBody" style="max-height:400px;overflow-y:auto;">
                <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// Quill pour la description
let phaseQuill, tsPhaseValUsers, tsPhaseValGroupes, tsPhaseResp;
document.addEventListener('DOMContentLoaded', function () {
    phaseQuill = new Quill('#phaseDescEditor', {
        theme: 'snow',
        placeholder: 'Description de la phase…',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'blockquote'],
                ['clean'],
            ],
        },
    });
    phaseQuill.on('text-change', function () {
        document.getElementById('phaseDesc').value = phaseQuill.root.innerHTML;
    });
    document.getElementById('phaseForm')?.addEventListener('submit', function () {
        document.getElementById('phaseDesc').value = phaseQuill.root.innerHTML;
    });

});

// ── Tom Select pour les valideurs (init au 1er affichage de la modale) ──
const _wbsPalette = ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5','#DC2626','#16A34A'];
const _wbsColorOf = s => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff; return _wbsPalette[Math.abs(h) % _wbsPalette.length]; };

function initTomSelectPhase() {
    if (tsPhaseValUsers) return;

    const puEl = document.getElementById('phaseValUsers');
    if (puEl && !puEl.tomselect) {
        tsPhaseValUsers = new TomSelect(puEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            dropdownParent: 'body',
            render: {
                option: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-opt"><span class="ts-opt-avatar" style="background:${_wbsColorOf(data.text)};">${escape(initials)}</span><div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div></div></div>`;
                },
                item: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-item-user"><span class="ts-item-avatar" style="background:${_wbsColorOf(data.text)};">${escape(initials)}</span>${escape(data.text)}</div>`;
                },
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(puEl.options).forEach(opt => { if (this.options[opt.value]) this.options[opt.value].initials = opt.dataset.initials || ''; });
            },
        });
    }

    // Responsable de phase — Tom Select avec recherche (single select)
    const respEl = document.getElementById('phaseResp');
    if (respEl && !respEl.tomselect) {
        tsPhaseResp = new TomSelect(respEl, {
            maxOptions: 500,
            dropdownParent: 'body',
            allowEmptyOption: true,
            render: {
                option: (data, escape) => {
                    if (!data.value) return '<div class="ts-opt"><em>— Aucun —</em></div>';
                    const initials = (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-opt"><span class="ts-opt-avatar" style="background:${_wbsColorOf(data.text)};">${escape(initials)}</span><div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div></div></div>`;
                },
                item: (data, escape) => {
                    if (!data.value) return '<div class="ts-item-user"><em>— Aucun —</em></div>';
                    const initials = (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-item-user"><span class="ts-item-avatar" style="background:${_wbsColorOf(data.text)};">${escape(initials)}</span>${escape(data.text)}</div>`;
                },
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
        });
    }

    const pgEl = document.getElementById('phaseValGroupes');
    if (pgEl && !pgEl.tomselect) {
        tsPhaseValGroupes = new TomSelect(pgEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            dropdownParent: 'body',
            render: {
                option: (d, e) => `<div class="ts-opt"><span class="ts-opt-color" style="background:${e(d.color || '#7C3AED')};"></span><div class="ts-opt-body"><div class="ts-opt-name">${e(d.text)}</div></div></div>`,
                item: (d, e) => `<div class="ts-item-group"><span class="ts-item-dot" style="background:${e(d.color || '#7C3AED')};"></span>${e(d.text)}</div>`,
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(pgEl.options).forEach(opt => { if (this.options[opt.value]) this.options[opt.value].color = opt.dataset.color || '#7C3AED'; });
            },
        });
    }
}

document.getElementById('modalPhase')?.addEventListener('shown.bs.modal', initTomSelectPhase);

function editPhase(id, nom, code, debut, fin, debutR, finR, resp, couleur, av, pond, desc, statutId, valUsers, valGroupes) {
    document.getElementById('phaseModalTitle').textContent = 'Modifier la phase';
    document.getElementById('phaseNom').value = nom;
    document.getElementById('phaseCode').value = code || '';
    document.getElementById('phaseStatut').value = statutId || '';
    phaseQuill.root.innerHTML = desc || '';
    document.getElementById('phaseDateDebut').value = debut || '';
    document.getElementById('phaseDateFin').value = fin || '';
    document.getElementById('phaseDateDebutReelle').value = debutR || '';
    document.getElementById('phaseDateFinReelle').value = finR || '';
    if (tsPhaseResp) { tsPhaseResp.setValue(resp || ''); } else { document.getElementById('phaseResp').value = resp || ''; }
    document.getElementById('phaseCouleur').value = couleur || '#0D9488';
    document.getElementById('phaseAvancement').value = av || 0;
    document.getElementById('phasePonderation').value = pond || '';
    document.getElementById('phaseAvancementWrap').style.display = 'block';
    document.getElementById('phaseMethod').value = 'PUT';
    document.getElementById('phaseSubmitText').textContent = 'Enregistrer';
    document.getElementById('phaseForm').action = '/projet/{{ $projet->id }}/wbs/' + id;

    // Pré-sélectionner les valideurs via Tom Select
    if (tsPhaseValUsers) { tsPhaseValUsers.clear(true); (valUsers || []).forEach(v => tsPhaseValUsers.addItem(String(v), true)); }
    if (tsPhaseValGroupes) { tsPhaseValGroupes.clear(true); (valGroupes || []).forEach(v => tsPhaseValGroupes.addItem(String(v), true)); }

    new bootstrap.Modal(document.getElementById('modalPhase')).show();
}

document.getElementById('modalPhase')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('phaseModalTitle').textContent = 'Nouvelle phase WBS';
    ['phaseNom','phaseCode','phaseDateDebut','phaseDateFin','phaseDateDebutReelle','phaseDateFinReelle','phaseAvancement','phasePonderation'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('phaseStatut').value = '';
    document.getElementById('phaseDesc').value = '';
    if (phaseQuill) phaseQuill.root.innerHTML = '';
    if (tsPhaseResp) tsPhaseResp.setValue(''); else document.getElementById('phaseResp').value = '';
    document.getElementById('phaseCouleur').value = '#0D9488';
    document.getElementById('phaseAvancementWrap').style.display = 'none';
    document.getElementById('phaseMethod').value = 'POST';
    document.getElementById('phaseSubmitText').textContent = 'Créer';
    document.getElementById('phaseForm').action = '{{ route("projet.wbs.store", $projet) }}';
    // Reset valideurs Tom Select
    if (tsPhaseValUsers) tsPhaseValUsers.clear(true);
    if (tsPhaseValGroupes) tsPhaseValGroupes.clear(true);
});

// ── Workflow clôture ──────────────────────────────────

function ouvrirModalCloture(entityId, type, nom, statutCloture) {
    document.getElementById('clotureNom').textContent = nom;
    document.getElementById('clotureJustification').value = '';
    const rejetMsg = document.getElementById('clotureRejetMsg');
    if (statutCloture === 'rejete' || statutCloture === 'revisions') {
        document.getElementById('clotureTitle').textContent = 'Resoumettre pour clôture';
        rejetMsg.style.display = 'block';
        rejetMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> La soumission précédente a été ' + (statutCloture === 'rejete' ? 'rejetée' : 'renvoyée pour révisions') + '. Veuillez corriger et resoumettre.';
    } else {
        document.getElementById('clotureTitle').textContent = 'Soumettre pour clôture';
        rejetMsg.style.display = 'none';
    }
    document.getElementById('clotureForm').action = '/projet/cloture/' + type + '/' + entityId + '/soumettre';
    new bootstrap.Modal(document.getElementById('modalCloture')).show();
}

function ouvrirModalDecision(entityId, type, nom, action) {
    const isApprouver = action === 'approuver';
    const header = document.getElementById('decisionHeader');
    header.style.background = isApprouver ? 'linear-gradient(135deg,#16A34A,#15803D)' : 'linear-gradient(135deg,#DC2626,#B91C1C)';
    document.getElementById('decisionTitle').textContent = isApprouver ? 'Approuver la clôture' : 'Rejeter la clôture';
    document.getElementById('decisionDesc').textContent = (isApprouver ? 'Approuver la clôture de : ' : 'Rejeter la clôture de : ') + nom;
    document.getElementById('decisionLabel').textContent = isApprouver ? 'Commentaire (optionnel)' : 'Motif de rejet *';
    document.getElementById('decisionCommentaire').required = !isApprouver;
    document.getElementById('decisionCommentaire').value = '';
    document.getElementById('decisionCommentaire').placeholder = isApprouver ? 'Commentaire optionnel...' : 'Motif de rejet (min. 10 caractères)...';
    document.getElementById('decisionBtn').innerHTML = isApprouver ? '<i class="fas fa-check me-1"></i> Approuver' : '<i class="fas fa-times me-1"></i> Rejeter';
    document.getElementById('decisionBtn').style.background = isApprouver ? '#16A34A' : '#DC2626';
    document.getElementById('decisionForm').action = '/projet/cloture/' + type + '/' + entityId + '/' + action;

    // Pour rejeter, on utilise le champ "motif" au lieu de "commentaire"
    document.getElementById('decisionForm').onsubmit = function () {
        if (!isApprouver) {
            document.getElementById('decisionMotif').value = document.getElementById('decisionCommentaire').value;
            document.getElementById('decisionCommentaire').name = '';
            document.getElementById('decisionMotif').name = 'motif';
        } else {
            document.getElementById('decisionCommentaire').name = 'commentaire';
            document.getElementById('decisionMotif').name = '';
        }
    };

    new bootstrap.Modal(document.getElementById('modalDecision')).show();
}

function voirHistorique(entityId, type) {
    const body = document.getElementById('historiqueBody');
    body.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    new bootstrap.Modal(document.getElementById('modalHistorique')).show();

    fetch('/projet/cloture/' + type + '/' + entityId + '/historique')
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                body.innerHTML = '<p class="text-center text-muted py-3">Aucun historique.</p>';
                return;
            }
            let html = '<div class="timeline-mini">';
            data.forEach(h => {
                html += '<div class="d-flex gap-2 mb-3 align-items-start">';
                html += '<div style="width:28px;height:28px;border-radius:50%;background:' + h.couleur + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
                html += '<i class="fas ' + h.icone + ' text-white" style="font-size:.65rem;"></i></div>';
                html += '<div style="flex:1;">';
                html += '<div style="font-size:.78rem;font-weight:600;">' + h.libelle + '</div>';
                html += '<div style="font-size:.7rem;color:#64748B;">' + (h.user || '') + ' · ' + h.date + '</div>';
                if (h.justification) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i>' + h.justification + '</div>';
                if (h.commentaire) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i>' + h.commentaire + '</div>';
                html += '</div></div>';
            });
            html += '</div>';
            body.innerHTML = html;
        })
        .catch(() => {
            body.innerHTML = '<p class="text-center text-danger py-3">Erreur de chargement.</p>';
        });
}

// Drag & drop reorder
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('wbsContainer');
    if (container) {
        new Sortable(container, {
            handle: '.wbs-phase-handle',
            animation: 180,
            ghostClass: 'kanban-ghost',
            onEnd: function () {
                const order = Array.from(container.querySelectorAll('.wbs-phase')).map(el => el.dataset.id);
                fetch('{{ route("projet.wbs.reorder", $projet) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order }),
                });
            },
        });
    }
});
</script>
@endpush

@include('projet._partials.modales-protection')

@endsection
