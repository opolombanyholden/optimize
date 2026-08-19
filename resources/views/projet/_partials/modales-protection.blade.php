{{-- Modale : Demande de modification --}}
<div class="modal fade" id="modalDemandeModif" tabindex="-1">
    <div class="modal-dialog">
        <form id="demandeModifForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                <h5 class="modal-title text-white"><i class="fas fa-pen-fancy me-2"></i> Demander une modification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3" style="font-size:.78rem;">
                    <i class="fas fa-lock me-1"></i> Cet élément est verrouillé car il contient du contenu actif. Un super-administrateur doit approuver la modification.
                </div>
                <p style="font-size:.82rem;color:#475569;">Élément : <strong id="demandeModifNom"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Motif de la modification <span class="text-danger">*</span></label>
                    <textarea name="motif" rows="4" class="form-control" required minlength="10"
                              placeholder="Décrivez les modifications souhaitées et pourquoi elles sont nécessaires..."></textarea>
                    <small class="form-hint">Minimum 10 caractères</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn text-white" style="background:#F59E0B;"><i class="fas fa-paper-plane me-2"></i> Soumettre</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale : Demande de suppression --}}
<div class="modal fade" id="modalDemandeSuppression" tabindex="-1">
    <div class="modal-dialog">
        <form id="demandeSuppressionForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#DC2626,#B91C1C);">
                <h5 class="modal-title text-white"><i class="fas fa-trash me-2"></i> Demander la suppression</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-2 mb-3" style="font-size:.78rem;">
                    <i class="fas fa-triangle-exclamation me-1"></i> L'élément sera mis en corbeille après approbation. Cette action est réversible par un administrateur.
                </div>
                <p style="font-size:.82rem;color:#475569;">Élément à supprimer : <strong id="demandeSuppressionNom"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Motif de la suppression <span class="text-danger">*</span></label>
                    <textarea name="motif" rows="3" class="form-control" required minlength="10"
                              placeholder="Expliquez pourquoi cet élément doit être supprimé..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-2"></i> Soumettre la demande</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function demanderModification(entityId, type, nom) {
    document.getElementById('demandeModifNom').textContent = nom;
    document.getElementById('demandeModifForm').action = '/projet/demandes/' + type + '/' + entityId + '/modification';
    new bootstrap.Modal(document.getElementById('modalDemandeModif')).show();
}

function demanderSuppression(entityId, type, nom) {
    document.getElementById('demandeSuppressionNom').textContent = nom;
    document.getElementById('demandeSuppressionForm').action = '/projet/demandes/' + type + '/' + entityId + '/suppression';
    new bootstrap.Modal(document.getElementById('modalDemandeSuppression')).show();
}
</script>
@endpush
