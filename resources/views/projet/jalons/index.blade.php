@extends('layouts.app')
@section('title', $projet->nom . ' — Jalons')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Jalons</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'jalons'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-flag-checkered"></i></span>
                Jalons — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Milestones du projet · Points de contrôle clés</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalJalon">
            <i class="fas fa-plus me-2"></i> Nouveau jalon
        </button>
    </div>

    @if($projet->jalons->count())
    <div class="jalons-timeline">
        @foreach($projet->jalons->sortBy('date_prevue') as $j)
        <div class="jalon-card statut-{{ $j->statut }}">
            <div class="jalon-date-col">
                <div class="jalon-date-badge {{ $j->estEnRetard() ? 'en-retard' : '' }}">
                    <div class="jalon-day">{{ $j->date_prevue->format('d') }}</div>
                    <div class="jalon-month">{{ $j->date_prevue->translatedFormat('M Y') }}</div>
                </div>
                <div class="jalon-line"></div>
            </div>
            <div class="jalon-body">
                <div class="jalon-header">
                    <h4 class="jalon-title">
                        @if($j->statut === 'atteint')<i class="fas fa-check-circle text-success me-1"></i>
                        @elseif($j->estEnRetard())<i class="fas fa-triangle-exclamation text-danger me-1"></i>
                        @else<i class="fas fa-flag" style="color:#0D9488;" class="me-1"></i>@endif
                        <a href="{{ route('projet.jalons.show', [$projet, $j]) }}" style="text-decoration:none;color:inherit;">{{ $j->titre }}</a>
                    </h4>
                    <div class="d-flex align-items-center gap-1">
                        <span class="opp-stage-badge" style="background:{{ match($j->statut) {
                            'atteint' => '#16A34A', 'manque' => '#DC2626', 'reporte' => '#F59E0B', default => '#0D9488'
                        } }};">{{ ucfirst($j->statut) }}</span>
                        {{-- Badge clôture --}}
                        @if($j->statut_cloture !== 'ouvert')
                        <span class="opp-stage-badge" style="background:{{ $j->cloture_couleur }};font-size:.6rem;">
                            {{ $j->cloture_libelle }}
                        </span>
                        @endif
                    </div>
                </div>
                @if($j->description)<p class="jalon-desc">{{ $j->description }}</p>@endif

                {{-- Message rejet/révisions --}}
                @if($j->statut_cloture === 'rejete' && $j->motif_rejet)
                <div class="alert alert-danger py-1 px-2 mb-2" style="font-size:.72rem;border-radius:6px;">
                    <i class="fas fa-circle-xmark me-1"></i> <strong>Rejet :</strong> {{ $j->motif_rejet }}
                </div>
                @elseif($j->statut_cloture === 'revisions' && $j->motif_rejet)
                <div class="alert alert-warning py-1 px-2 mb-2" style="font-size:.72rem;border-radius:6px;">
                    <i class="fas fa-arrows-rotate me-1"></i> <strong>Révisions :</strong> {{ $j->motif_rejet }}
                </div>
                @endif

                <div class="jalon-meta">
                    @if($j->phase)<span><i class="fas fa-sitemap"></i> {{ $j->phase->nom }}</span>@endif
                    @if($j->date_reelle)<span><i class="fas fa-check"></i> Atteint le {{ $j->date_reelle->format('d/m/Y') }}</span>@endif
                    <span><i class="fas fa-user"></i> {{ $j->auteur?->prenoms }}</span>
                    @if($j->valideurs->count())
                    <span><i class="fas fa-user-check" style="color:#6366F1;"></i> {{ $j->valideurs->count() }} valideur(s)</span>
                    @endif
                </div>
                <div class="jalon-actions d-flex align-items-center gap-1 flex-wrap">
                    {{-- Bouton marquer atteint --}}
                    @if($j->statut === 'prevu')
                        @if(!$j->aDesValideurs() || $j->statut_cloture === 'approuve')
                        <form action="{{ route('projet.jalons.atteint', [$projet, $j]) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn-action process btn-sm"><i class="fas fa-check"></i> Marquer atteint</button>
                        </form>
                        @endif
                    @endif

                    {{-- Boutons workflow clôture --}}
                    @if($j->aDesValideurs())
                        @if(in_array($j->statut_cloture, ['ouvert', 'rejete', 'revisions']))
                        <button class="btn-action process btn-sm" title="Soumettre pour clôture"
                                onclick="ouvrirModalCloture({{ $j->id }}, 'jalon', '{{ addslashes($j->titre) }}', '{{ $j->statut_cloture }}')">
                            <i class="fas fa-paper-plane"></i> Soumettre
                        </button>
                        @endif
                        @if($j->statut_cloture === 'soumis' && $j->estValideur())
                        <button class="btn-action success btn-sm"
                                onclick="ouvrirModalDecision({{ $j->id }}, 'jalon', '{{ addslashes($j->titre) }}', 'approuver')">
                            <i class="fas fa-check"></i> Approuver
                        </button>
                        <button class="btn-action danger btn-sm"
                                onclick="ouvrirModalDecision({{ $j->id }}, 'jalon', '{{ addslashes($j->titre) }}', 'rejeter')">
                            <i class="fas fa-times"></i> Rejeter
                        </button>
                        @endif
                    @endif

                    <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                        @if($j->peutModifier())
                        <li><button class="dropdown-item" onclick="editJalon({{ $j->id }}, '{{ addslashes($j->titre) }}', '{{ addslashes($j->description ?? '') }}', '{{ $j->phase_id }}', '{{ $j->date_prevue->format('Y-m-d') }}', '{{ $j->date_reelle?->format('Y-m-d') }}', '{{ $j->statut }}', {{ json_encode($j->validateursUsers->pluck('valideur_id')) }}, {{ json_encode($j->validateursGroupes->pluck('valideur_id')) }})">
                            <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                        @else
                        <li><button class="dropdown-item text-warning" onclick="demanderModification({{ $j->id }}, 'jalon', '{{ addslashes($j->titre) }}')">
                            <i class="fas fa-lock"></i> Demander modification</button></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('projet.jalons.show', [$projet, $j]) }}"><i class="fas fa-eye"></i> Voir détail</a></li>
                        @if($j->aDesValideurs())
                        <li><button class="dropdown-item" onclick="voirHistorique({{ $j->id }}, 'jalon')">
                            <i class="fas fa-clock-rotate-left"></i> Historique validation</button></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        @if($j->peutSupprimer())
                        <li><form action="{{ route('projet.jalons.destroy', [$projet, $j]) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                            @csrf @method('DELETE')
                            <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                        </form></li>
                        @else
                        <li><button class="dropdown-item text-danger" onclick="demanderSuppression({{ $j->id }}, 'jalon', '{{ addslashes($j->titre) }}')">
                            <i class="fas fa-trash"></i> Demander suppression</button></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-flag-checkered" style="color:#0D9488;"></i></div>
        <h3>Aucun jalon</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalJalon">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale jalon --}}
