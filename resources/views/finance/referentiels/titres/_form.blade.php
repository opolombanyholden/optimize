@php $isEdit = isset($titre) && $titre->exists; @endphp

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Imputation <span class="text-danger">*</span></label>
                <input type="text" name="imputation" class="form-control @error('imputation') is-invalid @enderror"
                       value="{{ old('imputation', $titre->imputation ?? '') }}" required maxlength="50">
                @error('imputation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                       value="{{ old('libelle', $titre->libelle ?? '') }}" required maxlength="255">
                @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type_ligne" class="form-select">
                    <option value="">—</option>
                    <option value="depense" @selected(old('type_ligne', $titre->type_ligne ?? '') === 'depense')>Dépense</option>
                    <option value="recette" @selected(old('type_ligne', $titre->type_ligne ?? '') === 'recette')>Recette</option>
                    <option value="mixte" @selected(old('type_ligne', $titre->type_ligne ?? '') === 'mixte')>Mixte</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Seuil (XAF)</label>
                <input type="number" name="seuil" step="0.01" min="0" class="form-control"
                       value="{{ old('seuil', $titre->seuil ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $titre->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.referentiels.titres.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>
