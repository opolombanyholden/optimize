@php $isEdit = isset($ligne) && $ligne->exists; @endphp

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Titre (famille) <span class="text-danger">*</span></label>
                <select name="id_titre" class="form-select @error('id_titre') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($titres as $t)
                        <option value="{{ $t->id }}" @selected(old('id_titre', $ligne->id_titre ?? '') == $t->id)>{{ $t->imputation }} — {{ $t->libelle }}</option>
                    @endforeach
                </select>
                @error('id_titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Code analytique <span class="text-danger">*</span></label>
                <input type="text" name="id_codeanalytique" class="form-control" required maxlength="50"
                       value="{{ old('id_codeanalytique', $ligne->id_codeanalytique ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Famille code analytique</label>
                <input type="text" name="id_famillecodeanalytique" class="form-control" maxlength="50"
                       value="{{ old('id_famillecodeanalytique', $ligne->id_famillecodeanalytique ?? '') }}">
            </div>

            <div class="col-12">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" class="form-control" required maxlength="255"
                       value="{{ old('libelle', $ligne->libelle ?? '') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Nature</label>
                <input type="text" name="nature" class="form-control" maxlength="50"
                       value="{{ old('nature', $ligne->nature ?? '') }}" placeholder="Fonctionnement, investissement…">
            </div>
            <div class="col-md-4">
                <label class="form-label">Seuil (XAF)</label>
                <input type="number" name="seuil" step="0.01" min="0" class="form-control"
                       value="{{ old('seuil', $ligne->seuil ?? '') }}">
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $ligne->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.referentiels.lignes.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>
