@php
    $isEdit = isset($echantillon) && $echantillon->exists;
    $selected = $isEdit ? ($selectedIds ?? []) : (old('employee_ids', []));
@endphp

<div class="card data-card">
    <div class="card-header">
        <strong><i class="fas fa-info-circle me-1 text-muted"></i> Informations générales</strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" id="libelle" required maxlength="255"
                       class="form-control @error('libelle') is-invalid @enderror"
                       value="{{ old('libelle', $echantillon->libelle ?? '') }}"
                       placeholder="Ex : Force de vente — équipe Libreville">
                @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="code" class="form-label">Code <small class="text-muted">(auto si vide)</small></label>
                <input type="text" name="code" id="code" maxlength="60"
                       class="form-control font-monospace @error('code') is-invalid @enderror"
                       value="{{ old('code', $echantillon->code ?? '') }}"
                       placeholder="ECH-…">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="2" maxlength="500"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="À quoi sert ce groupe ?">{{ old('description', $echantillon->description ?? '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label for="couleur" class="form-label">Couleur <small class="text-muted">(visuel)</small></label>
                <input type="color" name="couleur" id="couleur"
                       class="form-control form-control-color"
                       value="{{ old('couleur', $echantillon->couleur ?? '#0EA5E9') }}">
            </div>
            <div class="col-md-3">
                <label for="icone" class="form-label">Icône (FA)</label>
                <input type="text" name="icone" id="icone" maxlength="40"
                       class="form-control font-monospace"
                       value="{{ old('icone', $echantillon->icone ?? 'fa-users') }}"
                       placeholder="fa-users">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="statut" id="statut" value="1"
                           {{ old('statut', $echantillon->statut ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="statut">Actif</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-users me-1 text-muted"></i> Employés inclus <span class="text-danger">*</span></strong>
        <small class="text-muted"><span id="empCount">0</span> sélectionné(s)</small>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <input type="text" id="empFilter" class="form-control form-control-sm"
                   placeholder="Filtrer par nom, matricule ou département…">
        </div>
        <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Tout cocher (visibles)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectNone">Tout décocher</button>
        </div>
        @error('employee_ids')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
        <div class="table-responsive" style="max-height: 480px;">
            <table class="table table-sm table-hover mb-0" id="empTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Nom complet</th>
                        <th>Matricule</th>
                        <th>Département</th>
                        <th>Poste</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        @php
                            $checked = in_array($emp->id, is_array($selected) ? $selected : []);
                        @endphp
                        <tr class="emp-row"
                            data-search="{{ strtolower(($emp->noms ?? '') . ' ' . ($emp->prenoms ?? '') . ' ' . ($emp->matricule ?? '') . ' ' . ($emp->departement ?? '')) }}">
                            <td>
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                       class="form-check-input emp-check" {{ $checked ? 'checked' : '' }}>
                            </td>
                            <td>{{ $emp->noms }} {{ $emp->prenoms }}</td>
                            <td><code class="small">{{ $emp->matricule ?? '—' }}</code></td>
                            <td>{{ $emp->departement ?? '—' }}</td>
                            <td>{{ $emp->poste ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('rh.echantillons-paie.index') }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer l\'échantillon' }}
    </button>
</div>

@push('scripts')
<script>
    (function () {
        const filter = document.getElementById('empFilter');
        const rows   = document.querySelectorAll('.emp-row');
        const checks = document.querySelectorAll('.emp-check');
        const count  = document.getElementById('empCount');

        function refreshCount() {
            const n = Array.from(checks).filter(c => c.checked).length;
            count.textContent = n;
        }
        checks.forEach(c => c.addEventListener('change', refreshCount));
        refreshCount();

        filter?.addEventListener('input', () => {
            const q = filter.value.toLowerCase().trim();
            rows.forEach(r => {
                const m = r.getAttribute('data-search') || '';
                r.style.display = (q === '' || m.includes(q)) ? '' : 'none';
            });
        });

        document.getElementById('selectAll')?.addEventListener('click', () => {
            rows.forEach(r => {
                if (r.style.display !== 'none') {
                    const c = r.querySelector('.emp-check');
                    if (c) c.checked = true;
                }
            });
            refreshCount();
        });
        document.getElementById('selectNone')?.addEventListener('click', () => {
            checks.forEach(c => c.checked = false);
            refreshCount();
        });
    })();
</script>
@endpush
