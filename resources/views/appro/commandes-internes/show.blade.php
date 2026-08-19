@extends('layouts.app')
@section('title', $commande->numero)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes-internes.index') }}">Commandes internes</a></li>
    <li class="breadcrumb-item active">{{ $commande->numero }}</li>
</ol>
@endsection

@section('content')
@php $u = request()->user(); $estN1 = $commande->superieur_id === $u->id; @endphp
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><code>{{ $commande->numero }}</code>
            <span class="badge bg-{{ $commande->statut_couleur }} ms-2">{{ $commande->statut_libelle }}</span></h1>
        <p class="text-muted mb-0"><strong>Objet :</strong> {{ $commande->objet }}
            · <strong>Demandeur :</strong> {{ $commande->demandeur?->name }}
            · <strong>N+1 :</strong> {{ $commande->superieur?->name ?? '—' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($commande->peutEtreModifiee())
            <a href="{{ route('appro.commandes-internes.edit', $commande) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
        @endif
        @if($commande->peutEtreSoumise() && $commande->demandeur_id === $u->id)
            <form action="{{ route('appro.commandes-internes.soumettre-n1', $commande) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i> Soumettre au N+1</button></form>
        @endif
        @if($commande->peutEtreValideeN1() && $estN1)
            <form action="{{ route('appro.commandes-internes.valider-n1', $commande) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Valider (N+1)</button></form>
            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modal-refus"><i class="fas fa-times me-1"></i> Refuser</button>
        @endif
        @can('update:commande')
            @if($commande->peutEtreTransmise())
                <form action="{{ route('appro.commandes-internes.transmettre', $commande) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-primary"><i class="fas fa-hand-holding me-1"></i> Prendre en charge (Appro)</button></form>
            @endif
            @if($commande->peutEtreLivree())
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-livrer"><i class="fas fa-truck me-1"></i> Livrer</button>
            @endif
            @if($commande->peutEtreAssignee())
                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modal-assigner-n1"><i class="fas fa-user-check me-1"></i> Transmettre à un N+1</button>
            @endif
        @endcan
        @if($commande->peutEtreAnnulee())
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-annuler"><i class="fas fa-ban me-1"></i> Annuler</button>
        @endif
    </div>
</div>

@if($commande->motif_refus_n1)
    <div class="alert alert-warning"><strong>Refus N+1 :</strong> {{ $commande->motif_refus_n1 }}</div>
@endif
@if($commande->motif_annulation)
    <div class="alert alert-danger"><strong>Annulée :</strong> {{ $commande->motif_annulation }}</div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-list me-2"></i> Articles ({{ $commande->lignes->count() }})</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Désignation</th><th>Article</th><th class="text-end">Qté</th><th class="text-end">Livrée</th></tr></thead>
                <tbody>
                    @foreach($commande->lignes as $l)
                        <tr>
                            <td><strong>{{ $l->designation }}</strong>
                                @if($l->commentaire)<br><small class="text-muted">{{ $l->commentaire }}</small>@endif
                            </td>
                            <td>@if($l->produit)<small><code>{{ $l->produit->code }}</code></small>@else<em class="text-muted small">libre</em>@endif</td>
                            <td class="text-end">{{ number_format((float) $l->quantite_demandee, 2, ',', ' ') }} {{ $l->unite }}</td>
                            <td class="text-end">{{ number_format((float) $l->quantite_livree, 2, ',', ' ') }}
                                @if($l->est_entierement_livree)<i class="fas fa-check-circle text-success ms-1"></i>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>

        @if($commande->livraisons->isNotEmpty())
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-truck me-2"></i> Livraisons ({{ $commande->livraisons->count() }})</h6></div>
            <div class="card-body">
                @foreach($commande->livraisons as $liv)
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <div><strong>{{ $liv->date_livraison->format('d/m/Y') }}</strong>
                                <span class="badge bg-{{ $liv->type === 'complete' ? 'success' : 'warning text-dark' }} ms-2">{{ ucfirst($liv->type) }}</span>
                            </div>
                            <small class="text-muted">par {{ $liv->livreur?->name }}</small>
                        </div>
                        <ul class="small mb-0 mt-2">
                            @foreach($liv->lignes as $ll)
                                <li>{{ $ll->commandeLigne?->designation }} — {{ number_format((float) $ll->quantite, 2, ',', ' ') }}</li>
                            @endforeach
                        </ul>
                        @if($liv->commentaire)<small class="text-muted d-block mt-1">{{ $liv->commentaire }}</small>@endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Suivi</h6></div>
            <div class="card-body small">
                <p class="mb-1"><strong>Date besoin :</strong> {{ $commande->date_besoin?->format('d/m/Y') ?? '—' }}</p>
                <p class="mb-1"><strong>Soumis N+1 :</strong> {{ $commande->soumise_n1_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mb-1"><strong>Validé N+1 :</strong> {{ $commande->validee_n1_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mb-1"><strong>Transmis Appro :</strong> {{ $commande->transmise_appro_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mb-0"><strong>Livrée :</strong> {{ $commande->livree_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Modales --}}
@if($commande->peutEtreLivree())
<div class="modal fade" id="modal-livrer" tabindex="-1"><div class="modal-dialog modal-lg">
    <form action="{{ route('appro.commandes-internes.livrer', $commande) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Enregistrer une livraison</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="text-muted small">Saisissez les quantités effectivement livrées lors de cette remise. Une livraison partielle est autorisée ; les articles restants pourront être livrés ultérieurement.</p>
            <table class="table table-sm"><thead class="table-light"><tr><th>Article</th><th class="text-end">Reste à livrer</th><th>Emplacement source</th><th class="text-end">À livrer maintenant</th></tr></thead>
                <tbody>
                    @foreach($commande->lignes as $l)
                        <tr>
                            <td>{{ $l->designation }}
                                @if($l->produit)<br><small class="text-muted">Stock : {{ number_format((float) $l->produit->stock_actuel, 2, ',', ' ') }}</small>@endif
                            </td>
                            <td class="text-end">{{ number_format($l->reste_a_livrer, 2, ',', ' ') }}</td>
                            <td>
                                @php
                                    $emplacements = \App\Models\Emplacement::where('actif', true)->orderBy('libelle')->get();
                                    $emplacementsDispo = $l->produit
                                        ? $emplacements->filter(fn($e) => $l->produit->stockDans($e->id) > 0)
                                        : $emplacements;
                                @endphp
                                <select name="emplacements_source[{{ $l->id }}]" class="form-select form-select-sm"
                                    @if($l->produit && $emplacementsDispo->isEmpty()) disabled @endif>
                                    <option value="">— Sélectionner —</option>
                                    @foreach($emplacementsDispo as $e)
                                        <option value="{{ $e->id }}">{{ $e->chemin }}
                                            @if($l->produit)(dispo : {{ number_format($l->produit->stockDans($e->id), 2, ',', ' ') }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @if($l->produit && $emplacementsDispo->isEmpty())
                                    <small class="text-danger d-block"><i class="fas fa-triangle-exclamation me-1"></i>Article en rupture partout — livraison impossible.</small>
                                @endif
                            </td>
                            <td><input type="number" step="0.001" min="0" max="{{ $l->reste_a_livrer }}" name="quantites[{{ $l->id }}]" class="form-control form-control-sm text-end" value="0"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row g-2 mt-2">
                <div class="col-md-6"><label class="form-label small">Reçu par <span class="text-danger">*</span></label>
                    <select name="recu_par" class="form-select form-select-sm" required>
                        @foreach(\App\Models\User::actif()->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" @selected($u->id === $commande->demandeur_id)>
                                {{ trim(($u->name ?? '') . ' ' . ($u->prenoms ?? '')) ?: $u->email }}
                                @if($u->id === $commande->demandeur_id) (demandeur){{--sélection par défaut--}}@endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pré-sélectionné sur le demandeur — modifiable si la remise est faite à un tiers.</small>
                </div>
                <div class="col-md-6"><label class="form-label small">Commentaire</label>
                    <input type="text" name="commentaire" class="form-control form-control-sm"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            <button class="btn btn-success"><i class="fas fa-truck me-1"></i> Confirmer</button>
        </div>
    </form>
</div></div>
@endif

@if($commande->peutEtreValideeN1() && $estN1)
<div class="modal fade" id="modal-refus" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.commandes-internes.refuser-n1', $commande) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Refuser la demande</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
            <textarea name="motif_refus_n1" class="form-control" rows="4" required minlength="5" maxlength="1000"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-warning">Confirmer le refus</button>
        </div>
    </form>
</div></div>
@endif

@can('update:commande')
@if($commande->peutEtreAssignee())
<div class="modal fade" id="modal-assigner-n1" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.commandes-internes.assigner-n1', $commande) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-check me-2"></i> Transmettre à un N+1 pour validation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-info small">
                Cette demande a été soumise sans validation N+1. Vous pouvez la transmettre à un responsable pour validation préalable.
                Le demandeur (<strong>{{ $commande->demandeur?->name }}</strong>) sera notifié.
            </div>
            <div class="mb-3">
                <label class="form-label">Sélectionner le N+1 valideur <span class="text-danger">*</span></label>
                <select name="superieur_id" class="form-select" required>
                    <option value="">— Choisir un utilisateur —</option>
                    @foreach(\App\Models\User::actif()->where('id', '!=', $commande->demandeur_id)->orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}">
                            {{ trim(($u->name ?? '') . ' ' . ($u->prenoms ?? '')) ?: $u->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-0">
                <label class="form-label">Commentaire Appro (facultatif)</label>
                <textarea name="commentaire_appro" class="form-control" rows="2" maxlength="1000" placeholder="Ex : Montant important — validation N+1 requise."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-info text-white"><i class="fas fa-paper-plane me-1"></i> Transmettre</button>
        </div>
    </form>
</div></div>
@endif
@endcan

@if($commande->peutEtreAnnulee())
<div class="modal fade" id="modal-annuler" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.commandes-internes.annuler', $commande) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Annuler la demande</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
            <textarea name="motif_annulation" class="form-control" rows="4" required minlength="5" maxlength="1000"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            <button class="btn btn-warning">Confirmer</button>
        </div>
    </form>
</div></div>
@endif
@endsection
