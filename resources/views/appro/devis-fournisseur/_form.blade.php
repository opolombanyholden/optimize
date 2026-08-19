@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    @if(!$devis->exists)
        <div class="col-md-6">
            <label class="form-label">Commande fournisseur <span class="text-danger">*</span></label>
            <select name="commande_fournisseur_id" class="form-select" required>
                <option value="">— Sélectionner</option>
                @foreach($commandes ?? [] as $c)
                    <option value="{{ $c->id }}" @selected(old('commande_fournisseur_id', $devis->commande_fournisseur_id ?? $commande?->id) == $c->id)>
                        {{ $c->numero_commande }} — {{ $c->fournisseur?->raison_sociale ?? $c->fournisseur?->nom }}
                    </option>
                @endforeach
                @if($commande && $commandes?->isEmpty())
                    <option value="{{ $commande->id }}" selected>{{ $commande->numero_commande }} — {{ $commande->fournisseur?->raison_sociale ?? $commande->fournisseur?->nom }}</option>
                @endif
            </select>
        </div>
    @else
        <input type="hidden" name="commande_fournisseur_id" value="{{ $devis->commande_fournisseur_id }}">
    @endif
    <div class="col-md-6">
        <label class="form-label">Fournisseur émetteur</label>
        <select name="fournisseur_id" class="form-select">
            <option value="">— Sélectionner</option>
            @foreach($fournisseurs as $f)
                <option value="{{ $f->id }}" @selected(old('fournisseur_id', $devis->fournisseur_id) == $f->id)>{{ $f->raison_sociale ?: $f->nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date de réception <span class="text-danger">*</span></label>
        <input type="date" name="date_reception" class="form-control" value="{{ old('date_reception', optional($devis->date_reception)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Valide jusqu'au</label>
        <input type="date" name="date_validite" class="form-control" value="{{ old('date_validite', optional($devis->date_validite)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Délai de livraison (jours)</label>
        <input type="number" name="delai_livraison_jours" class="form-control text-end" value="{{ old('delai_livraison_jours', $devis->delai_livraison_jours) }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label">Mode de règlement</label>
        <select name="mode_reglement" class="form-select">
            <option value="">—</option>
            @foreach(\App\Models\CommandeFournisseur::MODES_REGLEMENT as $k => $v)
                <option value="{{ $k }}" @selected(old('mode_reglement', $devis->mode_reglement) === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div></div></div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-coins me-2"></i> Montants du devis</h6></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Montant HT (XAF) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="montant_ht" id="devis-ht" class="form-control text-end"
                   value="{{ old('montant_ht', $devis->montant_ht) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Montant TTC (XAF) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="montant_ttc" id="devis-ttc" class="form-control text-end"
                   value="{{ old('montant_ttc', $devis->montant_ttc) }}" required>
            <small class="text-muted">Doit être ≥ HT.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label">TVA (calculée)</label>
            <input type="text" id="devis-tva" class="form-control text-end bg-light" readonly disabled
                   value="{{ number_format(max((float) ($devis->montant_ttc ?? 0) - (float) ($devis->montant_ht ?? 0), 0), 2, ',', ' ') }}">
        </div>
    </div></div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>
        Devis PDF <span class="text-danger">*</span>
        @if($devis->exists)
            <small class="text-muted">(déjà {{ $devis->piecesJointes->count() }} pièce(s) — ajout facultatif ci-dessous)</small>
        @endif
    </h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple
               @unless($devis->exists) required @endunless>
        <small class="text-muted">
            Formats acceptés : PDF, images (JPG/PNG), Word, Excel — max 20 Mo par fichier.
            Le devis PDF fourni par le fournisseur doit obligatoirement être joint.
        </small>
        @if($devis->exists && $devis->piecesJointes->isNotEmpty())
            <hr>@include('mg._partials.pieces-jointes', ['pieces' => $devis->piecesJointes])
        @endif
    </div>
</div>

<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-12">
        <label class="form-label">Conditions particulières</label>
        <textarea name="conditions" class="form-control" rows="2">{{ old('conditions', $devis->conditions) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Commentaire</label>
        <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire', $devis->commentaire) }}</textarea>
    </div>
</div></div></div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ $devis->exists ? route('appro.devis-fournisseur.show', $devis) : route('appro.devis-fournisseur.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ht  = document.getElementById('devis-ht');
    const ttc = document.getElementById('devis-ttc');
    const tva = document.getElementById('devis-tva');
    const recalc = () => {
        const h = parseFloat(ht.value) || 0;
        const t = parseFloat(ttc.value) || 0;
        tva.value = Math.max(t - h, 0).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
    ht.addEventListener('input', recalc);
    ttc.addEventListener('input', recalc);
});
</script>
