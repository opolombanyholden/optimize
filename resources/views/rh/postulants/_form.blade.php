@php $isEdit = isset($postulant) && $postulant->exists; @endphp

<div class="card data-card mb-3">
    <div class="card-header"><strong><i class="fas fa-id-card me-1"></i> Identité</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Noms <span class="text-danger">*</span></label>
                <input type="text" name="noms" class="form-control @error('noms') is-invalid @enderror"
                       value="{{ old('noms', $postulant->noms ?? '') }}" required>
                @error('noms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Prénoms <span class="text-danger">*</span></label>
                <input type="text" name="prenoms" class="form-control @error('prenoms') is-invalid @enderror"
                       value="{{ old('prenoms', $postulant->prenoms ?? '') }}" required>
                @error('prenoms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $postulant->email ?? '') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="text" name="contact" class="form-control"
                       value="{{ old('contact', $postulant->contact ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Âge</label>
                <input type="text" name="age" class="form-control" maxlength="10"
                       value="{{ old('age', $postulant->age ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date de naissance</label>
                <input type="date" name="date_naissance" class="form-control"
                       value="{{ old('date_naissance', $postulant->date_naissance ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><strong><i class="fas fa-briefcase me-1"></i> Candidature</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Campagne de recrutement</label>
                <select name="recrutement_id" id="recrutement_id" class="form-select">
                    <option value="">— Aucune (spontanée) —</option>
                    @foreach($recrutements as $r)
                        <option value="{{ $r->id }}"
                                @selected(old('recrutement_id', $postulant->recrutement_id ?? ($recrutementId ?? null)) == $r->id)>
                            {{ $r->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Profil postulé <span class="text-danger">*</span></label>
                <select name="profil_id" id="profil_id" class="form-select @error('profil_id') is-invalid @enderror" required>
                    <option value="">— Choisir un profil —</option>
                    @foreach($profils as $p)
                        <option value="{{ $p->id }}" @selected(old('profil_id', $postulant->profil_id ?? null) == $p->id)>{{ $p->label }}</option>
                    @endforeach
                </select>
                @error('profil_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text text-muted">Liste filtrée par campagne sélectionnée.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="0" @selected(old('statut', $postulant->statut ?? 0) == 0)>Nouveau</option>
                    <option value="1" @selected(old('statut', $postulant->statut ?? 0) == 1)>Entretien</option>
                    <option value="2" @selected(old('statut', $postulant->statut ?? 0) == 2)>Retenu</option>
                    <option value="3" @selected(old('statut', $postulant->statut ?? 0) == 3)>Rejeté</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fichiers (CV, lettre…)</label>
                <textarea name="fichiersjoin" class="form-control" rows="1" placeholder="URLs ou noms de fichiers (provisoire)">{{ old('fichiersjoin', $postulant->fichiersjoin ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('rh.postulants.index') }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Enregistrer la candidature' }}
    </button>
</div>

@push('scripts')
<script>
    function setPlaceholderOption(select, text) {
        select.replaceChildren();
        const opt = document.createElement('option');
        opt.value = ''; opt.textContent = text;
        select.appendChild(opt);
    }
    document.getElementById('recrutement_id')?.addEventListener('change', async function () {
        const select = document.getElementById('profil_id');
        setPlaceholderOption(select, '— Chargement… —');
        if (!this.value) {
            setPlaceholderOption(select, '— Choisir une campagne d\'abord —');
            return;
        }
        try {
            const res = await fetch(`{{ url('/rh/recrutements') }}/${this.value}/profils`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Erreur réseau');
            const list = await res.json();
            setPlaceholderOption(select, '— Choisir un profil —');
            list.forEach(p => {
                const o = document.createElement('option');
                o.value = p.id;
                o.textContent = p.label;
                select.appendChild(o);
            });
        } catch (e) {
            setPlaceholderOption(select, '— Erreur de chargement —');
        }
    });
</script>
@endpush
