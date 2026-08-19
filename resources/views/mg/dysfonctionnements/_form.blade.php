@csrf
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $dysfonctionnement->label) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priorité</label>
                <select name="priorite" class="form-select">
                    @foreach($priorites as $k => $v)
                        <option value="{{ $k }}" @selected(old('priorite', $dysfonctionnement->priorite) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select name="type_id" class="form-select">
                    <option value="">—</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('type_id', $dysfonctionnement->type_id) == $t->id)>{{ $t->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Immobilisation liée</label>
                <select name="immobilisation_id" class="form-select">
                    <option value="">— Aucune</option>
                    @foreach($immobilisations as $imm)
                        <option value="{{ $imm->id }}" @selected(old('immobilisation_id', $dysfonctionnement->immobilisation_id) == $imm->id)>{{ $imm->code ? "[{$imm->code}] " : '' }}{{ $imm->designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input type="text" name="localisation" class="form-control" value="{{ old('localisation', $dysfonctionnement->localisation) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $dysfonctionnement->description) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes (photos, rapport…)</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple>
        @if($dysfonctionnement->exists && $dysfonctionnement->piecesJointes->isNotEmpty())
            <hr>
            @include('mg._partials.pieces-jointes', ['pieces' => $dysfonctionnement->piecesJointes])
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('mg.dysfonctionnements.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
