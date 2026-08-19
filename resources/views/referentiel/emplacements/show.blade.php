@extends('layouts.app')
@section('title', $emplacement->libelle)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.emplacements.index') }}">Emplacements</a></li>
    <li class="breadcrumb-item active">{{ $emplacement->libelle }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-{{ ['magasin' => 'warehouse', 'zone' => 'th-large', 'etagere' => 'grip-lines', 'case' => 'square'][$emplacement->type] ?? 'folder-open' }} text-muted me-2"></i>
            {{ $emplacement->libelle }}
            <code class="small text-muted ms-2">{{ $emplacement->code }}</code>
            @if($emplacement->type)<span class="badge bg-light text-dark ms-2">{{ \App\Models\Emplacement::TYPES[$emplacement->type] }}</span>@endif
            @unless($emplacement->actif)<span class="badge bg-secondary ms-1">Inactif</span>@endunless
        </h1>
        <p class="text-muted mb-0">
            <strong>Chemin :</strong> {{ $emplacement->chemin }}
            @if($emplacement->responsable) · <strong>Responsable :</strong> {{ $emplacement->responsable->name }}@endif
        </p>
    </div>
    <a href="{{ route('referentiel.emplacements.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card data-card p-3">
        <small class="text-muted">Articles stockés</small>
        <h4 class="mb-0">{{ $emplacement->stocks->where('quantite', '>', 0)->count() }}</h4>
        <small class="text-muted">Total lignes : {{ $emplacement->stocks->count() }}</small>
    </div></div>
    <div class="col-md-3"><div class="card data-card p-3">
        <small class="text-muted">Valeur du stock (XAF)</small>
        <h4 class="mb-0">{{ number_format($valeurTotale, 0, ',', ' ') }}</h4>
    </div></div>
    <div class="col-md-3"><div class="card data-card p-3">
        <small class="text-muted">Sous-emplacements</small>
        <h4 class="mb-0">{{ $emplacement->enfants->count() }}</h4>
    </div></div>
    <div class="col-md-3"><div class="card data-card p-3">
        <small class="text-muted">État</small>
        <h4 class="mb-0"><span class="badge bg-{{ $emplacement->actif ? 'success' : 'secondary' }} text-uppercase">{{ $emplacement->actif ? 'Actif' : 'Inactif' }}</span></h4>
    </div></div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-boxes-stacked me-2"></i> Articles stockés ici</h6></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr>
                    <th>Article</th><th>Famille</th><th class="text-end">Quantité</th><th class="text-end">Valeur</th>
                </tr></thead>
                <tbody>
                    @forelse($emplacement->stocks->sortByDesc('quantite') as $s)
                        <tr>
                            <td>
                                @if($s->produit)
                                    <a href="{{ route('referentiel.catalogue.show', $s->produit) }}">
                                        <code class="small">{{ $s->produit->code }}</code>
                                        <strong class="ms-1">{{ $s->produit->designation }}</strong>
                                    </a>
                                @else
                                    <em class="text-muted">Article supprimé</em>
                                @endif
                            </td>
                            <td><small>{{ $s->produit?->famille?->libelle ?? '—' }}</small></td>
                            <td class="text-end fw-semibold">{{ number_format((float) $s->quantite, 3, ',', ' ') }}
                                <small class="text-muted">{{ $s->produit?->unite_mesure }}</small>
                            </td>
                            <td class="text-end">{{ number_format((float) $s->quantite * (float) ($s->produit?->prix_unitaire ?? 0), 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucun article stocké dans cet emplacement.</td></tr>
                    @endforelse
                </tbody>
                @if($emplacement->stocks->isNotEmpty())
                    <tfoot class="table-light"><tr>
                        <th colspan="3" class="text-end">TOTAL</th>
                        <th class="text-end">{{ number_format($valeurTotale, 0, ',', ' ') }}</th>
                    </tr></tfoot>
                @endif
            </table></div>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h6 class="mb-0"><i class="fas fa-arrow-right-arrow-left me-2"></i> Derniers mouvements ({{ $mouvements->count() }})</h6>
                <a href="{{ route('appro.stock.mouvements', ['emplacement' => $emplacement->id]) }}" class="small">Journal complet →</a>
            </div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="table-light"><tr>
                    <th>Date</th><th>Type</th><th>Article</th><th class="text-end">Qté</th><th>Réf.</th><th>Par</th>
                </tr></thead>
                <tbody>
                    @forelse($mouvements as $m)
                        <tr>
                            <td><small>{{ $m->created_at?->format('d/m/Y H:i') }}</small></td>
                            <td>
                                <span class="badge bg-{{ $m->type_couleur }}">{{ \App\Models\ProduitMouvement::TYPES[$m->type] ?? $m->type }}</span>
                                @if($m->emplacement_source_id === $emplacement->id && $m->type === 'transfert')
                                    <small class="text-muted d-block">sortie</small>
                                @elseif($m->emplacement_id === $emplacement->id && $m->type === 'transfert')
                                    <small class="text-muted d-block">entrée</small>
                                @endif
                            </td>
                            <td><small>{{ $m->produit?->designation ?? '—' }}</small></td>
                            <td class="text-end fw-semibold">
                                @php
                                    $signe = '+';
                                    if ($m->type === 'sortie' || ($m->type === 'transfert' && $m->emplacement_source_id === $emplacement->id)) $signe = '-';
                                @endphp
                                {{ $signe }}{{ number_format((float) $m->quantite, 3, ',', ' ') }}
                            </td>
                            <td><small>{{ $m->reference ?? '—' }}</small></td>
                            <td><small>{{ $m->user?->name ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Aucun mouvement sur cet emplacement.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>

        @if($emplacement->enfants->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-sitemap me-2"></i> Sous-emplacements ({{ $emplacement->enfants->count() }})</h6></div>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Code</th><th>Libellé</th><th>Type</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @foreach($emplacement->enfants as $e)
                            <tr>
                                <td><code class="small">{{ $e->code }}</code></td>
                                <td>{{ $e->libelle }}</td>
                                <td><small>{{ \App\Models\Emplacement::TYPES[$e->type] ?? '—' }}</small></td>
                                <td class="text-end"><a href="{{ route('referentiel.emplacements.show', $e) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">Code</dt><dd class="col-sm-7"><code>{{ $emplacement->code }}</code></dd>
                    <dt class="col-sm-5">Type</dt><dd class="col-sm-7">{{ \App\Models\Emplacement::TYPES[$emplacement->type] ?? '—' }}</dd>
                    <dt class="col-sm-5">Parent</dt><dd class="col-sm-7">{{ $emplacement->parent?->libelle ?? '— Racine' }}</dd>
                    <dt class="col-sm-5">Responsable</dt><dd class="col-sm-7">{{ $emplacement->responsable?->name ?? '—' }}</dd>
                    <dt class="col-sm-5">État</dt><dd class="col-sm-7">{{ $emplacement->actif ? 'Actif' : 'Inactif' }}</dd>
                    <dt class="col-sm-5">Créé le</dt><dd class="col-sm-7">{{ $emplacement->created_at?->format('d/m/Y') }}</dd>
                </dl>
                @if($emplacement->adresse)
                    <hr><small class="text-muted">Adresse</small><p class="mb-0">{{ $emplacement->adresse }}</p>
                @endif
                @if($emplacement->description)
                    <hr><small class="text-muted">Description</small><p class="mb-0">{{ $emplacement->description }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