<div class="modal fade" id="modalJalon" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="jalonForm" method="POST" class="modal-content" action="{{ route('projet.jalons.store', $projet) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="jalonMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="jalonModalTitle">Nouveau jalon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="jalonTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="jalonDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Phase</label>
                        <select name="phase_id" id="jalonPhase" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($projet->phases as $ph)
                                <option value="{{ $ph->id }}">{{ $ph->nom }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Date prévue <span class="text-danger">*</span></label>
                        <input type="date" name="date_prevue" id="jalonDatePrevue" class="form-control" required></div>
                </div>
                <div class="row g-3 mt-2" id="jalonEditFields" style="display:none;">
                    <div class="col-md-6"><label class="form-label">Date réelle</label>
                        <input type="date" name="date_reelle" id="jalonDateReelle" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Statut</label>
                        <select name="statut" id="jalonStatut" class="form-select">
                            <option value="prevu">Prévu</option>
                            <option value="atteint">Atteint</option>
                            <option value="manque">Manqué</option>
                            <option value="reporte">Reporté</option>
                        </select></div>
                </div>

                {{-- Valideurs de clôture --}}
                <fieldset class="mt-3" style="border:1px solid #D8B4FE;border-radius:9px;padding:.85rem;">
                    <legend style="font-size:.72rem;font-weight:700;color:#6366F1;text-transform:uppercase;letter-spacing:.04em;padding:0 .5rem;width:auto;">
                        <i class="fas fa-user-check me-1"></i> Valideurs de clôture
                    </legend>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Valideurs (utilisateurs)</label>
                            <select name="valideurs_users[]" id="jalonValUsers" multiple placeholder="Sélectionner...">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1)) }}{{ strtoupper(substr($u->name, 0, 1)) }}">{{ $u->prenoms }} {{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valideurs (groupes)</label>
                            <select name="valideurs_groupes[]" id="jalonValGroupes" multiple placeholder="Sélectionner...">
                                @foreach($groupes as $g)
                                    <option value="{{ $g->id }}" data-color="{{ $g->couleur ?? '#7C3AED' }}">{{ $g->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <small class="form-hint mt-1" style="color:#6366F1;">
                        <i class="fas fa-info-circle"></i> Si des valideurs sont définis, marquer "Atteint" nécessitera leur approbation.
                    </small>
                </fieldset>

                {{-- Pièces jointes --}}
                <div class="mt-3">
                    <label class="form-label"><i class="fas fa-paperclip me-1"></i> Pièces jointes</label>
                    <input type="file" name="pieces_jointes[]" class="form-control" multiple
                           accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <small class="form-hint">Images, vidéos, documents — max 50 Mo par fichier</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-flag me-2"></i> <span id="jalonSubmitText">Créer</span></button>
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
                <div id="clotureRejetMsg" style="display:none;" class="alert alert-warning py-2 mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">Justification de clôture <span class="text-danger">*</span></label>
                    <textarea name="justification" id="clotureJustification" rows="4" class="form-control" required
                              placeholder="Décrivez pourquoi ce jalon peut être clôturé : critères remplis, livrables réalisés..."></textarea>
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

{{-- Modale décision valideur --}}
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
                    <textarea name="commentaire" id="decisionCommentaire" rows="3" class="form-control"></textarea>
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

{{-- Modale historique --}}
<div class="modal fade" id="modalHistorique" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2"></i> Historique de validation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="historiqueBody" style="max-height:400px;overflow-y:auto;"></div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// ── Tom Select pour les valideurs ──────────────────────
let tsValUsers, tsValGroupes;
const _palette = ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5','#DC2626','#16A34A'];
const _colorOf = s => { let h = 0; for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) & 0xffffffff; return _palette[Math.abs(h) % _palette.length]; };

function initTomSelectJalon() {
    if (tsValUsers) return; // déjà initialisé

    const usersEl = document.getElementById('jalonValUsers');
    if (usersEl && !usersEl.tomselect) {
        tsValUsers = new TomSelect(usersEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            dropdownParent: 'body',
            render: {
                option: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-opt"><span class="ts-opt-avatar" style="background:${_colorOf(data.text)};">${escape(initials)}</span><div class="ts-opt-body"><div class="ts-opt-name">${escape(data.text)}</div></div></div>`;
                },
                item: (data, escape) => {
                    const initials = data.initials || (data.text || '?').substring(0, 2).toUpperCase();
                    return `<div class="ts-item-user"><span class="ts-item-avatar" style="background:${_colorOf(data.text)};">${escape(initials)}</span>${escape(data.text)}</div>`;
                },
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(usersEl.options).forEach(opt => {
                    if (this.options[opt.value]) this.options[opt.value].initials = opt.dataset.initials || '';
                });
            },
        });
    }

    const groupesEl = document.getElementById('jalonValGroupes');
    if (groupesEl && !groupesEl.tomselect) {
        tsValGroupes = new TomSelect(groupesEl, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 500,
            dropdownParent: 'body',
            render: {
                option: (d, e) => `<div class="ts-opt"><span class="ts-opt-color" style="background:${e(d.color || '#7C3AED')};"></span><div class="ts-opt-body"><div class="ts-opt-name">${e(d.text)}</div></div></div>`,
                item: (d, e) => `<div class="ts-item-group"><span class="ts-item-dot" style="background:${e(d.color || '#7C3AED')};"></span>${e(d.text)}</div>`,
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
            onInitialize: function () {
                Array.from(groupesEl.options).forEach(opt => {
                    if (this.options[opt.value]) this.options[opt.value].color = opt.dataset.color || '#7C3AED';
                });
            },
        });
    }
}

