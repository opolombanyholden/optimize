@extends('layouts.app')
@section('title', $facture->numero)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.factures.index') }}">Factures</a></li>
        <li class="breadcrumb-item active">{{ $facture->numero }}</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $facture->numero }}</h1>
        <p class="text-muted mb-0">
            {{ $facture->sens_libelle }} ·
            <span class="badge bg-{{ $facture->statut_couleur }}">{{ $facture->statut_libelle }}</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.factures.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @can('update:facture')
            @if($facture->est_modifiable)
                <a href="{{ route('finance.factures.edit', $facture) }}" class="btn btn-secondary"><i class="fas fa-pen me-1"></i> Éditer</a>
            @endif
        @endcan
        @can('validate:facture')
            @if($facture->est_validable)
                <form action="{{ route('finance.factures.valider', $facture) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-success"><i class="fas fa-check me-1"></i> Valider</button>
                </form>
            @endif
            @if($facture->peutEtreOrdonnancee())
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalOrdonnancer">
                    <i class="fas fa-stamp me-1"></i> Ordonnancer (OK paiement)
                </button>
            @endif
            @if($facture->est_annulable)
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalAnnul"><i class="fas fa-ban me-1"></i> Annuler</button>
            @endif
        @endcan
    </div>
</div>

@if($facture->sens === 'depense')
    @if($facture->estOrdonnancee())
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-1"></i>
            <strong>Facture ordonnancée</strong> le {{ $facture->ordonnancee_at?->format('d/m/Y H:i') }}
            par {{ $facture->ordonnateur?->name ?? '—' }} — <strong>OK pour paiement</strong>.
            @if($facture->motivation_ordonnancement)<br><em>Motivation :</em> {{ $facture->motivation_ordonnancement }}@endif
        </div>
    @else
        <div class="alert alert-warning mb-3">
            <i class="fas fa-clock me-1"></i>
            <strong>Facture non ordonnancée</strong> — les ordres de paiement ne pourront pas être créés tant que l'ordonnateur n'a pas donné son OK.
        </div>
    @endif
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header"><strong>Détails</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Date émission</dt><dd class="col-sm-8">{{ $facture->date_emission?->format('d/m/Y') }}</dd>
                    @if($facture->date_echeance)
                        <dt class="col-sm-4 text-muted">Date échéance</dt><dd class="col-sm-8">{{ $facture->date_echeance->format('d/m/Y') }}</dd>
                    @endif
                    @if($facture->reference_externe)
                        <dt class="col-sm-4 text-muted">Réf. externe</dt><dd class="col-sm-8">{{ $facture->reference_externe }}</dd>
                    @endif
                    <dt class="col-sm-4 text-muted">Tiers</dt>
                    <dd class="col-sm-8">
                        @if($tiers)
                            <strong>{{ $tiers->nom ?? $tiers->raison_sociale }}</strong>
                            <br><small class="text-muted">{{ ucfirst($facture->tiers_type) }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4 text-muted">Objet</dt><dd class="col-sm-8">{{ $facture->objet }}</dd>
                    @if($facture->exercice)
                        <dt class="col-sm-4 text-muted">Exercice</dt><dd class="col-sm-8">{{ $facture->exercice->libelle }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        @if($facture->commentaire)
            <div class="alert alert-light border mt-3">
                <strong>Commentaire :</strong> {{ $facture->commentaire }}
            </div>
        @endif

        @if($facture->motif_annulation)
            <div class="alert alert-danger mt-3">
                <strong>Motif d'annulation :</strong> {{ $facture->motif_annulation }}
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header bg-primary text-white"><strong>Montants</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-6 text-muted">Montant HT</dt>
                    <dd class="col-sm-6 text-end">{{ number_format((float) $facture->montant_ht, 0, ',', ' ') }}</dd>

                    <dt class="col-sm-6 text-muted">TVA ({{ $facture->taux_tva }}%)</dt>
                    <dd class="col-sm-6 text-end">{{ number_format((float) $facture->montant_tva, 0, ',', ' ') }}</dd>

                    <dt class="col-sm-6 fw-bold">Montant TTC</dt>
                    <dd class="col-sm-6 text-end fw-bold fs-5">{{ number_format((float) $facture->montant_ttc, 0, ',', ' ') }}</dd>

                    <dt class="col-sm-6 text-muted">Déjà réglé</dt>
                    <dd class="col-sm-6 text-end text-success">{{ number_format((float) $facture->montant_regle, 0, ',', ' ') }}</dd>

                    <dt class="col-sm-6 text-warning">Reste à régler</dt>
                    <dd class="col-sm-6 text-end text-warning fw-bold">{{ number_format($facture->montant_reste_a, 0, ',', ' ') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ ORDRES RATTACHÉS ═════════ --}}
<div class="card data-card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>
            <i class="fas fa-file-invoice me-1"></i>
            Ordres {{ $facture->sens === 'depense' ? 'de paiement' : 'de recette' }} rattachés
        </strong>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Réglé : <strong class="text-success">{{ number_format($facture->montant_ordres_executes, 0, ',', ' ') }}</strong> / {{ number_format((float) $facture->montant_ttc, 0, ',', ' ') }}</span>
            @if($facture->solde_restant > 0.01)
                <span class="badge bg-warning text-dark">Solde restant : {{ number_format($facture->solde_restant, 0, ',', ' ') }}</span>
            @else
                <span class="badge bg-success">Soldée</span>
            @endif
            @can('create:operation')
                <a href="{{ route('finance.ordres.create', ['modele' => \App\Models\Finance\OrdreModele::where('sens', $facture->sens)->first()?->id, 'facture_id' => $facture->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> Nouvel ordre
                </a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>N° ordre</th><th>Compte</th><th class="text-end">Montant</th><th>Statut</th><th>Créé le</th></tr>
            </thead>
            <tbody>
            @forelse($facture->ordres as $o)
                <tr>
                    <td><a href="{{ route('finance.ordres.show', $o) }}"><code>{{ $o->numero_ordre }}</code></a></td>
                    <td>
                        @if($o->compte)
                            <small><i class="fas {{ $o->compte->type_icone }} text-muted me-1"></i>{{ $o->compte->nom }}</small>
                        @else
                            <em class="text-muted small">—</em>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">{{ number_format((float) $o->montant, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $o->statut_couleur }}">{{ $o->statut_libelle }}</span></td>
                    <td><small class="text-muted">{{ $o->created_at?->format('d/m/Y') }}</small></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aucun ordre rattaché.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═════════ PIÈCES JOINTES ═════════ --}}
