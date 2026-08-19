<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" required maxlength="50" value="{{ old('code', $source->code ?? '') }}">
            </div>
            <div class="col-md-9">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="label" class="form-control" required maxlength="255" value="{{ old('label', $source->label ?? '') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $source->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.v2.sources.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($source) && $source->exists ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>
