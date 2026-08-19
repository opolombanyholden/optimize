@extends('layouts.app')
@section('title', 'Saisie — ' . $inventaire->numero)
@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="h3 mb-1">Saisie inventaire — <code>{{ $inventaire->numero }}</code></h1>
        <p class="text-muted mb-0">{{ $inventaire->libelle }} · {{ $inventaire->emplacement?->chemin ?? 'Tous emplacements' }}</p></div>
    <a href="{{ route('appro.inventaires.show', $inventaire) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Détail</a>
</div>

<div class="alert alert-info small">
    <i class="fas fa-info-circle me-1"></i>
    Comptez le stock physique en distinguant <strong>bon état</strong> et <strong>mauvais état</strong>.
    Seule la quantité en <strong>bon état</strong> est comparée au stock théorique et alimente le stock livrable
    (les livraisons internes n'utilisent que du bon état).
</div>

<form action="{{ route('appro.inventaires.saisie.save', $inventaire) }}" method="POST">@csrf
<div class="card data-card">
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead class="table-light"><tr>
            <th style="width:260px">Article</th><th>Emplacement</th>
            <th style="width:110px" class="text-end">Théorique</th>
            <th style="width:130px" class="text-end">Bon état <span class="text-success">✓</span></th>
            <th style="width:130px" class="text-end">Mauvais état <span class="text-warning">⚠</span></th>
            <th style="width:110px" class="text-end">Écart</th>
            <th>Commentaire</th>
        </tr></thead>
        <tbody>
            @foreach($inventaire->lignes as $l)
                <tr>
                    <td>
                        <input type="hidden" name="lignes[{{ $loop->index }}][id]" value="{{ $l->id }}">
                        <code class="small">{{ $l->produit?->code }}</code><br>
                        {{ $l->produit?->designation }}
                    </td>
                    <td><small>{{ $l->emplacement?->libelle ?? '—' }}</small></td>
                    <td class="text-end fw-semibold">{{ number_format((float) $l->quantite_theorique, 3, ',', ' ') }}</td>
                    <td>
                        <input type="number" step="0.001" min="0"
                               name="lignes[{{ $loop->index }}][quantite_bon_etat]"
                               value="{{ $l->quantite_bon_etat }}"
                               class="form-control form-control-sm text-end saisie-input"
                               data-theorique="{{ $l->quantite_theorique }}"
                               data-ecart-target="ecart-{{ $l->id }}">
                    </td>
                    <td>
                        <input type="number" step="0.001" min="0"
                               name="lignes[{{ $loop->index }}][quantite_mauvais_etat]"
                               value="{{ $l->quantite_mauvais_etat }}"
                               class="form-control form-control-sm text-end">
                    </td>
                    <td class="text-end" id="ecart-{{ $l->id }}">
                        @if($l->ecart !== null && abs((float) $l->ecart) > 0.0005)
                            <span class="badge bg-{{ $l->ecart_couleur }}">{{ $l->ecart > 0 ? '+' : '' }}{{ number_format((float) $l->ecart, 3, ',', ' ') }}</span>
                        @endif
                    </td>
                    <td><input type="text" name="lignes[{{ $loop->index }}][commentaire]" value="{{ $l->commentaire }}" class="form-control form-control-sm"></td>
                </tr>
            @endforeach
        </tbody>
    </table></div>
    <div class="card-footer">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer la saisie</button>
        <a href="{{ route('appro.inventaires.show', $inventaire) }}" class="btn btn-outline-secondary">Retour</a>
    </div>
</div>
</form>

<script>
document.querySelectorAll('.saisie-input').forEach(input => {
    input.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value);
        const theo = parseFloat(e.target.dataset.theorique);
        const cell = document.getElementById(e.target.dataset.ecartTarget);
        if (isNaN(val)) { cell.textContent = ''; return; }
        const ecart = val - theo;
        cell.textContent = '';
        const badge = document.createElement('span');
        const cls = Math.abs(ecart) < 0.0005 ? 'success' : (ecart > 0 ? 'info' : 'warning text-dark');
        badge.className = 'badge bg-' + cls;
        badge.textContent = (ecart > 0 ? '+' : '') + ecart.toFixed(3);
        cell.appendChild(badge);
    });
});
</script>
@endsection