// Initialiser au premier affichage de la modale (garantit que le DOM est visible)
document.getElementById('modalJalon')?.addEventListener('shown.bs.modal', initTomSelectJalon);

function editJalon(id, titre, desc, phase, datePrevue, dateReelle, statut, valUsers, valGroupes) {
    document.getElementById('jalonModalTitle').textContent = 'Modifier le jalon';
    document.getElementById('jalonTitre').value = titre;
    document.getElementById('jalonDesc').value = desc;
    document.getElementById('jalonPhase').value = phase || '';
    document.getElementById('jalonDatePrevue').value = datePrevue;
    document.getElementById('jalonDateReelle').value = dateReelle || '';
    document.getElementById('jalonStatut').value = statut;
    document.getElementById('jalonEditFields').style.display = 'flex';
    document.getElementById('jalonMethod').value = 'PUT';
    document.getElementById('jalonSubmitText').textContent = 'Enregistrer';
    document.getElementById('jalonForm').action = '/projet/{{ $projet->id }}/jalons/' + id;

    // Pré-sélectionner les valideurs via Tom Select
    if (tsValUsers) { tsValUsers.clear(true); (valUsers || []).forEach(v => tsValUsers.addItem(String(v), true)); }
    if (tsValGroupes) { tsValGroupes.clear(true); (valGroupes || []).forEach(v => tsValGroupes.addItem(String(v), true)); }

    new bootstrap.Modal(document.getElementById('modalJalon')).show();
}

