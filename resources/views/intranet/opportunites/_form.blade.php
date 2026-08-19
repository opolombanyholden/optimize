@php
    $isEdit = isset($opportunite) && $opportunite->exists;
    $tagsString = is_array($opportunite->tags ?? null) ? implode(', ', $opportunite->tags) : '';
    $publication = $isEdit ? $opportunite->publication : null;
@endphp

<div class="form-grid">
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-fire-flame-curved"></i> Opportunité</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control" required value="{{ old('titre', $opportunite->titre) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <div id="opp-editor">{!! old('description', $opportunite->description) !!}</div>
                    <input type="hidden" name="description" id="opp-description" value="{{ old('description', $opportunite->description) }}">
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact</label>
                        <select name="contact_id" id="select-contact" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($contacts as $c)
                                <option value="{{ $c->id }}" @selected(old('contact_id', $opportunite->contact_id) == $c->id)>
                                    {{ $c->prenoms }} {{ $c->nom }}{{ $c->email ? " · {$c->email}" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organisation</label>
                        <select name="organisation_id" id="select-organisation" class="form-select">
                            <option value="">— Aucune —</option>
                            @foreach($organisations as $o)
                                <option value="{{ $o->id }}" @selected(old('organisation_id', $opportunite->organisation_id) == $o->id)>{{ $o->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-coins"></i> Valorisation</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Montant</label>
                        <input type="number" step="0.01" name="valeur" class="form-control" value="{{ old('valeur', $opportunite->valeur) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Devise</label>
                        <select name="devise" class="form-select">
                            @foreach(['XAF','EUR','USD','XOF','MAD'] as $d)
                                <option value="{{ $d }}" @selected(old('devise', $opportunite->devise) === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Probabilité (%)</label>
                        <input type="number" name="probabilite" min="0" max="100" class="form-control" value="{{ old('probabilite', $opportunite->probabilite) }}">
                    </div>
                </div>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $opportunite,
            'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.opportunites.pieces-jointes.destroy',
            'deleteRouteParam' => 'opportunite',
        ])

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-tags"></i> Tags & notes</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           placeholder="urgent, b2b…"
                           value="{{ old('tags', $tagsString) }}">
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $opportunite->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-chart-gantt"></i> Pipeline</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Étape <span class="text-danger">*</span></label>
                    <select name="etape_id" class="form-select" required>
                        @foreach($etapes as $e)
                            <option value="{{ $e->id }}" data-prob="{{ $e->probabilite_defaut }}"
                                @selected(old('etape_id', $opportunite->etape_id) == $e->id)>
                                {{ $e->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        @foreach(['ouvert'=>'Ouvert','gagnee'=>'Gagnée','perdue'=>'Perdue','abandonnee'=>'Abandonnée'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('statut', $opportunite->statut ?? 'ouvert') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Responsable</label>
                    <select name="responsable_id" id="select-responsable" class="form-select">
                        <option value="">— Aucun —</option>
                        @foreach($responsables as $r)
                            <option value="{{ $r->id }}" @selected(old('responsable_id', $opportunite->responsable_id) == $r->id)>
                                {{ $r->prenoms }} {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance', $opportunite->date_echeance?->format('Y-m-d')) }}">
                </div>
                <div class="mb-0">
                    <label class="form-label">Source</label>
                    <input type="text" name="source" class="form-control" value="{{ old('source', $opportunite->source) }}">
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Créer l\'opportunité' }}
            </button>
            <a href="{{ route('intranet.opportunites.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    ['select-contact', 'select-organisation', 'select-responsable'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    const editorEl = document.getElementById('opp-editor');
    const hidden   = document.getElementById('opp-description');
    if (editorEl) {
        const quill = new Quill('#opp-editor', {
            theme: 'snow',
            placeholder: 'Décrivez l\'opportunité, le besoin, la solution proposée…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ color: [] }],
                    ['link', 'blockquote'],
                    ['clean'],
                ],
            },
        });
        quill.on('text-change', () => { hidden.value = quill.root.innerHTML; });
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => { hidden.value = quill.root.innerHTML; });
    }
});
</script>
@include('intranet._partials.publication-scripts')
@endpush
