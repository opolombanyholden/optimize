@php
    $documentsAttendus = $organisation->documentsAttendus();
    $completude        = $organisation->completude_documents;
@endphp

@if($documentsAttendus->isNotEmpty())
<div class="contact-detail-card mt-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h4 class="contact-detail-card-title mb-0"><i class="fas fa-folder-open"></i> Documents à fournir</h4>
        @if($completude['obligatoires_total'] > 0)
            <span class="badge bg-{{ $completude['complet'] ? 'success' : 'warning' }}">
                <i class="fas fa-{{ $completude['complet'] ? 'check' : 'triangle-exclamation' }} me-1"></i>
                Obligatoires : {{ $completude['obligatoires_fournis'] }} / {{ $completude['obligatoires_total'] }} ({{ $completude['pct'] }}%)
            </span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:34%">Document</th>
                    <th style="width:12%">Exigence</th>
                    <th>Fichier(s) fournis</th>
                    @can('update:organisation_crm')<th class="text-end" style="width:10%">Action</th>@endcan
                </tr>
            </thead>
            <tbody>
            @foreach($documentsAttendus as $item)
                @php
                    $type      = $item['type_document'];
                    $obl       = $item['obligatoire'];
                    $docs      = $item['documents'];
                    $manquant  = $docs->isEmpty();
                @endphp
                <tr class="{{ $obl && $manquant ? 'table-warning' : '' }}">
                    <td>
                        <i class="fas {{ $type->icone }} text-muted me-2"></i>
                        <strong>{{ $type->libelle }}</strong>
                        @if($type->description)
                            <div class="text-muted small">{{ $type->description }}</div>
                        @endif
                    </td>
                    <td>
                        @if($obl)
                            <span class="badge bg-danger">Obligatoire</span>
                        @else
                            <span class="badge bg-secondary">Facultatif</span>
                        @endif
                    </td>
                    <td>
                        @if($manquant)
                            <em class="text-muted">— Non fourni</em>
                        @else
                            @foreach($docs as $d)
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <a href="{{ $d->url }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-arrow-down"></i>
                                        {{ $d->nom_original ?: $type->libelle }}
                                    </a>
                                    <small class="text-muted">({{ $d->taille_humaine }})</small>
                                    @if($d->date_expiration)
                                        @if($d->est_expire)
                                            <span class="badge bg-danger">Expiré le {{ $d->date_expiration->format('d/m/Y') }}</span>
                                        @elseif($d->expire_bientot)
                                            <span class="badge bg-warning text-dark">Expire le {{ $d->date_expiration->format('d/m/Y') }}</span>
                                        @else
                                            <small class="text-muted">Expire le {{ $d->date_expiration->format('d/m/Y') }}</small>
                                        @endif
                                    @endif
                                    @can('update:organisation_crm')
                                    <form action="{{ route('intranet.organisations.documents.destroy', [$organisation, $d]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce document ?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0" title="Supprimer">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            @endforeach
                        @endif
                    </td>
                    @can('update:organisation_crm')
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#modal-upload-doc"
                                data-type-id="{{ $type->id }}" data-type-libelle="{{ $type->libelle }}"
                                data-avec-expiration="{{ $type->avec_expiration ? '1' : '0' }}">
                            <i class="fas fa-upload"></i>
                        </button>
                    </td>
                    @endcan
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@can('update:organisation_crm')
{{-- ═════════ MODALE UPLOAD ═════════ --}}
<div class="modal fade" id="modal-upload-doc" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.organisations.documents.store', $organisation) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i> Ajouter un document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="type_document_id" id="mdl-type-id">
                <div class="mb-3">
                    <label class="form-label">Type de document</label>
                    <input type="text" id="mdl-type-libelle" class="form-control-plaintext fw-semibold" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="fichier" class="form-control" required
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                    <small class="text-muted">PDF, image, DOC ou XLS — 20 Mo max.</small>
                </div>
                <div class="mb-3" id="mdl-expiration-wrap" style="display:none">
                    <label class="form-label">Date d'expiration</label>
                    <input type="date" name="date_expiration" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet"><i class="fas fa-upload me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal-upload-doc');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (evt) {
        const btn = evt.relatedTarget;
        if (!btn) return;
        document.getElementById('mdl-type-id').value      = btn.dataset.typeId;
        document.getElementById('mdl-type-libelle').value = btn.dataset.typeLibelle;
        document.getElementById('mdl-expiration-wrap').style.display = btn.dataset.avecExpiration === '1' ? '' : 'none';
    });
});
</script>
@endpush
@endcan
@endif