document.getElementById('modalJalon')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('jalonModalTitle').textContent = 'Nouveau jalon';
    ['jalonTitre','jalonDesc','jalonDatePrevue','jalonDateReelle'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('jalonPhase').value = '';
    document.getElementById('jalonStatut').value = 'prevu';
    document.getElementById('jalonEditFields').style.display = 'none';
    document.getElementById('jalonMethod').value = 'POST';
    document.getElementById('jalonSubmitText').textContent = 'Créer';
    document.getElementById('jalonForm').action = '{{ route("projet.jalons.store", $projet) }}';
    if (tsValUsers) tsValUsers.clear(true);
    if (tsValGroupes) tsValGroupes.clear(true);
});

// ── Workflow clôture (partagé) ──────────────────────────

function ouvrirModalCloture(entityId, type, nom, statutCloture) {
    document.getElementById('clotureNom').textContent = nom;
    document.getElementById('clotureJustification').value = '';
    const rejetMsg = document.getElementById('clotureRejetMsg');
    if (statutCloture === 'rejete' || statutCloture === 'revisions') {
        document.getElementById('clotureTitle').textContent = 'Resoumettre pour clôture';
        rejetMsg.style.display = 'block';
        rejetMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Soumission précédente ' + (statutCloture === 'rejete' ? 'rejetée' : 'renvoyée pour révisions') + '.';
    } else {
        document.getElementById('clotureTitle').textContent = 'Soumettre pour clôture';
        rejetMsg.style.display = 'none';
    }
    document.getElementById('clotureForm').action = '/projet/cloture/' + type + '/' + entityId + '/soumettre';
    new bootstrap.Modal(document.getElementById('modalCloture')).show();
}

function ouvrirModalDecision(entityId, type, nom, action) {
    const isApprouver = action === 'approuver';
    document.getElementById('decisionHeader').style.background = isApprouver ? 'linear-gradient(135deg,#16A34A,#15803D)' : 'linear-gradient(135deg,#DC2626,#B91C1C)';
    document.getElementById('decisionTitle').textContent = isApprouver ? 'Approuver la clôture' : 'Rejeter la clôture';
    document.getElementById('decisionDesc').textContent = (isApprouver ? 'Approuver : ' : 'Rejeter : ') + nom;
    document.getElementById('decisionLabel').textContent = isApprouver ? 'Commentaire (optionnel)' : 'Motif de rejet *';
    document.getElementById('decisionCommentaire').required = !isApprouver;
    document.getElementById('decisionCommentaire').value = '';
    document.getElementById('decisionBtn').innerHTML = isApprouver ? '<i class="fas fa-check me-1"></i> Approuver' : '<i class="fas fa-times me-1"></i> Rejeter';
    document.getElementById('decisionBtn').style.background = isApprouver ? '#16A34A' : '#DC2626';
    document.getElementById('decisionForm').action = '/projet/cloture/' + type + '/' + entityId + '/' + action;
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
            if (!data.length) { body.innerHTML = '<p class="text-center text-muted py-3">Aucun historique.</p>'; return; }
            let html = '';
            data.forEach(h => {
                html += '<div class="d-flex gap-2 mb-3 align-items-start">';
                html += '<div style="width:28px;height:28px;border-radius:50%;background:' + h.couleur + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
                html += '<i class="fas ' + h.icone + ' text-white" style="font-size:.65rem;"></i></div>';
                html += '<div><div style="font-size:.78rem;font-weight:600;">' + h.libelle + '</div>';
                html += '<div style="font-size:.7rem;color:#64748B;">' + (h.user || '') + ' · ' + h.date + '</div>';
                if (h.justification) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i>' + h.justification + '</div>';
                if (h.commentaire) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i>' + h.commentaire + '</div>';
                html += '</div></div>';
            });
            body.innerHTML = html;
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-danger py-3">Erreur.</p>'; });
}
</script>
@endpush

@include('projet._partials.modales-protection')

@endsection