@if($facture->piecesJointes->isNotEmpty())
<div class="card data-card mt-3">
    <div class="card-header">
        <strong><i class="fas fa-paperclip me-1"></i> Pièces jointes ({{ $facture->piecesJointes->count() }})</strong>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach($facture->piecesJointes as $pj)
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
                                <div class="text-center p-3 bg-light rounded"><i class="fas fa-file-pdf fa-3x text-danger"></i></div>
                            </button>
                        @else
                            <div class="text-center p-3 bg-light rounded"><i class="fas fa-file fa-3x text-muted"></i></div>
                        @endif
                        <div class="mt-2 small">
                            <div class="text-truncate fw-semibold" title="{{ $pj->nom_original }}">{{ $pj->nom_original }}</div>
                            @if($isImg || $isPdf)
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-1 preview-trigger"
                                        data-url="{{ $url }}" data-kind="{{ $kind }}" data-name="{{ $pj->nom_original }}">
                                    <i class="fas fa-eye me-1"></i> Prévisualiser
                                </button>
                            @else
                                <a href="{{ $url }}" download class="btn btn-sm btn-outline-primary w-100 mt-1">
                                    <i class="fas fa-download me-1"></i> Télécharger
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modale preview identique à celle des ordres --}}
<div class="modal fade" id="modal-preview-pj-facture" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:90vh;">
            <div class="modal-header">
                <h5 class="modal-title text-truncate"><i class="fas fa-eye me-2"></i> <span data-preview-name>Prévisualisation</span></h5>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-secondary" data-preview-download>
                        <i class="fas fa-download me-1"></i> Télécharger
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative bg-dark text-center overflow-hidden">
                <div class="position-absolute top-50 start-50 translate-middle text-white" data-preview-loader>
                    <div class="spinner-border"></div>
                    <div class="small mt-2">Chargement…</div>
                </div>
                <img src="" alt="" class="d-none img-fluid h-100 w-auto mx-auto" style="object-fit:contain;max-height:100%;" data-preview-image>
                <iframe src="" class="d-none w-100 h-100 border-0 bg-white" data-preview-pdf title="Aperçu PDF"></iframe>
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
    const modalEl = document.getElementById('modal-preview-pj-facture');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    const img = modalEl.querySelector('[data-preview-image]');
    const pdf = modalEl.querySelector('[data-preview-pdf]');
    const fb  = modalEl.querySelector('[data-preview-fallback]');
    const loader = modalEl.querySelector('[data-preview-loader]');
    const nameSpan = modalEl.querySelector('[data-preview-name]');
    const dlLink = modalEl.querySelector('[data-preview-download]');
    function reset() {
        img.classList.add('d-none'); img.src = '';
        pdf.classList.add('d-none'); pdf.src = '';
        fb.classList.add('d-none');
        loader.classList.remove('d-none');
    }
    document.querySelectorAll('.preview-trigger').forEach(btn => {
        btn.addEventListener('click', function () {
            reset();
            const url = this.dataset.url, kind = this.dataset.kind, name = this.dataset.name || 'Justificatif';
            nameSpan.textContent = name;
            dlLink.href = url; dlLink.setAttribute('download', name);
            modal.show();
            if (kind === 'image') {
                img.onload = () => loader.classList.add('d-none');
                img.onerror = () => { loader.classList.add('d-none'); fb.classList.remove('d-none'); };
                img.src = url; img.classList.remove('d-none');
            } else if (kind === 'pdf') {
                pdf.onload = () => loader.classList.add('d-none');
                pdf.src = url; pdf.classList.remove('d-none');
            } else {
                loader.classList.add('d-none'); fb.classList.remove('d-none');
            }
        });
    });
    modalEl.addEventListener('hidden.bs.modal', reset);
});
</script>
@endpush
@endif

