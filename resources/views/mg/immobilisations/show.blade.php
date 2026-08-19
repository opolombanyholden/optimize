@extends('layouts.app')
@section('title', $immobilisation->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.immobilisations.index') }}">Immobilisations</a></li>
    <li class="breadcrumb-item active">{{ $immobilisation->designation }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            {{ $immobilisation->designation }}
            @if($immobilisation->est_totalement_amorti)
                <span class="badge bg-dark ms-2">Totalement amorti</span>
            @endif
            @if($immobilisation->date_sortie)
                <span class="badge bg-warning ms-2">Sorti le {{ $immobilisation->date_sortie->format('d/m/Y') }}</span>
            @endif
        </h1>
        <p class="text-muted mb-0">
            <code>{{ $immobilisation->code ?: '—' }}</code>
            · <strong>Catégorie :</strong> {{ \App\Models\Immobilisation::CATEGORIES[$immobilisation->categorie] ?? $immobilisation->categorie ?? '—' }}
            · <strong>Localisation :</strong> {{ $immobilisation->localisation ?: '—' }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update:immobilisation')
            <a href="{{ route('mg.immobilisations.edit', $immobilisation) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            @if(!$immobilisation->est_totalement_amorti && !$immobilisation->date_sortie && $immobilisation->methode_amortissement === \App\Models\Immobilisation::METHODE_LINEAIRE)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-dotation">
                    <i class="fas fa-calculator me-1"></i> Enregistrer dotation
                </button>
            @endif
            @if(!$immobilisation->date_sortie)
                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-sortie">
                    <i class="fas fa-sign-out-alt me-1"></i> Sortir
                </button>
            @endif
        @endcan
        <a href="{{ route('mg.immobilisations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Valeur d'acquisition</small><h4 class="mb-0">{{ number_format((float) $immobilisation->valeur_acquisition, 0, ',', ' ') }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">VNC</small><h4 class="mb-0 text-primary">{{ number_format((float) $immobilisation->valeur_nette_comptable, 0, ',', ' ') }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Cumul amortissement</small><h4 class="mb-0 text-warning">{{ number_format((float) $immobilisation->amortissement_cumule, 0, ',', ' ') }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Dotation mensuelle</small><h4 class="mb-0">{{ number_format($immobilisation->dotation_mensuelle, 0, ',', ' ') }}</h4></div></div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-list me-2"></i> Historique des dotations ({{ $immobilisation->dotations->count() }})</h6></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr>
                        <th>Période</th><th class="text-end">Dotation</th><th class="text-end">VNC après</th><th class="text-end">Cumul</th><th>Auteur</th>
                    </tr></thead>
                    <tbody>
                    @forelse($immobilisation->dotations as $d)
                        <tr>
                            <td><small>{{ $d->periode_debut->format('m/Y') }}</small></td>
                            <td class="text-end fw-semibold">{{ number_format((float) $d->montant, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format((float) $d->vnc_apres, 0, ',', ' ') }}</td>
                            <td class="text-end text-muted">{{ number_format((float) $d->cumul_apres, 0, ',', ' ') }}</td>
                            <td><small>{{ $d->auteur?->name ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune dotation enregistrée.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Dysfonctionnements ({{ $immobilisation->dysfonctionnements->count() }})</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Libellé</th><th>Type</th><th>Statut</th><th>Signalement</th></tr></thead>
                    <tbody>
                    @forelse($immobilisation->dysfonctionnements as $d)
                        <tr>
                            <td><a href="{{ route('mg.dysfonctionnements.show', $d) }}">{{ $d->label }}</a></td>
                            <td><small>{{ $d->type?->libelle ?? '—' }}</small></td>
                            <td><span class="badge bg-{{ $d->statut_couleur }}">{{ $d->statut_libelle }}</span></td>
                            <td><small>{{ $d->date_signalement?->format('d/m/Y') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Aucun dysfonctionnement signalé.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>Méthode :</strong> {{ \App\Models\Immobilisation::METHODES[$immobilisation->methode_amortissement] ?? '—' }}</p>
                <p class="mb-1"><strong>Durée :</strong> {{ $immobilisation->duree_amortissement ?? '—' }} mois</p>
                <p class="mb-1"><strong>Valeur résiduelle :</strong> {{ number_format((float) ($immobilisation->valeur_residuelle ?? 0), 0, ',', ' ') }}</p>
                <p class="mb-1"><strong>Acquisition :</strong> {{ $immobilisation->date_acquisition?->format('d/m/Y') ?? '—' }}</p>
                <p class="mb-1"><strong>Mise en service :</strong> {{ $immobilisation->date_mise_en_service?->format('d/m/Y') ?? '—' }}</p>
                <p class="mb-1"><strong>État :</strong> {{ \App\Models\Immobilisation::ETATS[$immobilisation->etat] ?? '—' }}</p>
                <p class="mb-1"><strong>Affecté à :</strong> {{ $immobilisation->employe?->noms ?? '—' }}</p>
                <p class="mb-0"><strong>Entité :</strong> {{ $immobilisation->entite?->libelle ?? '—' }}</p>
                @if($immobilisation->date_sortie)
                    <hr>
                    <p class="mb-1"><strong>Sortie :</strong> {{ $immobilisation->date_sortie->format('d/m/Y') }}</p>
                    <p class="mb-0"><strong>Motif :</strong> {{ $immobilisation->motif_sortie }}</p>
                @endif
            </div>
        </div>

        @if($immobilisation->piecesJointes->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes ({{ $immobilisation->piecesJointes->count() }})</h6></div>
                <div class="card-body">
                    @include('mg._partials.pieces-jointes', ['pieces' => $immobilisation->piecesJointes])
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═════════ MODALES ═════════ --}}
@can('update:immobilisation')
@if(!$immobilisation->est_totalement_amorti && !$immobilisation->date_sortie && $immobilisation->methode_amortissement === \App\Models\Immobilisation::METHODE_LINEAIRE)
<div class="modal fade" id="modal-dotation" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.immobilisations.dotation', $immobilisation) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-calculator me-2"></i> Enregistrer une dotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    Dotation mensuelle théorique : <strong>{{ number_format($immobilisation->dotation_mensuelle, 0, ',', ' ') }} XAF</strong>.
                    Le montant sera plafonné à la VNC restante.
                </div>
                <label class="form-label">Période (mois)</label>
                <input type="month" name="periode" class="form-control" value="{{ now()->format('Y-m') }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endif

@if(!$immobilisation->date_sortie)
<div class="modal fade" id="modal-sortie" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.immobilisations.sortir', $immobilisation) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-sign-out-alt text-warning me-2"></i> Sortir l'immobilisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Date de sortie <span class="text-danger">*</span></label>
                <input type="date" name="date_sortie" class="form-control mb-3" value="{{ now()->format('Y-m-d') }}" required>
                <label class="form-label">Motif <span class="text-danger">*</span></label>
                <textarea name="motif_sortie" class="form-control" rows="3" required minlength="3" maxlength="255"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection
