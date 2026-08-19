@extends('layouts.app')
@section('title', 'Nouvelle évaluation prestataire')
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Nouvelle évaluation prestataire</h1></div>

<form action="{{ route('appro.evaluations.store') }}" method="POST">@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Prestataire <span class="text-danger">*</span></label>
        <select name="prestataire_id" class="form-select" required>
            <option value="">—</option>
            @foreach($prestataires as $p)
                <option value="{{ $p->id }}" @selected(($commande?->tiers_id ?? old('prestataire_id')) == $p->id)>{{ $p->raison_sociale ?: $p->nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Source <span class="text-danger">*</span></label>
        <select name="source" class="form-select" required>
            <option value="ad_hoc" @selected(old('source') === 'ad_hoc' || (!$commande && !$campagne))>Ad hoc</option>
            <option value="livraison" @selected(old('source') === 'livraison' || $commande)>Post-livraison</option>
            <option value="campagne" @selected(old('source') === 'campagne' || $campagne)>Campagne</option>
        </select>
    </div>
    @if($commande)
        <div class="col-md-6"><input type="hidden" name="commande_fournisseur_id" value="{{ $commande->id }}">
            <small class="text-muted">Rattachée à la commande : <code>{{ $commande->numero_commande }}</code></small></div>
    @endif
    @if($campagne)
        <div class="col-md-6"><input type="hidden" name="campagne_id" value="{{ $campagne->id }}">
            <small class="text-muted">Dans le cadre de : <strong>{{ $campagne->libelle }}</strong></small></div>
    @endif
    <div class="col-12"><label class="form-label">Commentaire général</label>
        <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire') }}</textarea></div>
</div></div></div>

@foreach($themes as $theme)
    @if($theme->criteres->isNotEmpty())
    <div class="card data-card mb-3">
        <div class="card-header"><h6 class="mb-0">{{ $theme->libelle }}</h6></div>
        <div class="card-body"><table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Critère</th><th style="width:180px">Note</th><th style="width:100px" class="text-center">Poids</th><th>Commentaire</th></tr></thead>
            <tbody>
                @foreach($theme->criteres as $c)
                    <tr>
                        <td><strong>{{ $c->libelle }}</strong>@if($c->description)<br><small class="text-muted">{{ $c->description }}</small>@endif</td>
                        <td><input type="hidden" name="notes[{{ $c->id }}][critere_id]" value="{{ $c->id }}">
                            <input type="number" step="0.1" min="{{ $c->echelle_min }}" max="{{ $c->echelle_max }}" name="notes[{{ $c->id }}][note]" class="form-control form-control-sm" required placeholder="{{ $c->echelle_min }}–{{ $c->echelle_max }}"></td>
                        <td class="text-center"><small>{{ $c->poids }}</small></td>
                        <td><input type="text" name="notes[{{ $c->id }}][commentaire]" class="form-control form-control-sm"></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
    @endif
@endforeach

@if($criteresIndep->isNotEmpty())
    <div class="card data-card mb-3">
        <div class="card-header"><h6 class="mb-0">Autres critères</h6></div>
        <div class="card-body"><table class="table table-sm mb-0">
            <tbody>
                @foreach($criteresIndep as $c)
                    <tr>
                        <td><strong>{{ $c->libelle }}</strong></td>
                        <td style="width:180px"><input type="hidden" name="notes[{{ $c->id }}][critere_id]" value="{{ $c->id }}">
                            <input type="number" step="0.1" min="{{ $c->echelle_min }}" max="{{ $c->echelle_max }}" name="notes[{{ $c->id }}][note]" class="form-control form-control-sm" required></td>
                        <td><input type="text" name="notes[{{ $c->id }}][commentaire]" class="form-control form-control-sm"></td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
@endif

<button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer l'évaluation</button>
<a href="{{ route('appro.evaluations.index') }}" class="btn btn-outline-secondary">Annuler</a>
</form>
@endsection
