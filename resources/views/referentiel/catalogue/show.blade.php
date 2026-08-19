@extends('layouts.app')
@section('title', $article->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.index') }}">Catalogue</a></li>
    <li class="breadcrumb-item active">{{ $article->designation }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            {{ $article->designation }}
            @if($article->code)<code class="small text-muted ms-2">{{ $article->code }}</code>@endif
            <span class="badge bg-{{ $article->type_article === 'service' ? 'info' : 'secondary' }} ms-2">{{ $types[$article->type_article] ?? '—' }}</span>
            @if($article->statut != 1)<span class="badge bg-warning text-dark ms-1">Inactif</span>@endif
        </h1>
        <p class="text-muted mb-0">
            @if($article->famille)Famille : <strong>{{ $article->famille->chemin }}</strong>@endif
            @if($article->emplacementPrincipal) · Emplacement principal : <strong>{{ $article->emplacementPrincipal->chemin }}</strong>@endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('referentiel.catalogue.edit', $article) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
        <a href="{{ route('referentiel.catalogue.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

<div class="row g-3">
    {{-- ═════════ Colonne gauche : identification + description ═════════ --}}
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Identification</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Code interne</dt><dd class="col-sm-8"><code>{{ $article->code ?? '—' }}</code></dd>
                    <dt class="col-sm-4">Désignation</dt><dd class="col-sm-8">{{ $article->designation }}</dd>
                    <dt class="col-sm-4">Type d'article</dt><dd class="col-sm-8">{{ $types[$article->type_article] ?? '—' }}</dd>
                    <dt class="col-sm-4">Famille</dt><dd class="col-sm-8">{{ $article->famille?->chemin ?? '—' }}</dd>
                    <dt class="col-sm-4">Unité de mesure</dt><dd class="col-sm-8">{{ $article->unite_mesure ?? '—' }}</dd>
                    <dt class="col-sm-4">Prix unitaire (XAF)</dt><dd class="col-sm-8">{{ number_format((float) $article->prix_unitaire, 2, ',', ' ') }}</dd>
                    <dt class="col-sm-4">Taux TVA (%)</dt><dd class="col-sm-8">{{ $article->taux_tva ? number_format((float) $article->taux_tva, 2, ',', ' ') : '—' }}</dd>
                    <dt class="col-sm-4">Emplacement principal</dt><dd class="col-sm-8">{{ $article->emplacementPrincipal?->chemin ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        @if($article->description)
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Description</h6></div>
                <div class="card-body"><p class="mb-0">{!! nl2br(e($article->description)) !!}</p></div>
            </div>
        @endif

        @if($article->est_stockable && $article->type_article === 'bien')
            <div class="card data-card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-warehouse me-2"></i> Ventilation par emplacement</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('referentiel.catalogue.ajuster-stock.form', $article) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus-minus me-1"></i> Ajustement
                        </a>
                        @if($article->stocksParEmplacement->where('quantite', '>', 0)->isNotEmpty())
                            <a href="{{ route('referentiel.catalogue.transferer-stock.form', $article) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-shuffle me-1"></i> Transfert
                            </a>
                        @endif
                    </div>
                </div>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr>
                        <th>Emplacement</th><th class="text-end">Quantité</th><th class="text-end">Valeur</th>
                    </tr></thead>
                    <tbody>
                        @forelse($article->stocksParEmplacement as $s)
                            <tr>
                                <td>
                                    <i class="fas fa-warehouse text-secondary me-1"></i>{{ $s->emplacement?->chemin ?? '—' }}
                                    @if($article->emplacement_id === $s->emplacement_id)
                                        <span class="badge bg-primary ms-1" title="Emplacement principal">★</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ number_format((float) $s->quantite, 3, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format((float) $s->quantite * (float) $article->prix_unitaire, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Aucun stock dans un emplacement.</td></tr>
                        @endforelse
                    </tbody>
                    @if($article->stocksParEmplacement->isNotEmpty())
                        <tfoot class="table-light"><tr>
                            <th class="text-end">TOTAL</th>
                            <th class="text-end">{{ number_format((float) $article->stock_actuel, 3, ',', ' ') }}</th>
                            <th class="text-end">{{ number_format($valeurStock, 0, ',', ' ') }}</th>
                        </tr></tfoot>
                    @endif
                </table></div>
            </div>

            <div class="card data-card mb-3">
                <div class="card-header d-flex justify-content-between">
                    <h6 class="mb-0"><i class="fas fa-arrow-right-arrow-left me-2"></i> Derniers mouvements ({{ $mouvements->count() }})</h6>
                    <a href="{{ route('appro.stock.mouvements', ['produit' => $article->id]) }}" class="small">Journal complet →</a>
                </div>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr>
                        <th>Date</th><th>Type</th><th class="text-end">Qté</th><th>Emplacement</th><th>Réf.</th><th>Par</th>
                    </tr></thead>
                    <tbody>
                        @forelse($mouvements as $m)
                            <tr>
                                <td><small>{{ $m->created_at?->format('d/m/Y H:i') }}</small></td>
                                <td><span class="badge bg-{{ $m->type_couleur }}">{{ \App\Models\ProduitMouvement::TYPES[$m->type] ?? $m->type }}</span></td>
                                <td class="text-end fw-semibold">{{ $m->type === 'sortie' ? '-' : '+' }}{{ number_format((float) $m->quantite, 2, ',', ' ') }}</td>
                                <td><small>
                                    @if($m->type === 'transfert'){{ $m->emplacementSource?->libelle }} → {{ $m->emplacement?->libelle }}
                                    @elseif($m->type === 'sortie'){{ $m->emplacementSource?->libelle ?? '—' }}
                                    @else{{ $m->emplacement?->libelle ?? '—' }}
                                    @endif
                                </small></td>
                                <td><small>{{ $m->reference ?? '—' }}</small></td>
                                <td><small>{{ $m->user?->name ?? '—' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Aucun mouvement.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>

            @if($inventairesLignes->isNotEmpty())
                <div class="card data-card mb-3">
                    <div class="card-header"><h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Inventaires ({{ $inventairesLignes->count() }})</h6></div>
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Inventaire</th><th>Date</th><th>Statut</th><th class="text-end">Théorique</th><th class="text-end">Compté</th><th class="text-end">Écart</th></tr></thead>
                        <tbody>
                            @foreach($inventairesLignes as $l)
                                <tr>
                                    <td><code class="small">{{ $l->inventaire?->numero }}</code></td>
                                    <td><small>{{ $l->inventaire?->date_prevue?->format('d/m/Y') }}</small></td>
                                    <td><span class="badge bg-{{ $l->inventaire?->statut_couleur }}">{{ $l->inventaire?->statut_libelle }}</span></td>
                                    <td class="text-end">{{ number_format((float) $l->quantite_theorique, 3, ',', ' ') }}</td>
                                    <td class="text-end">{{ $l->quantite_reelle !== null ? number_format((float) $l->quantite_reelle, 3, ',', ' ') : '—' }}</td>
                                    <td class="text-end">
                                        @if($l->ecart !== null && abs((float) $l->ecart) > 0.0005)
                                            <span class="badge bg-{{ $l->ecart_couleur }}">{{ $l->ecart > 0 ? '+' : '' }}{{ number_format((float) $l->ecart, 3, ',', ' ') }}</span>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                </div>
            @endif
        @endif
    </div>

    {{-- ═════════ Colonne droite : stock summary + méta ═════════ --}}
    <div class="col-md-4">
        @if($article->est_stockable && $article->type_article === 'bien')
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-boxes-stacked me-2"></i> Stock</h6></div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h2 mb-0 text-{{ $article->etat_stock_couleur }}">{{ number_format((float) $article->stock_actuel, 3, ',', ' ') }}</div>
                        <small class="text-muted">{{ $article->unite_mesure ?? '' }}</small>
                        <div class="mt-2"><span class="badge bg-{{ $article->etat_stock_couleur }} text-uppercase">{{ $article->etat_stock }}</span></div>
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-6">Valeur (XAF)</dt><dd class="col-6 text-end">{{ number_format($valeurStock, 0, ',', ' ') }}</dd>
                        <dt class="col-6">Emplacements</dt><dd class="col-6 text-end">{{ $article->stocksParEmplacement->count() }}</dd>
                        <dt class="col-6">Seuil alerte</dt><dd class="col-6 text-end">{{ $article->seuil_alerte !== null ? number_format((float) $article->seuil_alerte, 2, ',', ' ') : '—' }}</dd>
                        <dt class="col-6">Stock min</dt><dd class="col-6 text-end">{{ $article->stock_minimum !== null ? number_format((float) $article->stock_minimum, 2, ',', ' ') : '—' }}</dd>
                        <dt class="col-6">Stock max</dt><dd class="col-6 text-end">{{ $article->stock_maximum !== null ? number_format((float) $article->stock_maximum, 2, ',', ' ') : '—' }}</dd>
                    </dl>
                </div>
            </div>
        @elseif($article->type_article === 'service')
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-hand-holding me-2"></i> Service</h6></div>
                <div class="card-body text-center text-muted small">Prestation immatérielle — pas de gestion de stock.</div>
            </div>
        @endif

        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-clock me-2"></i> Traçabilité</h6></div>
            <div class="card-body small">
                <p class="mb-1"><strong>Créé :</strong> {{ $article->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mb-0"><strong>Dernière MAJ :</strong> {{ $article->updated_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