@if($facture->operations->isNotEmpty())
    <div class="card data-card mt-3">
        <div class="card-header"><strong>Opérations financières liées</strong></div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>N°</th><th>Date</th><th>Objet</th><th class="text-end">Montant</th><th>Statut</th></tr></thead>
                <tbody>
                @foreach($facture->operations as $op)
                    <tr>
                        <td><a href="{{ route('finance.operations.show', $op) }}">{{ $op->numero }}</a></td>
                        <td>{{ $op->date_operation?->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($op->objet, 60) }}</td>
                        <td class="text-end">{{ number_format((float) $op->montant, 0, ',', ' ') }}</td>
                        <td><span class="badge bg-{{ $op->statut_couleur }}">{{ $op->statut_libelle }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($facture->peutEtreOrdonnancee())
    <div class="modal fade" id="modalOrdonnancer" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('finance.factures.ordonnancer', $facture) }}" method="POST" class="modal-content">@csrf
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-stamp text-primary me-2"></i> Ordonnancer la facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        En tant qu'ordonnateur, vous donnez le OK pour paiement. Les ordres de paiement pourront ensuite être créés (en une ou plusieurs tranches).
                    </div>
                    <label class="form-label">Motivation (facultatif)</label>
                    <textarea name="motivation" class="form-control" rows="3" maxlength="2000" placeholder="Contrôle service fait, conformité constatée…"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Ordonnancer</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($facture->est_annulable)
    <div class="modal fade" id="modalAnnul" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('finance.factures.annuler', $facture) }}" method="POST" class="modal-content">@csrf
                <div class="modal-header"><h5 class="modal-title">Annuler la facture</h5></div>
                <div class="modal-body">
                    <div class="mb-0">
                        <label class="form-label">Motif <span class="text-danger">*</span></label>
                        <textarea name="motif_annulation" class="form-control" rows="3" required maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Retour</button>
                    <button class="btn btn-danger">Confirmer l'annulation</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
