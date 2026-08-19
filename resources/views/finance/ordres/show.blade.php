@extends('layouts.app')
@section('title', $ordre->numero_ordre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.ordres.index') }}">Dépenses & Recettes</a></li>
    <li class="breadcrumb-item active">{{ $ordre->numero_ordre }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 gap-3 flex-wrap">
    <div>
        <h1 class="h3 mb-1">{{ $ordre->modele->libelle }} <code class="ms-2 fs-6">{{ $ordre->numero_ordre }}</code></h1>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span class="badge bg-{{ $ordre->statut_couleur }}">{{ $ordre->statut_libelle }}</span>
            @if($ordre->sens === 'depense')
                <span class="badge bg-danger"><i class="fas fa-arrow-down me-1"></i>Dépense</span>
            @else
                <span class="badge bg-success"><i class="fas fa-arrow-up me-1"></i>Recette</span>
            @endif
            <small class="text-muted">Créé le {{ $ordre->created_at?->format('d/m/Y H:i') }} par {{ $ordre->createur?->name }}</small>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.ordres.pdf', $ordre) }}" class="btn btn-outline-dark" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
        @can('update:operation')
            @if($ordre->peutEtreModifie() || auth()->user()->hasRole('super-admin'))
                <a href="{{ route('finance.ordres.edit', $ordre) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-pen me-1"></i> Modifier
                </a>
            @endif
            @if($ordre->peutEtreSoumis())
                <form action="{{ route('finance.ordres.soumettre', $ordre) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Soumettre cet ordre pour signature ?');">@csrf
                    <button class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i> Soumettre</button>
                </form>
            @endif
        @endcan
        @can('validate:operation')
            @if($ordre->peutEtreSigne())
                <form action="{{ route('finance.ordres.signer', $ordre) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Signer cet ordre ?');">@csrf
                    <button class="btn btn-primary"><i class="fas fa-signature me-1"></i> Signer</button>
                </form>
            @endif
            @if($ordre->peutEtreExecute())
                <form action="{{ route('finance.ordres.executer', $ordre) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Exécuter cet ordre ? Une écriture définitive sera créée au Grand Livre.');">@csrf
                    <button class="btn btn-success"><i class="fas fa-check-double me-1"></i> Exécuter au Grand Livre</button>
                </form>
            @endif
            @if($ordre->peutEtreAnnule())
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-annuler-ordre">
                    <i class="fas fa-ban me-1"></i> Annuler
                </button>
            @endif
        @endcan
        @can('delete:operation')
            @if($ordre->statut === \App\Models\Finance\Ordre::STATUT_BROUILLON || auth()->user()->hasRole('super-admin'))
                <form action="{{ route('finance.ordres.destroy', $ordre) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer cet ordre ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> Supprimer</button>
                </form>
            @endif
        @endcan
        <a href="{{ route('finance.ordres.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

{{-- Bandeau annulation --}}
@if((int) $ordre->statut === \App\Models\Finance\Ordre::STATUT_ANNULE)
    <div class="alert alert-warning">
        <strong><i class="fas fa-ban me-1"></i> Ordre annulé</strong>
        le {{ $ordre->annule_at?->format('d/m/Y H:i') }} par {{ $ordre->annuleur?->name ?? '—' }}
        <div class="mt-1"><strong>Motif :</strong> {{ $ordre->motif_annulation }}</div>
    </div>
@endif

{{-- Modal d'annulation --}}
@can('validate:operation')
@if($ordre->peutEtreAnnule())
<div class="modal fade" id="modal-annuler-ordre" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.ordres.annuler', $ordre) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban text-warning me-2"></i> Annuler l'ordre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    L'ordre <strong>{{ $ordre->numero_ordre }}</strong> passera au statut <strong>Annulé</strong>.
                    Cette action est irréversible. L'ordre conservera sa trace pour l'audit.
                </div>
                <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
                <textarea name="motif_annulation" class="form-control" rows="4" required minlength="10" maxlength="500"
                          placeholder="Justifier l'annulation (min. 10 caractères)…"></textarea>
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

<div class="row g-3">
    <div class="col-lg-8">
        {{-- ═════════ PRÉSENTATION À L'IMAGE DU DOCUMENT ═════════ --}}
        <div class="card data-card">
            <div class="card-body">
                {{-- Entête institutionnel --}}
                <div class="text-center mb-4 border-bottom pb-3">
                    @if($ordre->modele->entete_soustitre)
                        <div class="text-muted small">{{ $ordre->modele->entete_soustitre }}</div>
                    @endif
                    <h2 class="mt-2">{{ $ordre->modele->entete_titre ?? $ordre->modele->libelle }}</h2>
                    <div class="fs-5 text-primary">N° {{ $ordre->numero_ordre }}</div>
                    <div class="mt-2">
                        <em>EXERCICE BUDGETAIRE : <strong>{{ $ordre->exercice?->libelle ?? $ordre->exercice?->exercice }}</strong></em>
                    </div>
                </div>

                @if($ordre->modele->phrase_intro)
                    <p class="fst-italic">{{ $ordre->modele->phrase_intro }}</p>
                @endif

                {{-- Bénéficiaire / Client --}}
                @if($ordre->beneficiaire_source)
                <div class="alert alert-secondary">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $ordre->beneficiaire_label_ui }} :</strong>
                            {{ $ordre->beneficiaire_libelle ?? '—' }}
                            <span class="badge bg-secondary ms-2">{{ \App\Models\Finance\Ordre::BENEFICIAIRE_SOURCES[$ordre->beneficiaire_source] ?? $ordre->beneficiaire_source }}</span>
                            @if($ordre->beneficiaire_infos_json)
                                <div class="small text-muted mt-1">
                                    @foreach(['entite'=>'Entité', 'email'=>'Email', 'telephone'=>'Tél', 'nif'=>'NIF', 'adresse'=>'Adresse'] as $k => $lbl)
                                        @if(!empty($ordre->beneficiaire_infos_json[$k]))
                                            <span class="me-3"><strong>{{ $lbl }} :</strong> {{ $ordre->beneficiaire_infos_json[$k] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Ligne budgétaire --}}
                @if($ordre->budgetLigne)
                <div class="alert alert-light border">
                    <strong>Ligne budgétaire :</strong>
                    <code>{{ $ordre->budgetLigne->id_budgetligne }}</code>
                    · {{ $ordre->budgetLigne->ligne?->libelle ?? $ordre->budgetLigne->commentaire }}
                </div>
                @endif

                {{-- Compte de trésorerie --}}
                @if($ordre->compte)
                <div class="alert alert-{{ $ordre->compte->type_couleur }}-subtle border-{{ $ordre->compte->type_couleur }}-subtle border">
                    <strong>
                        <i class="fas {{ $ordre->compte->type_icone }} me-1"></i>
                        {{ $ordre->sens === 'depense' ? 'Compte à débiter' : 'Compte à créditer' }} :
                    </strong>
                    {{ $ordre->compte->nom }}
                    <span class="badge bg-{{ $ordre->compte->type_couleur }} ms-1">{{ $ordre->compte->type_libelle }}</span>
                    @if($ordre->compte->rib) · <code>{{ $ordre->compte->rib }}</code>@endif
                    · <span class="text-muted">Solde : {{ number_format((float) $ordre->compte->solde, 0, ',', ' ') }} {{ $ordre->compte->devise ?: 'XAF' }}</span>
                </div>
                @endif

                {{-- Champs dynamiques du modèle --}}
                <div class="row g-3">
                    @foreach($ordre->modele->champs as $c)
                        @php $val = $ordre->donnees_json[$c->code_champ] ?? '—'; @endphp
                        <div class="col-md-{{ $c->largeur_col }}">
                            <div class="text-muted small text-uppercase">{{ $c->label_personnalise }}</div>
                            <div class="fw-semibold">
                                @if($c->type_saisie === 'number' && is_numeric($val))
                                    {{ number_format((float) $val, 0, ',', ' ') }}
                                @elseif($c->type_saisie === 'date' && $val && $val !== '—')
                                    {{ \Carbon\Carbon::parse($val)->format('d/m/Y') }}
                                @elseif($c->type_saisie === 'select')
                                    {{ ($c->options_json ?? [])[$val] ?? $val }}
                                @else
                                    {{ $val }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Détails ventilés --}}
                @if($ordre->details->isNotEmpty())
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted small">Détail ventilé</h6>
                    <table class="table table-sm border">
                        <thead class="table-light">
                            <tr>
                                <th>Désignation</th>
                                <th>Libellé</th>
                                <th class="text-end">Qté</th>
                                <th class="text-end">Prix unit.</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($ordre->details as $d)
                            <tr>
                                <td><small>{{ $d->rubrique?->libelle ?? '—' }}</small></td>
                                <td>{{ $d->libelle }}
                                    @if($d->observation)<br><small class="text-muted">{{ $d->observation }}</small>@endif
                                </td>
                                <td class="text-end">{{ number_format((float) $d->quantite, 2, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format((float) $d->prix_unitaire, 0, ',', ' ') }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $d->montant, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL</th>
                                <th class="text-end fs-6">{{ number_format((float) $ordre->montant_total_details, 0, ',', ' ') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                {{-- Total + phrase de conclusion --}}
                <div class="mt-4 p-3 bg-light rounded text-center">
                    <div class="text-muted small">{{ $ordre->modele->phrase_conclusion ?? 'MONTANT TOTAL :' }}</div>
                    <div class="display-6 fw-bold text-{{ $ordre->sens_couleur }}">
                        {{ number_format((float) $ordre->montant, 0, ',', ' ') }} {{ $ordre->compte?->devise ?: 'XAF' }}
                    </div>
                    <div class="mt-2 fst-italic text-muted">
                        <strong>Arrêté à la somme de :</strong> {{ $ordre->montant_en_lettres }}
                    </div>
                </div>

                {{-- Justificatifs --}}
                @if($ordre->piecesJointes->isNotEmpty())
                <div class="mt-4">
                    <h6 class="text-uppercase text-muted small"><i class="fas fa-paperclip me-1"></i> Justificatifs</h6>
                    <div class="row g-2">
                        @foreach($ordre->piecesJointes as $pj)
                            @php
                                $url  = asset('storage/' . ltrim($pj->fichier, '/'));
                                $ext  = strtolower(pathinfo($pj->nom_original ?? $pj->fichier, PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                                $isPdf = $ext === 'pdf';
                                $kind  = $isImg ? 'image' : ($isPdf ? 'pdf' : 'file');
                            @endphp
                            <div class="col-md-4 col-lg-3">
                                <div class="border rounded p-2 h-100 d-flex flex-column">
                                    @if($isImg)
                                        <button type="button" class="btn p-0 border-0 bg-transparent preview-trigger"
                                                data-url="{{ $url }}" data-kind="image" data-name="{{ $pj->nom_original }}">
                                            <img src="{{ $url }}" alt="" class="w-100 rounded" style="max-height:150px;object-fit:cover;" loading="lazy">
                                        </button>
                                    @elseif($isPdf)
                                        <button type="button" class="btn p-0 border-0 bg-transparent preview-trigger"
                                                data-url="{{ $url }}" data-kind="pdf" data-name="{{ $pj->nom_original }}">
                                            <div class="text-center p-3 bg-light rounded">
                                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                            </div>
                                        </button>
                                    @else
                                        <div class="text-center p-3 bg-light rounded">
                                            <i class="fas fa-file fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="mt-2 small">
                                        <div class="text-truncate fw-semibold" title="{{ $pj->nom_original }}">{{ $pj->nom_original }}</div>
                                        <div class="d-flex gap-2 mt-1">
                                            @if($isImg || $isPdf)
                                                <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 preview-trigger"
                                                        data-url="{{ $url }}" data-kind="{{ $kind }}" data-name="{{ $pj->nom_original }}">
                                                    <i class="fas fa-eye me-1"></i> Prévisualiser
                                                </button>
                                            @else
                                                <a href="{{ $url }}" download class="btn btn-sm btn-outline-primary flex-grow-1">
                                                    <i class="fas fa-download me-1"></i> Télécharger
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Signataires --}}
                @if($ordre->modele->signataires->isNotEmpty())
                <div class="mt-4 row g-3">
                    @foreach($ordre->modele->signataires as $s)
                        <div class="col">
                            <div class="border rounded p-3 text-center" style="min-height:120px;">
                                <div class="text-uppercase small fw-semibold">{{ $s->role_libelle }}</div>
                                @if($s->userParDefaut)
                                    <div class="mt-4 text-muted small">{{ $s->userParDefaut->name }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Métadonnées & workflow --}}
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Métadonnées</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Modèle</dt>
                    <dd class="col-7"><code>{{ $ordre->modele->code }}</code></dd>

                    <dt class="col-5 text-muted">Exercice</dt>
                    <dd class="col-7">{{ $ordre->exercice?->libelle }}</dd>

                    <dt class="col-5 text-muted">Statut</dt>
                    <dd class="col-7"><span class="badge bg-{{ $ordre->statut_couleur }}">{{ $ordre->statut_libelle }}</span></dd>

                    <dt class="col-5 text-muted">Créé le</dt>
                    <dd class="col-7">{{ $ordre->created_at?->format('d/m/Y H:i') }}</dd>

                    @if($ordre->grandLivre)
                    <dt class="col-5 text-muted">Grand Livre</dt>
                    <dd class="col-7">
                        <a href="{{ route('finance.grand-livre.show', $ordre->grand_livre_id) }}">
                            écriture #{{ $ordre->grand_livre_id }}
                        </a>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>

        @if(auth()->user()->hasRole('super-admin'))
        <div class="alert alert-warning small">
            <i class="fas fa-shield-halved me-1"></i>
            <strong>Super-admin :</strong> vous pouvez modifier / supprimer cet ordre quel que soit son statut.
        </div>
        @endif
    </div>
</div>

{{-- ═════════ MODALE PRÉVISUALISATION INLINE ═════════ --}}
<div class="modal fade" id="modal-preview-pj" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:90vh;">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="preview-pj-title">
                    <i class="fas fa-eye me-2"></i> <span data-preview-name>Prévisualisation</span>
                </h5>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-secondary" data-preview-download>
                        <i class="fas fa-download me-1"></i> Télécharger
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative bg-dark text-center overflow-hidden" style="min-height:0;">
                {{-- Loader --}}
                <div class="position-absolute top-50 start-50 translate-middle text-white" data-preview-loader>
                    <div class="spinner-border" role="status"></div>
                    <div class="small mt-2">Chargement…</div>
                </div>
                {{-- Container image --}}
                <img src="" alt="" class="d-none img-fluid h-100 w-auto mx-auto" style="object-fit:contain;max-height:100%;" data-preview-image>
                {{-- Container PDF --}}
                <iframe src="" class="d-none w-100 h-100 border-0 bg-white" data-preview-pdf title="Aperçu PDF"></iframe>
                {{-- Fallback --}}
                <div class="d-none text-white p-4" data-preview-fallback>
                    <i class="fas fa-file fa-4x mb-3"></i>
                    <div>Ce type de fichier ne peut pas être prévisualisé.</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl   = document.getElementById('modal-preview-pj');
    if (!modalEl) return;
    const modal     = new bootstrap.Modal(modalEl);
    const img       = modalEl.querySelector('[data-preview-image]');
    const pdf       = modalEl.querySelector('[data-preview-pdf]');
    const fallback  = modalEl.querySelector('[data-preview-fallback]');
    const loader    = modalEl.querySelector('[data-preview-loader]');
    const nameSpan  = modalEl.querySelector('[data-preview-name]');
    const dlLink    = modalEl.querySelector('[data-preview-download]');

    function reset() {
        img.classList.add('d-none');       img.src = '';
        pdf.classList.add('d-none');       pdf.src = '';
        fallback.classList.add('d-none');
        loader.classList.remove('d-none');
    }

    document.querySelectorAll('.preview-trigger').forEach(btn => {
        btn.addEventListener('click', function () {
            reset();
            const url  = this.dataset.url;
            const kind = this.dataset.kind;
            const name = this.dataset.name || 'Justificatif';
            nameSpan.textContent = name;
            dlLink.href = url;
            dlLink.setAttribute('download', name);
            modal.show();

            if (kind === 'image') {
                img.onload = () => loader.classList.add('d-none');
                img.onerror = () => { loader.classList.add('d-none'); fallback.classList.remove('d-none'); };
                img.src = url;
                img.classList.remove('d-none');
            } else if (kind === 'pdf') {
                pdf.onload = () => loader.classList.add('d-none');
                pdf.src = url;
                pdf.classList.remove('d-none');
            } else {
                loader.classList.add('d-none');
                fallback.classList.remove('d-none');
            }
        });
    });

    // Nettoyage à la fermeture (évite un iframe qui continue à jouer un PDF)
    modalEl.addEventListener('hidden.bs.modal', reset);
});
</script>
@endpush
@endsection
