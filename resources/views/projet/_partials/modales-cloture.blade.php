{{-- Modale soumission clôture --}}
<div class="modal fade" id="modalCloture" tabindex="-1">
    <div class="modal-dialog">
        <form id="clotureForm" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#6366F1,#4F46E5);">
                <h5 class="modal-title text-white"><i class="fas fa-paper-plane me-2"></i> <span id="clotureTitle">Soumettre pour clôture</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;">Soumission de : <strong id="clotureNom"></strong></p>
                <div id="clotureRejetMsg" style="display:none;" class="alert alert-warning py-2 mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">Justification de clôture <span class="text-danger">*</span></label>
                    <textarea name="justification" id="clotureJustification" rows="4" class="form-control" required
                              placeholder="Décrivez pourquoi cette entité peut être clôturée : livrables réalisés, critères remplis..."></textarea>
                    <small class="form-hint">Minimum 10 caractères</small>
                </div>
                <div class="mb-0">
                    <label class="form-label"><i class="fas fa-paperclip me-1"></i> Pièces justificatives (optionnelles)</label>
                    <input type="file" name="pieces_jointes[]" class="form-control" multiple
                           accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <small class="form-hint">Livrables, captures, comptes-rendus… (50 Mo max par fichier)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#6366F1;"><i class="fas fa-paper-plane me-2"></i> Soumettre</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale décision valideur --}}
<div class="modal fade" id="modalDecision" tabindex="-1">
    <div class="modal-dialog">
        <form id="decisionForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" id="decisionHeader">
                <h5 class="modal-title text-white" id="decisionTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.82rem;color:#475569;" id="decisionDesc"></p>
                <div class="mb-3">
                    <label class="form-label" id="decisionLabel">Commentaire</label>
                    <textarea name="commentaire" id="decisionCommentaire" rows="3" class="form-control"></textarea>
                </div>
                <input type="hidden" name="motif" id="decisionMotif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" id="decisionBtn"></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale historique --}}
<div class="modal fade" id="modalHistorique" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-clock-rotate-left me-2"></i> Historique de validation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="historiqueBody" style="max-height:400px;overflow-y:auto;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function ouvrirModalCloture(entityId, type, nom, statutCloture) {
    document.getElementById('clotureNom').textContent = nom;
    document.getElementById('clotureJustification').value = '';
    const rejetMsg = document.getElementById('clotureRejetMsg');
    if (statutCloture === 'rejete' || statutCloture === 'revisions') {
        document.getElementById('clotureTitle').textContent = 'Resoumettre pour clôture';
        rejetMsg.style.display = 'block';
        rejetMsg.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Soumission précédente ' + (statutCloture === 'rejete' ? 'rejetée' : 'renvoyée pour révisions') + '.';
    } else {
        document.getElementById('clotureTitle').textContent = 'Soumettre pour clôture';
        rejetMsg.style.display = 'none';
    }
    document.getElementById('clotureForm').action = '/projet/cloture/' + type + '/' + entityId + '/soumettre';
    new bootstrap.Modal(document.getElementById('modalCloture')).show();
}

function ouvrirModalDecision(entityId, type, nom, action) {
    const isApprouver = action === 'approuver';
    document.getElementById('decisionHeader').style.background = isApprouver ? 'linear-gradient(135deg,#16A34A,#15803D)' : 'linear-gradient(135deg,#DC2626,#B91C1C)';
    document.getElementById('decisionTitle').textContent = isApprouver ? 'Approuver la clôture' : 'Rejeter la clôture';
    document.getElementById('decisionDesc').textContent = (isApprouver ? 'Approuver : ' : 'Rejeter : ') + nom;
    document.getElementById('decisionLabel').textContent = isApprouver ? 'Commentaire (optionnel)' : 'Motif de rejet *';
    document.getElementById('decisionCommentaire').required = !isApprouver;
    document.getElementById('decisionCommentaire').value = '';
    document.getElementById('decisionBtn').innerHTML = isApprouver ? '<i class="fas fa-check me-1"></i> Approuver' : '<i class="fas fa-times me-1"></i> Rejeter';
    document.getElementById('decisionBtn').style.background = isApprouver ? '#16A34A' : '#DC2626';
    document.getElementById('decisionForm').action = '/projet/cloture/' + type + '/' + entityId + '/' + action;
    document.getElementById('decisionForm').onsubmit = function () {
        if (!isApprouver) {
            document.getElementById('decisionMotif').value = document.getElementById('decisionCommentaire').value;
            document.getElementById('decisionCommentaire').name = '';
            document.getElementById('decisionMotif').name = 'motif';
        } else {
            document.getElementById('decisionCommentaire').name = 'commentaire';
            document.getElementById('decisionMotif').name = '';
        }
    };
    new bootstrap.Modal(document.getElementById('modalDecision')).show();
}

function voirHistorique(entityId, type) {
    const body = document.getElementById('historiqueBody');
    body.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
    new bootstrap.Modal(document.getElementById('modalHistorique')).show();
    fetch('/projet/cloture/' + type + '/' + entityId + '/historique')
        .then(r => r.json())
        .then(data => {
            if (!data.length) { body.innerHTML = '<p class="text-center text-muted py-3">Aucun historique.</p>'; return; }
            let html = '';
            data.forEach(h => {
                html += '<div class="d-flex gap-2 mb-3 align-items-start">';
                html += '<div style="width:28px;height:28px;border-radius:50%;background:' + h.couleur + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
                html += '<i class="fas ' + h.icone + ' text-white" style="font-size:.65rem;"></i></div>';
                html += '<div><div style="font-size:.78rem;font-weight:600;">' + h.libelle + '</div>';
                html += '<div style="font-size:.7rem;color:#64748B;">' + (h.user || '') + ' · ' + h.date + '</div>';
                if (h.justification) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-quote-left me-1" style="font-size:.55rem;"></i>' + h.justification + '</div>';
                if (h.commentaire) html += '<div style="font-size:.72rem;color:#475569;margin-top:2px;"><i class="fas fa-comment me-1" style="font-size:.55rem;"></i>' + h.commentaire + '</div>';
                html += '</div></div>';
            });
            body.innerHTML = html;
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-danger py-3">Erreur.</p>'; });
}
</script>
@endpush
