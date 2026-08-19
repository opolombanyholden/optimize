@extends('layouts.app')
@section('title', 'Nouvelle livraison fournisseur')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.livraisons-fournisseur.index') }}">Livraisons fournisseur</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1"><i class="fas fa-truck-loading text-muted me-2"></i> Enregistrer une livraison fournisseur</h1>
    <p class="text-muted mb-0">Saisir la réception physique d'une commande — totale ou partielle.</p>
</div>

@if(!$commande)
    {{-- Étape 1 : choix de la commande --}}
    <div class="card data-card">
        <div class="card-header"><h6 class="mb-0">Sélectionner la commande fournisseur</h6></div>
        <div class="card-body">
            @if($commandes->isEmpty())
                <p class="text-center text-muted py-3">Aucune commande approuvée en attente de livraison.</p>
            @else
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>N° commande</th><th>Fournisseur</th><th>Date</th><th>Statut</th><th class="text-end">Actions</th>
                    </tr></thead>
                    <tbody>
                        @foreach($commandes as $c)
                            <tr>
                                <td><code>{{ $c->numero_commande }}</code></td>
                                <td>{{ $c->fournisseur?->raison_sociale ?? $c->fournisseur?->nom ?? '—' }}</td>
                                <td><small>{{ $c->date_commande?->format('d/m/Y') }}</small></td>
                                <td><span class="badge bg-{{ $c->statut_couleur }}">{{ $c->statut_libelle }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('appro.livraisons-fournisseur.create', ['commande_id' => $c->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-arrow-right me-1"></i> Choisir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
        </div>
    </div>
@else
    {{-- Étape 2 : saisie de la livraison --}}
    <form action="{{ route('appro.livraisons-fournisseur.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="commande_fournisseur_id" value="{{ $commande->id }}">

        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Commande <code>{{ $commande->numero_commande }}</code></h6></div>
            <div class="card-body"><dl class="row mb-0 small">
                <dt class="col-sm-3">Fournisseur</dt><dd class="col-sm-9">{{ $commande->fournisseur?->raison_sociale ?? $commande->fournisseur?->nom }}</dd>
                <dt class="col-sm-3">Date commande</dt><dd class="col-sm-9">{{ $commande->date_commande?->format('d/m/Y') }}</dd>
                <dt class="col-sm-3">Statut</dt><dd class="col-sm-9"><span class="badge bg-{{ $commande->statut_couleur }}">{{ $commande->statut_libelle }}</span></dd>
            </dl></div>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Informations de la livraison</h6></div>
            <div class="card-body"><div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date de livraison <span class="text-danger">*</span></label>
                    <input type="date" name="date_livraison" class="form-control" value="{{ old('date_livraison', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Emplacement de réception</label>
                    <select name="emplacement_reception_id" class="form-select" id="emplacement-global">
                        <option value="">—</option>
                        @foreach($emplacements as $e)
                            <option value="{{ $e->id }}" @selected(old('emplacement_reception_id') == $e->id)>{{ $e->chemin }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Emplacement par défaut ; peut être surchargé par ligne.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">N° bon de livraison fournisseur</label>
                    <input type="text" name="bon_livraison_ref" class="form-control" value="{{ old('bon_livraison_ref') }}" maxlength="100" placeholder="BL-2027-…">
                </div>
                <div class="col-12">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="2" maxlength="2000">{{ old('commentaire') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Pièces jointes (BL scanné, photos, PV…)</label>
                    <input type="file" name="pieces_jointes[]" class="form-control" multiple>
                </div>
            </div></div>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-boxes-stacked me-2"></i> Articles à réceptionner</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr>
                    <th>Article</th>
                    <th class="text-end">Commandé</th>
                    <th class="text-end">Déjà livré</th>
                    <th class="text-end">Reste</th>
                    <th style="width:160px">À livrer maintenant</th>
                    <th style="width:200px">Emplacement (override)</th>
                    <th>Observation</th>
                </tr></thead>
                <tbody>
                    @foreach($commande->lignes as $i => $l)
                        @php $reste = (float) $l->quantite_commandee - (float) $l->quantite_livree; @endphp
                        <tr class="{{ $reste <= 0 ? 'table-secondary' : '' }}">
                            <td>
                                <strong>{{ $l->designation }}</strong>
                                @if($l->produit)<br><code class="small text-muted">{{ $l->produit->code }}</code>@endif
                                <input type="hidden" name="lignes[{{ $i }}][commande_ligne_id]" value="{{ $l->id }}">
                            </td>
                            <td class="text-end">{{ number_format((float) $l->quantite_commandee, 3, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format((float) $l->quantite_livree, 3, ',', ' ') }}</td>
                            <td class="text-end fw-semibold">{{ number_format($reste, 3, ',', ' ') }}</td>
                            <td>
                                <input type="number" step="0.001" min="0" max="{{ $reste }}"
                                       name="lignes[{{ $i }}][quantite]"
                                       class="form-control form-control-sm text-end"
                                       value="{{ $reste > 0 ? $reste : 0 }}"
                                       @if($reste <= 0) readonly @endif>
                            </td>
                            <td>
                                <select name="lignes[{{ $i }}][emplacement_id]" class="form-select form-select-sm">
                                    <option value="">— (défaut)</option>
                                    @foreach($emplacements as $e)
                                        <option value="{{ $e->id }}">{{ $e->chemin }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="lignes[{{ $i }}][observation]" class="form-control form-control-sm" maxlength="500"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>

        <div class="alert alert-info small mb-3">
            <i class="fas fa-info-circle me-1"></i> Le type <strong>Totale</strong> ou <strong>Partielle</strong> est déterminé automatiquement après enregistrement selon les quantités reçues. Les stocks des articles rattachés au catalogue seront mis à jour.
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Enregistrer la livraison</button>
            <a href="{{ route('appro.commandes.show', $commande) }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
@endif
@endsection
