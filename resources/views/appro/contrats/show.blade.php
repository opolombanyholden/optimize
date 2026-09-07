@extends('layouts.app')

@section('title', $contrat->reference)

@push('styles')
<style>
.ct-panel { background:#fff; border:1px solid #E0DFDC; padding:1.25rem 1.5rem; margin-bottom:1rem; }
.ct-title { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
.ct-meta-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
@media(max-width:768px){ .ct-meta-grid { grid-template-columns:1fr 1fr; } }
.ct-meta-lbl { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#666; font-weight:700; margin-bottom:.2rem; }
.ct-meta-val { font-weight:600; color:#191919; }
.ct-badge { display:inline-block; font-size:.7rem; text-transform:uppercase; font-weight:700; letter-spacing:.04em; padding:.2rem .6rem; }
.ct-badge.brouillon { background:#F1F5F9; color:#64748B; }
.ct-badge.actif { background:#DCFCE7; color:#137333; }
.ct-badge.expire { background:#FEE2E2; color:#DC2626; }
.ct-badge.resilie { background:#FEE2E2; color:#DC2626; }
.ct-badge.renouvele { background:#DBEAFE; color:#1E40AF; }
.ct-alert-expir { border-left:4px solid #D97706; background:#FFFBEB; padding:.75rem 1rem; margin-bottom:1rem; }
.ct-alert-expired { border-left:4px solid #DC2626; background:#FEF2F2; padding:.75rem 1rem; margin-bottom:1rem; }
</style>
@endpush

@section('content')

@if($contrat->est_expire && $contrat->statut === \App\Models\ContratFournisseur::STATUT_ACTIF)
<div class="ct-alert-expired">
    <strong><i class="fas fa-triangle-exclamation me-1" style="color:#DC2626;"></i>Engagement expiré</strong> — la date de fin ({{ $contrat->date_fin->format('d/m/Y') }}) est dépassée. Renouveler ou marquer comme expiré.
</div>
@elseif($contrat->est_prochain_expiration)
<div class="ct-alert-expir">
    <strong><i class="fas fa-clock me-1" style="color:#D97706;"></i>Expiration proche</strong> — plus que <strong>{{ $contrat->jours_avant_expiration }} jour(s)</strong> avant la fin de l'engagement.
    @if($contrat->preavis_resiliation_jours)
        Préavis de résiliation : {{ $contrat->preavis_resiliation_jours }} jours.
    @endif
</div>
@endif

<div class="ct-title">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-file-contract me-2" style="color:#0A66C2;"></i>{{ $contrat->reference }}</h1>
        <div class="text-muted">{{ $contrat->objet }}</div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="ct-badge {{ $contrat->statut }}">{{ $contrat->statut_libelle }}</span>
        @if($contrat->statut === \App\Models\ContratFournisseur::STATUT_BROUILLON)
            <form action="{{ route('appro.contrats.activer', $contrat) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Activer</button>
            </form>
        @endif
        @if($contrat->statut === \App\Models\ContratFournisseur::STATUT_ACTIF)
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ctRenewModal"><i class="fas fa-arrows-rotate me-1"></i>Renouveler</button>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#ctResilierModal"><i class="fas fa-ban me-1"></i>Résilier</button>
        @endif
        <a href="{{ route('appro.contrats.edit', $contrat) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen me-1"></i>Modifier</a>
        <a href="{{ route('appro.contrats.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="ct-panel">
    <div class="ct-meta-grid">
        <div><div class="ct-meta-lbl">Fournisseur</div><div class="ct-meta-val">{{ $contrat->fournisseur->raison_sociale ?? '—' }}</div></div>
        <div><div class="ct-meta-lbl">Type</div><div class="ct-meta-val">{{ $contrat->type_libelle }}</div></div>
        <div><div class="ct-meta-lbl">Devise</div><div class="ct-meta-val">{{ $contrat->devise }}</div></div>
        <div><div class="ct-meta-lbl">Signature</div><div class="ct-meta-val">{{ $contrat->date_signature?->format('d/m/Y') ?? '—' }}</div></div>
        <div><div class="ct-meta-lbl">Début</div><div class="ct-meta-val">{{ $contrat->date_debut?->format('d/m/Y') }}</div></div>
        <div><div class="ct-meta-lbl">Fin</div><div class="ct-meta-val">{{ $contrat->date_fin?->format('d/m/Y') ?? 'Indéterminée' }}</div></div>
        <div><div class="ct-meta-lbl">Montant HT</div><div class="ct-meta-val">{{ $contrat->montant_ht !== null ? number_format((float)$contrat->montant_ht, 2, ',', ' ') : '—' }}</div></div>
        <div><div class="ct-meta-lbl">Montant TTC</div><div class="ct-meta-val">{{ $contrat->montant_ttc !== null ? number_format((float)$contrat->montant_ttc, 2, ',', ' ') : '—' }}</div></div>
        <div><div class="ct-meta-lbl">Renouvellement auto</div><div class="ct-meta-val">{{ $contrat->renouvellement_auto ? 'Oui' : 'Non' }}</div></div>
    </div>
    @if($contrat->description)
        <hr>
        <div class="ct-meta-lbl">Description</div>
        <div>{!! nl2br(e($contrat->description)) !!}</div>
    @endif
    @if($contrat->conditions)
        <hr>
        <div class="ct-meta-lbl">Conditions particulières</div>
        <div>{!! nl2br(e($contrat->conditions)) !!}</div>
    @endif
</div>

{{-- Modalités de paiement --}}
<div class="ct-panel">
    <h5><i class="fas fa-money-check-dollar me-2" style="color:#0A66C2;"></i>Modalités de paiement</h5>
    <div class="ct-meta-grid">
        <div><div class="ct-meta-lbl">Type de paiement</div><div class="ct-meta-val">{{ $contrat->frequence_paiement_libelle }}</div></div>
        <div><div class="ct-meta-lbl">Montant / paiement</div><div class="ct-meta-val">{{ $contrat->montant_par_paiement !== null ? number_format((float)$contrat->montant_par_paiement, 2, ',', ' ').' '.$contrat->devise : '—' }}</div></div>
        <div><div class="ct-meta-lbl">Jour d'échéance</div><div class="ct-meta-val">{{ $contrat->jour_paiement ? 'Le '.$contrat->jour_paiement.' du mois' : '—' }}</div></div>
        <div><div class="ct-meta-lbl">Délai de règlement</div><div class="ct-meta-val">{{ $contrat->delai_paiement_jours !== null ? $contrat->delai_paiement_jours.' jours' : '—' }}</div></div>
        @if($contrat->prochaine_echeance)
        <div style="grid-column:span 2;">
            <div class="ct-meta-lbl">Prochaine échéance</div>
            <div class="ct-meta-val" style="color:#0A66C2;">
                <i class="fas fa-calendar-check me-1"></i>{{ $contrat->prochaine_echeance->isoFormat('dddd D MMMM YYYY') }}
                <span class="text-muted small">({{ (int) now()->startOfDay()->diffInDays($contrat->prochaine_echeance->startOfDay(), false) }} jour(s))</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Avenants --}}
<div class="ct-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-file-signature me-2" style="color:#0A66C2;"></i>Avenants ({{ $contrat->avenants->count() }})</h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ctAvenantModal"><i class="fas fa-plus me-1"></i>Nouvel avenant</button>
    </div>
    @forelse($contrat->avenants as $av)
    <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
        <div style="flex-shrink:0; width:60px;">
            <div style="background:#DBEAFE; color:#1E40AF; text-align:center; padding:.5rem; font-weight:700;">N°{{ $av->numero }}</div>
        </div>
        <div style="flex:1;">
            <div><strong>{{ $av->objet }}</strong> · <small class="text-muted">{{ $av->date_avenant?->format('d/m/Y') }}</small></div>
            @if($av->impact)<div class="text-muted small mt-1">{{ $av->impact }}</div>@endif
            <div class="mt-1" style="font-size:.82rem;">
                @if($av->delta_montant_ht !== null)
                    <span class="badge bg-light text-dark"><i class="fas fa-euro-sign me-1"></i>{{ $av->delta_montant_ht > 0 ? '+' : '' }}{{ number_format((float)$av->delta_montant_ht, 2, ',', ' ') }} HT</span>
                @endif
                @if($av->nouvelle_date_fin)
                    <span class="badge bg-light text-dark"><i class="fas fa-calendar-day me-1"></i>Nouvelle fin : {{ $av->nouvelle_date_fin->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </div>
    @empty
        <p class="text-muted text-center py-3 mb-0"><i class="fas fa-file-signature me-1"></i>Aucun avenant.</p>
    @endforelse
</div>

{{-- Pièces jointes --}}
@if($contrat->piecesJointes->count())
<div class="ct-panel">
    <h5><i class="fas fa-paperclip me-2"></i>Pièces jointes</h5>
    <div class="d-flex flex-wrap gap-2">
        @foreach($contrat->piecesJointes as $pj)
        <a href="{{ $pj->url }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas {{ $pj->icone }} me-1"></i>{{ $pj->nom_original }}</a>
        @endforeach
    </div>
</div>
@endif

{{-- Historique de renouvellement --}}
@if($contrat->parent || $contrat->enfants->count())
<div class="ct-panel">
    <h5><i class="fas fa-history me-2"></i>Historique</h5>
    @if($contrat->parent)
        <div>Renouvelle : <a href="{{ route('appro.contrats.show', $contrat->parent) }}">{{ $contrat->parent->reference }}</a></div>
    @endif
    @foreach($contrat->enfants as $enfant)
        <div>Renouvelé par : <a href="{{ route('appro.contrats.show', $enfant) }}">{{ $enfant->reference }}</a></div>
    @endforeach
</div>
@endif

{{-- Modales --}}
<div class="modal fade" id="ctResilierModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('appro.contrats.resilier', $contrat) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header bg-danger text-white"><h5 class="modal-title">Résilier l'engagement</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted">L'engagement passe au statut « Résilié ». Aucune facture ne pourra plus lui être rattachée.</p>
            <label class="form-label">Motif (facultatif)</label>
            <textarea name="motif" class="form-control" rows="3"></textarea>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button><button class="btn btn-danger">Résilier</button></div>
    </form></div>
</div>

<div class="modal fade" id="ctRenewModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('appro.contrats.renouveler', $contrat) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header bg-primary text-white"><h5 class="modal-title">Renouveler l'engagement</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted">Un nouvel engagement est créé (copie), l'ancien passe en statut « Renouvelé ».</p>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Date de début *</label><input type="date" name="date_debut" class="form-control" required value="{{ $contrat->date_fin?->addDay()->format('Y-m-d') }}"></div>
                <div class="col-md-6"><label class="form-label">Date de fin</label><input type="date" name="date_fin" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Montant HT</label><input type="number" step="0.01" min="0" name="montant_ht" class="form-control" value="{{ $contrat->montant_ht }}"></div>
                <div class="col-md-6"><label class="form-label">Montant TTC</label><input type="number" step="0.01" min="0" name="montant_ttc" class="form-control" value="{{ $contrat->montant_ttc }}"></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button><button class="btn btn-primary">Créer le renouvellement</button></div>
    </form></div>
</div>

<div class="modal fade" id="ctAvenantModal" tabindex="-1">
    <div class="modal-dialog"><form action="{{ route('appro.contrats.avenants.store', $contrat) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header bg-primary text-white"><h5 class="modal-title">Nouvel avenant</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Date de l'avenant *</label><input type="date" name="date_avenant" class="form-control" required value="{{ now()->format('Y-m-d') }}"></div>
                <div class="col-md-6"><label class="form-label">Nouvelle date de fin</label><input type="date" name="nouvelle_date_fin" class="form-control"></div>
                <div class="col-12"><label class="form-label">Objet *</label><input type="text" name="objet" class="form-control" required maxlength="255"></div>
                <div class="col-12"><label class="form-label">Description / impact</label><textarea name="impact" class="form-control" rows="3"></textarea></div>
                <div class="col-md-6"><label class="form-label">Delta montant HT (+/-)</label><input type="number" step="0.01" name="delta_montant_ht" class="form-control"></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button><button class="btn btn-primary">Enregistrer l'avenant</button></div>
    </form></div>
</div>

@endsection
