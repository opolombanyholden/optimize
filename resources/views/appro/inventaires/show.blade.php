@extends('layouts.app')
@section('title', $inventaire->numero)
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1"><code>{{ $inventaire->numero }}</code>
            <span class="badge bg-{{ $inventaire->statut_couleur }} ms-2">{{ $inventaire->statut_libelle }}</span></h1>
        <p class="text-muted mb-0">
            <strong>{{ $inventaire->libelle }}</strong>
            · <strong>Prévu :</strong> {{ $inventaire->date_prevue?->format('d/m/Y') }}
            · <strong>Périmètre :</strong> {{ $inventaire->emplacement?->chemin ?? 'Tous emplacements' }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($inventaire->peutEtreModifie())
            <a href="{{ route('appro.inventaires.edit', $inventaire) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            @if($inventaire->peutEtreLance())
                <form action="{{ route('appro.inventaires.lancer', $inventaire) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-info text-white"><i class="fas fa-play me-1"></i> Lancer l'inventaire</button></form>
            @endif
        @endif
        @if($inventaire->statut === \App\Models\Inventaire::STATUT_EN_COURS)
            <a href="{{ route('appro.inventaires.saisie', $inventaire) }}" class="btn btn-primary"><i class="fas fa-pen-to-square me-1"></i> Saisir les comptages</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-cloturer"><i class="fas fa-lock me-1"></i> Clôturer</button>
        @endif
        @if($inventaire->statut !== \App\Models\Inventaire::STATUT_CLOTURE)
            <form action="{{ route('appro.inventaires.annuler', $inventaire) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler cet inventaire ?');">@csrf
                <button class="btn btn-outline-warning"><i class="fas fa-ban me-1"></i> Annuler</button></form>
        @endif
    </div>
</div>

@php
    $total = $inventaire->lignes->count();
    $comptees = $inventaire->lignes->whereNotNull('quantite_reelle')->count();
    $avecEcart = $inventaire->lignes->filter(fn($l) => $l->ecart !== null && abs((float) $l->ecart) > 0.0005)->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Lignes</small><h4 class="mb-0">{{ $total }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Comptées</small><h4 class="mb-0 text-info">{{ $comptees }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Avec écart</small><h4 class="mb-0 text-warning">{{ $avecEcart }}</h4></div></div>
    <div class="col-md-3"><div class="card data-card p-3"><small class="text-muted">Progression</small>
        <h4 class="mb-0">{{ $total ? round($comptees * 100 / $total) : 0 }}%</h4></div></div>
</div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr>
            <th>Article</th><th>Emplacement</th>
            <th class="text-end">Théorique</th>
            <th class="text-end">Bon état</th>
            <th class="text-end">Mauvais état</th>
            <th class="text-end">Écart</th><th>Compté par</th><th>Commentaire</th>
        </tr></thead>
        <tbody>
            @forelse($inventaire->lignes as $l)
                <tr>
                    <td><code class="small">{{ $l->produit?->code }}</code> {{ $l->produit?->designation }}</td>
                    <td><small>{{ $l->emplacement?->libelle ?? '—' }}</small></td>
                    <td class="text-end">{{ number_format((float) $l->quantite_theorique, 3, ',', ' ') }}</td>
                    <td class="text-end text-success fw-semibold">{{ $l->quantite_bon_etat !== null ? number_format((float) $l->quantite_bon_etat, 3, ',', ' ') : '—' }}</td>
                    <td class="text-end text-warning">{{ $l->quantite_mauvais_etat !== null ? number_format((float) $l->quantite_mauvais_etat, 3, ',', ' ') : '—' }}</td>
                    <td class="text-end">
                        @if($l->ecart !== null)
                            <span class="badge bg-{{ $l->ecart_couleur }}">{{ $l->ecart > 0 ? '+' : '' }}{{ number_format((float) $l->ecart, 3, ',', ' ') }}</span>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td><small>{{ $l->compteur?->name ?? '—' }}</small></td>
                    <td><small>{{ $l->commentaire ?: '' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Aucune ligne.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@if($inventaire->statut === \App\Models\Inventaire::STATUT_EN_COURS)
<div class="modal fade" id="modal-cloturer" tabindex="-1"><div class="modal-dialog">
    <form action="{{ route('appro.inventaires.cloturer', $inventaire) }}" method="POST" class="modal-content">@csrf
        <div class="modal-header"><h5 class="modal-title">Clôturer l'inventaire</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p>{{ $comptees }}/{{ $total }} lignes comptées.</p>
            <p>{{ $avecEcart }} ligne(s) présentent un écart. La clôture générera automatiquement des mouvements d'ajustement pour aligner le stock théorique sur le stock réel.</p>
            <div class="alert alert-warning small mb-0">
                <strong>Les lignes non comptées ne seront pas ajustées.</strong> Le stock théorique restera inchangé pour ces articles.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-success"><i class="fas fa-check me-1"></i> Confirmer la clôture</button>
        </div>
    </form>
</div></div>
@endif
@endsection
