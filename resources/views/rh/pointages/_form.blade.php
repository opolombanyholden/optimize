@php $isEdit = isset($pointage) && $pointage->exists; @endphp

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Employé <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required {{ $isEdit ? 'disabled' : '' }}>
                    <option value="">— Sélectionner —</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}"
                                @selected(old('employee_id', $pointage->employee_id ?? request('employee_id')) == $e->id)>
                            {{ $e->noms }} {{ $e->prenoms }} ({{ $e->matricule ?? '—' }})
                        </option>
                    @endforeach
                </select>
                @if($isEdit)<input type="hidden" name="employee_id" value="{{ $pointage->employee_id }}">@endif
                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                       value="{{ old('date', isset($pointage) ? $pointage->date?->toDateString() : now()->toDateString()) }}" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">Heures normales</label>
                <input type="number" step="0.25" min="0" max="24" name="h_normales" class="form-control"
                       value="{{ old('h_normales', $pointage->h_normales ?? 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-warning">Heures sup</label>
                <input type="number" step="0.25" min="0" max="24" name="h_sup" class="form-control"
                       value="{{ old('h_sup', $pointage->h_sup ?? 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Heures de nuit</label>
                <input type="number" step="0.25" min="0" max="24" name="h_nuit" class="form-control"
                       value="{{ old('h_nuit', $pointage->h_nuit ?? 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Heures dimanche</label>
                <input type="number" step="0.25" min="0" max="24" name="h_dimanche" class="form-control"
                       value="{{ old('h_dimanche', $pointage->h_dimanche ?? 0) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Motif <small class="text-muted">(absence, formation, mission…)</small></label>
                <input type="text" name="motif" class="form-control" maxlength="100"
                       value="{{ old('motif', $pointage->motif ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="0" @selected(old('statut', $pointage->statut ?? 0) == 0)>Brouillon</option>
                    <option value="1" @selected(old('statut', $pointage->statut ?? 0) == 1)>Valider directement</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control" maxlength="500">{{ old('notes', $pointage->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Enregistrer' }}</button>
    </div>
</div>
