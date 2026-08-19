@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $article->code) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Désignation <span class="text-danger">*</span></label>
        <input type="text" name="designation" class="form-control" value="{{ old('designation', $article->designation) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type_article" class="form-select" required>
            @foreach($types as $k => $v)<option value="{{ $k }}" @selected(old('type_article', $article->type_article) === $k)>{{ $v }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Famille</label>
        <select name="famille_id" class="form-select">
            <option value="">—</option>
            @foreach($familles as $f)<option value="{{ $f->id }}" @selected(old('famille_id', $article->famille_id) == $f->id)>{{ $f->chemin }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Unité de mesure</label>
        <input type="text" name="unite_mesure" class="form-control" value="{{ old('unite_mesure', $article->unite_mesure) }}" placeholder="pièce, kg, litre…">
    </div>
    <div class="col-md-3">
        <label class="form-label">Prix unitaire (XAF)</label>
        <input type="number" step="0.01" min="0" name="prix_unitaire" class="form-control text-end" value="{{ old('prix_unitaire', $article->prix_unitaire) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $article->description) }}</textarea>
    </div>
    <div class="col-md-3">
        <div class="form-check mt-4">
            <input type="hidden" name="est_stockable" value="0">
            <input type="checkbox" class="form-check-input" name="est_stockable" value="1" id="est_stockable" @checked(old('est_stockable', $article->est_stockable))>
            <label for="est_stockable" class="form-check-label">Article stockable (biens uniquement)</label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Stock actuel <small class="text-muted">(calculé)</small></label>
        <input type="text" class="form-control text-end bg-light" readonly disabled
               value="{{ number_format((float) $article->stock_actuel, 3, ',', ' ') }}{{ $article->unite_mesure ? ' ' . $article->unite_mesure : '' }}">
        <small class="text-muted">
            Somme des quantités par emplacement.
            @if($article->exists && $article->est_stockable)
                Modifiable via
                <a href="{{ route('referentiel.catalogue.ajuster-stock.form', $article) }}">Ajustement</a>
                ou <a href="{{ route('referentiel.catalogue.transferer-stock.form', $article) }}">Transfert</a>
                (formulaires dédiés).
            @else
                Alimenté par les livraisons fournisseur et ajustements.
            @endif
        </small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Seuil d'alerte</label>
        <input type="number" step="0.001" min="0" name="seuil_alerte" class="form-control text-end" value="{{ old('seuil_alerte', $article->seuil_alerte) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Stock max</label>
        <input type="number" step="0.001" min="0" name="stock_maximum" class="form-control text-end" value="{{ old('stock_maximum', $article->stock_maximum) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Emplacement principal</label>
        <select name="emplacement_id" class="form-select">
            <option value="">— Aucun</option>
            @foreach($emplacements as $e)
                <option value="{{ $e->id }}" @selected(old('emplacement_id', $article->emplacement_id) == $e->id)>{{ $e->chemin }}</option>
            @endforeach
        </select>
        <small class="text-muted">
            Défini dans <a href="{{ route('referentiel.emplacements.index') }}" target="_blank">Référentiel › Emplacements</a>.
            Le stock détaillé multi-emplacements est géré dans <a href="{{ route('appro.stock.emplacements') }}" target="_blank">Stock par emplacement</a>.
        </small>
    </div>
</div></div></div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('referentiel.catalogue.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
