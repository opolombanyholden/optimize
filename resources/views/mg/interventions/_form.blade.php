@csrf
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $intervention->label) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select name="type_intervention" class="form-select">
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}" @selected(old('type_intervention', $intervention->type_intervention) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Dysfonctionnement lié</label>
                <select name="dysfonctionnement_id" class="form-select">
                    <option value="">— Aucun</option>
                    @foreach($dysfonctionnements as $d)
                        <option value="{{ $d->id }}" @selected(old('dysfonctionnement_id', $intervention->dysfonctionnement_id) == $d->id)>{{ Str::limit($d->label, 60) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Immobilisation</label>
                <select name="immobilisation_id" class="form-select">
                    <option value="">— Aucune</option>
                    @foreach($immobilisations as $imm)
                        <option value="{{ $imm->id }}" @selected(old('immobilisation_id', $intervention->immobilisation_id) == $imm->id)>{{ $imm->designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Technicien</label>
                <select name="technicien_id" class="form-select">
                    <option value="">— À affecter</option>
                    @foreach($techniciens as $u)
                        <option value="{{ $u->id }}" @selected(old('technicien_id', $intervention->technicien_id) == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date planifiée</label>
                <input type="datetime-local" name="date_planifiee" class="form-control" value="{{ old('date_planifiee', optional($intervention->date_planifiee)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Coût estimé (XAF)</label>
                <input type="number" step="0.01" min="0" name="cout" class="form-control text-end" value="{{ old('cout', $intervention->cout) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $intervention->description) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes (bon de commande, photos…)</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple>
        @if($intervention->exists && $intervention->piecesJointes->isNotEmpty())
            <hr>
            @include('mg._partials.pieces-jointes', ['pieces' => $intervention->piecesJointes])
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('mg.interventions.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
