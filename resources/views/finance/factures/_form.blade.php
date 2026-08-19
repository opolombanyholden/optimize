@php
    $isEdit = isset($facture) && $facture->exists;
    $sens = old('sens', $facture->sens ?? $sens ?? 'depense');
    $tiersTypeDefault = old('tiers_type', $facture->tiers_type ?? ($sens === 'depense' ? 'fournisseur' : 'client'));
    $tiersSource = old('tiers_source', $facture->tiers_source ?? \App\Models\Facture::TIERS_SOURCE_ORGANISATION);
    $tiersInfos  = old('tiers_infos', $facture->tiers_infos_json ?? []);
    $labelTiers  = $sens === 'depense' ? 'Fournisseur' : 'Client';
@endphp

<input type="hidden" name="sens" value="{{ $sens }}">
<input type="hidden" name="tiers_type" value="{{ $tiersTypeDefault }}">

<div class="card data-card mb-3">
    <div class="card-header">
        <strong>
            @if($sens === 'depense')
                <i class="fas fa-arrow-down text-danger me-1"></i> Facture fournisseur (dépense)
            @else
                <i class="fas fa-arrow-up text-success me-1"></i> Facture client (recette)
            @endif
        </strong>
    </div>
    <div class="card-body">
        {{-- ═════════ TIERS (enregistré ou externe) ═════════ --}}
        <div class="mb-3">
            <label class="form-label small text-uppercase">{{ $labelTiers }}</label>
            <div class="btn-group w-100 mb-2" role="group">
                <input type="radio" class="btn-check" name="tiers_source" id="ts-org" value="organisation" @checked($tiersSource === 'organisation')>
                <label class="btn btn-outline-primary" for="ts-org">
                    <i class="fas fa-building me-1"></i> {{ $labelTiers }} enregistré
                </label>
                <input type="radio" class="btn-check" name="tiers_source" id="ts-ext" value="externe" @checked($tiersSource === 'externe')>
                <label class="btn btn-outline-warning" for="ts-ext">
                    <i class="fas fa-user-pen me-1"></i> Saisie manuelle (externe)
                </label>
            </div>

            {{-- Sélecteur d'un tiers enregistré --}}
            <div class="tiers-ref-block" data-visible-for="organisation">
                <select name="tiers_id" class="form-select @error('tiers_id') is-invalid @enderror">
                    <option value="">— Sélectionner —</option>
                    @php $liste = $tiersTypeDefault === 'fournisseur' ? $fournisseurs : $clients; @endphp
                    @foreach($liste as $t)
                        <option value="{{ $t->id }}" @selected(old('tiers_id', $facture->tiers_id ?? '') == $t->id)>
                            {{ $t->nom_affichage ?? ($t->raison_sociale ?: $t->nom) }}
                            @if($t->code) · {{ $t->code }}@endif
                        </option>
                    @endforeach
                </select>
                @error('tiers_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <small class="text-muted">Choisissez dans la liste des {{ strtolower($labelTiers) }}s enregistrés dans le CRM.</small>
            </div>

            {{-- Saisie manuelle pour tiers externe --}}
            <div class="tiers-ref-block" data-visible-for="externe">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Nom / raison sociale <span class="text-danger">*</span></label>
                        <input type="text" name="tiers_infos[nom]" class="form-control @error('tiers_infos.nom') is-invalid @enderror"
                               value="{{ $tiersInfos['nom'] ?? '' }}" placeholder="Ex : ETS DUPONT & FILS SARL">
                        @error('tiers_infos.nom')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">NIF</label>
                        <input type="text" name="tiers_infos[nif]" class="form-control" value="{{ $tiersInfos['nif'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Téléphone</label>
                        <input type="text" name="tiers_infos[telephone]" class="form-control" value="{{ $tiersInfos['telephone'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Email</label>
                        <input type="email" name="tiers_infos[email]" class="form-control" value="{{ $tiersInfos['email'] ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Adresse</label>
                        <input type="text" name="tiers_infos[adresse]" class="form-control" value="{{ $tiersInfos['adresse'] ?? '' }}">
                    </div>
                </div>
                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>
                    Les infos saisies sont conservées avec la facture pour la trace comptable.
                </small>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date d'émission <span class="text-danger">*</span></label>
                <input type="date" name="date_emission" class="form-control @error('date_emission') is-invalid @enderror"
                       value="{{ old('date_emission', $facture->date_emission?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                @error('date_emission')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Date d'échéance</label>
                <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance', $facture->date_echeance?->format('Y-m-d') ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Référence externe</label>
                <input type="text" name="reference_externe" class="form-control" maxlength="100"
                       value="{{ old('reference_externe', $facture->reference_externe ?? '') }}"
                       placeholder="N° fournisseur, devis…">
            </div>
            <div class="col-md-3">
                <label class="form-label">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">—</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(old('exercice_id', $facture->exercice_id ?? '') == $e->id)>{{ $e->libelle ?? $e->exercice }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Objet <span class="text-danger">*</span></label>
                <input type="text" name="objet" class="form-control @error('objet') is-invalid @enderror"
                       value="{{ old('objet', $facture->objet ?? '') }}" required maxlength="500">
                @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Montant HT <span class="text-danger">*</span></label>
                <input type="number" name="montant_ht" step="0.01" min="0" id="ht"
                       class="form-control @error('montant_ht') is-invalid @enderror"
                       value="{{ old('montant_ht', $facture->montant_ht ?? '') }}" required>
                @error('montant_ht')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Taxe</label>
                <input type="number" name="montant_tva" step="0.01" min="0" id="taxe"
                       class="form-control @error('montant_tva') is-invalid @enderror"
                       value="{{ old('montant_tva', $facture->montant_tva ?? 0) }}" placeholder="0">
                @error('montant_tva')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Montant total des taxes (TVA, autres…). Saisir 0 si HT = TTC.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant TTC (calculé)</label>
                <input type="text" id="ttc" class="form-control bg-light fw-bold" readonly value="{{ $isEdit ? number_format((float) $facture->montant_ttc, 2, '.', '') : '' }}">
                <small class="text-muted">HT + Total Taxe</small>
            </div>

            <div class="col-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="2" maxlength="2000">{{ old('commentaire', $facture->commentaire ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ PIÈCES JOINTES ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6>
    </div>
    <div class="card-body">
        @if($isEdit && $facture->piecesJointes && $facture->piecesJointes->isNotEmpty())
            <div class="mb-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($facture->piecesJointes as $pj)
                        @php
                            $url  = asset('storage/' . ltrim($pj->fichier, '/'));
                            $ext  = strtolower(pathinfo($pj->nom_original ?? $pj->fichier, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $isPdf = $ext === 'pdf';
                        @endphp
                        <div class="border rounded p-2 d-flex align-items-center gap-2" style="max-width:280px;">
                            @if($isImg)
                                <img src="{{ $url }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:.25rem;">
                            @elseif($isPdf)
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            @else
                                <i class="fas fa-file fa-2x text-muted"></i>
                            @endif
                            <div class="flex-grow-1 small">
                                <div class="text-truncate" title="{{ $pj->nom_original }}">{{ $pj->nom_original }}</div>
                                <a href="{{ $url }}" target="_blank" class="text-decoration-none small">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                            <form action="{{ route('finance.factures.pieces-jointes.destroy', [$facture, $pj]) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette pièce jointe ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0" title="Supprimer"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <label class="form-label small">Joindre des fichiers (PDF, images, DOC, XLS — 20 Mo max chacun)</label>
        <input type="file" id="pj-input" name="pieces_jointes[]" class="form-control" multiple
               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.zip">
        <div id="pj-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
        <small class="text-muted">Les images et PDF seront prévisualisables sur la fiche de la facture.</small>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('finance.factures.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
    <button type="submit" id="fact-submit-btn" class="btn btn-primary">
        <span class="fact-submit-label"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer la facture' }}</span>
        <span class="fact-submit-loading d-none">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span> Enregistrement…
        </span>
    </button>
</div>

<div id="fact-loading-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background:rgba(0,0,0,0.5);z-index:1080;">
    <div class="bg-white rounded p-4 text-center" style="min-width:280px;">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
        <h6 class="mb-1">Enregistrement en cours…</h6>
        <div class="text-muted small">Les fichiers peuvent prendre quelques secondes.</div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Calcul TTC = HT + Total Taxe (saisie directe, pas de % à calculer)
    const ht   = document.getElementById('ht');
    const taxe = document.getElementById('taxe');
    const ttc  = document.getElementById('ttc');
    function calcTtc() {
        const h = parseFloat(ht.value) || 0;
        const t = parseFloat(taxe.value) || 0;
        ttc.value = (h + t).toFixed(2);
    }
    [ht, taxe].forEach(el => el?.addEventListener('input', calcTtc));
    calcTtc();

    // Basculer visibilité bloc tiers selon source
    const radios = document.querySelectorAll('input[name="tiers_source"]');
    const blocs  = document.querySelectorAll('.tiers-ref-block');
    function appliquerVisibilite() {
        const sel = document.querySelector('input[name="tiers_source"]:checked')?.value;
        blocs.forEach(b => {
            b.style.display = (b.dataset.visibleFor === sel) ? '' : 'none';
        });
    }
    radios.forEach(r => r.addEventListener('change', appliquerVisibilite));
    appliquerVisibilite();

    // Preview pièces jointes avant upload
    const inputPj = document.getElementById('pj-input');
    const previewPj = document.getElementById('pj-preview');
    const IMG_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    inputPj?.addEventListener('change', function () {
        previewPj.replaceChildren();
        for (const file of this.files) {
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const card = document.createElement('div');
            card.className = 'border rounded p-2 d-flex align-items-center gap-2';
            card.style.maxWidth = '260px';
            const iconWrap = document.createElement('div');
            if (IMG_EXTS.includes(ext)) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file); img.alt = '';
                img.style.width = '48px'; img.style.height = '48px';
                img.style.objectFit = 'cover'; img.style.borderRadius = '.25rem';
                img.onload = () => URL.revokeObjectURL(img.src);
                iconWrap.appendChild(img);
            } else {
                const icon = document.createElement('i');
                icon.className = (ext === 'pdf' ? 'fas fa-file-pdf fa-2x text-danger' : 'fas fa-file fa-2x text-muted');
                iconWrap.appendChild(icon);
            }
            card.appendChild(iconWrap);
            const info = document.createElement('div');
            info.className = 'flex-grow-1 small overflow-hidden';
            const name = document.createElement('div');
            name.className = 'text-truncate fw-semibold';
            name.textContent = file.name; name.title = file.name;
            const size = document.createElement('div');
            size.className = 'text-muted';
            size.textContent = (file.size / 1024).toFixed(1) + ' Ko';
            info.appendChild(name); info.appendChild(size);
            card.appendChild(info);
            previewPj.appendChild(card);
        }
    });

    // Loading overlay au submit
    const form = document.querySelector('form');
    const overlay = document.getElementById('fact-loading-overlay');
    const submitBtn = document.getElementById('fact-submit-btn');
    if (form && overlay && submitBtn) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            overlay.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.querySelector('.fact-submit-label').classList.add('d-none');
            submitBtn.querySelector('.fact-submit-loading').classList.remove('d-none');
        });
    }
});
</script>
@endpush
