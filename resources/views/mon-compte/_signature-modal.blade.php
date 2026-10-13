{{-- Modale de création ($signature = null) ou de modification d'une signature personnelle --}}
@php
    $id = $signature ? 'edit-'.$signature->id : 'create';
    $estCible = $errors->any() && old('_signature_form') === $id;
@endphp
<div class="modal fade" id="sigModal-{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ $signature ? route('mon-compte.signatures.update', $signature) : route('mon-compte.signatures.store') }}" method="POST" class="modal-content" data-sig-form>
            @csrf
            @if($signature) @method('PUT') @endif
            <input type="hidden" name="_signature_form" value="{{ $id }}">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-signature me-2"></i>{{ $signature ? 'Modifier « '.$signature->nom.' »' : 'Nouvelle signature' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($estCible)
                    <div class="alert alert-danger py-2" style="font-size:.85rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                @endif
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" maxlength="100" required value="{{ $estCible ? old('nom') : $signature?->nom }}" placeholder="Ex. Professionnelle, Courte, English">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="par_defaut" value="0">
                            <input class="form-check-input" type="checkbox" name="par_defaut" value="1" id="sigDefaut-{{ $id }}" @checked($estCible ? old('par_defaut') : $signature?->par_defaut)>
                            <label class="form-check-label" for="sigDefaut-{{ $id }}">Signature par défaut (ajoutée aux nouveaux mails)</label>
                        </div>
                    </div>
                </div>
                @include('mon-compte._signature-editeur', [
                    'sourceHtml' => $estCible ? old('source_html') : ($signature?->source_html ?? $signature?->contenu),
                    'sourceCss'  => $estCible ? old('source_css') : $signature?->source_css,
                    'variables'  => true,
                ])
                <div class="form-text mt-2">Le CSS est converti en styles en ligne (seule forme fiable dans les messageries). Les scripts et contenus actifs sont supprimés. Le logo est joint au mail comme image intégrée : il s'affiche aussi dans Gmail et Outlook.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
