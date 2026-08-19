@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-8"><label class="form-label">Libellé <span class="text-danger">*</span></label>
        <input type="text" name="libelle" class="form-control" value="{{ old('libelle', $campagne->libelle) }}" required></div>
    <div class="col-md-2"><label class="form-label">Début <span class="text-danger">*</span></label>
        <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', optional($campagne->date_debut)->format('Y-m-d')) }}" required></div>
    <div class="col-md-2"><label class="form-label">Fin</label>
        <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', optional($campagne->date_fin)->format('Y-m-d')) }}"></div>
    <div class="col-12"><label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $campagne->description) }}</textarea></div>
    <div class="col-12"><label class="form-label">Prestataires ciblés</label>
        <select name="prestataires[]" class="form-select" multiple size="8">
            @php $selected = $campagne->exists ? $campagne->prestataires->pluck('id')->all() : (old('prestataires', [])); @endphp
            @foreach($prestataires as $p)<option value="{{ $p->id }}" @selected(in_array($p->id, $selected))>{{ $p->raison_sociale ?: $p->nom }}</option>@endforeach
        </select>
        <small class="text-muted">Ctrl/Cmd+clic pour sélection multiple.</small>
    </div>
</div></div></div>

<button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
<a href="{{ route('appro.campagnes.index') }}" class="btn btn-outline-secondary">Annuler</a>
