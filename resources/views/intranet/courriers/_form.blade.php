@php
    $isEdit = isset($courrier) && $courrier->exists;
    $publication = $isEdit ? $courrier->publication : null;
@endphp

<div class="form-grid">
    <div class="form-main">

        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-envelope"></i> Identification</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required id="typeSelect">
                            @foreach(['entrant'=>'📥 Entrant','sortant'=>'📤 Sortant','interne'=>'🔄 Interne'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('type', $courrier->type) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Référence</label>
                        <input type="text" name="reference" class="form-control"
                               placeholder="Auto-générée si vide"
                               value="{{ old('reference', $courrier->reference) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            @foreach(['recu'=>'Reçu','en_traitement'=>'En traitement','traite'=>'Traité','archive'=>'Archivé','envoye'=>'Envoyé','brouillon'=>'Brouillon'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('statut', $courrier->statut ?? 'recu') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 mb-3">
                    <label class="form-label">Objet <span class="text-danger">*</span></label>
                    <input type="text" name="objet" class="form-control" required value="{{ old('objet', $courrier->objet) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Résumé / Extrait</label>
                    <textarea name="extrait" rows="2" class="form-control" maxlength="500">{{ old('extrait', $courrier->extrait) }}</textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label">Contenu</label>
                    <div id="courrier-editor">{!! old('contenu', $courrier->contenu) !!}</div>
                    <input type="hidden" name="contenu" id="courrier-contenu" value="{{ old('contenu', $courrier->contenu) }}">
                </div>
            </div>
        </div>

        {{-- Expéditeur / Destinataire --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-arrows-left-right"></i> Expéditeur & Destinataire</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Expéditeur (nom)</label>
                        <input type="text" name="expediteur" class="form-control" value="{{ old('expediteur', $courrier->expediteur) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email expéditeur</label>
                        <input type="email" name="expediteur_email" class="form-control" value="{{ old('expediteur_email', $courrier->expediteur_email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Organisation expéditrice</label>
                        <input type="text" name="expediteur_organisation" class="form-control" value="{{ old('expediteur_organisation', $courrier->expediteur_organisation) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service expéditeur (interne)</label>
                        <select name="service_expediteur_id" id="select-service-exp" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_expediteur_id', $courrier->service_expediteur_id) == $s->id)>{{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Destinataire (nom)</label>
                        <input type="text" name="destinataire" class="form-control" value="{{ old('destinataire', $courrier->destinataire) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email destinataire</label>
                        <input type="email" name="destinataire_email" class="form-control" value="{{ old('destinataire_email', $courrier->destinataire_email) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Service destinataire (interne)</label>
                        <select name="service_destinataire_id" id="select-service-dest" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_destinataire_id', $courrier->service_destinataire_id) == $s->id)>{{ $s->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dates --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-calendar"></i> Dates</div>
            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date de réception</label>
                        <input type="date" name="date_reception" class="form-control"
                               value="{{ old('date_reception', $courrier->date_reception?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date d'expédition</label>
                        <input type="date" name="date_expedition" class="form-control"
                               value="{{ old('date_expedition', $courrier->date_expedition?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Échéance traitement</label>
                        <input type="date" name="echeance_traitement" class="form-control"
                               value="{{ old('echeance_traitement', $courrier->echeance_traitement?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        @include('intranet._partials.media-section', [
            'entity' => $courrier,
            'isEdit' => $isEdit,
            'deleteRouteName' => 'intranet.courriers.pieces-jointes.destroy',
            'deleteRouteParam' => 'courrier',
        ])
    </div>

    <div class="form-side">

        {{-- Assignation --}}
        <div class="form-card">
            <div class="form-card-header"><i class="fas fa-user-check"></i> Traitement</div>
            <div class="form-card-body">
                <div class="mb-3">
                    <label class="form-label">Assigné à</label>
                    <select name="assigne_a" id="select-assigne" class="form-select">
                        <option value="">— Non assigné —</option>
                        @foreach($utilisateurs as $u)
                            <option value="{{ $u->id }}" @selected(old('assigne_a', $courrier->assigne_a) == $u->id)>
                                {{ $u->prenoms }} {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="urgent" value="1" @checked(old('urgent', $courrier->urgent))>
                    <label class="form-check-label text-danger fw-semibold">🔥 Urgent</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" name="confidentiel" value="1" @checked(old('confidentiel', $courrier->confidentiel))>
                    <label class="form-check-label">🔒 Confidentiel</label>
                </div>
            </div>
        </div>

        @include('intranet._partials.publication-section')

        <div class="form-actions">
            <button type="submit" class="btn btn-intranet w-100">
                <i class="fas fa-{{ $isEdit ? 'save' : 'plus' }} me-2"></i>
                {{ $isEdit ? 'Mettre à jour' : 'Enregistrer le courrier' }}
            </button>
            <a href="{{ route('intranet.courriers.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
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
    ['select-assigne', 'select-service-exp', 'select-service-dest'].forEach(id => {
        const el = document.getElementById(id);
        if (el) new TomSelect(el, { allowEmptyOption: true, maxOptions: 500, dropdownParent: 'body' });
    });

    const editorEl = document.getElementById('courrier-editor');
    const hidden   = document.getElementById('courrier-contenu');
    if (editorEl) {
        const quill = new Quill('#courrier-editor', {
            theme: 'snow',
            placeholder: 'Contenu détaillé du courrier…',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
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
