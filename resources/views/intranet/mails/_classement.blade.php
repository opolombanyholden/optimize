{{-- Classement d'un mail : liens vers les modules, priorité, catégorie, statut, échéance, confidentialité, note.
     Paramètres : $classement (cf. MailController::classementVue), $typesLiens (type → libellé). Champs : liens[], priorite… --}}
@php
    $idc = uniqid('cl'); // identifiant unique des champs (plusieurs formulaires possibles sur une page)
    $c = [
        'priorite'     => old('priorite', $classement['priorite']),
        'categorie'    => old('categorie', $classement['categorie']),
        'statut'       => old('statut', $classement['statut']),
        'echeance'     => old('echeance', $classement['echeance']),
        'note'         => old('note', $classement['note']),
        'confidentiel' => old('confidentiel', $classement['confidentiel']),
    ];
@endphp
<div class="mcl" data-classement>
    {{-- Liens vers les modules --}}
    <div class="mb-3">
        <label class="form-label mb-1"><i class="fas fa-link me-1 text-muted"></i>Lier ce mail à…</label>
        <div class="mcl-liens" data-liens>
            @foreach($classement['liens'] as $l)
                <span class="mcl-lien" data-cle="{{ $l['cle'] }}">
                    <i class="fas {{ $l['icon'] }}"></i>
                    <span class="mcl-lien-type">{{ $l['label'] }}</span>
                    @if($l['url'])<a href="{{ $l['url'] }}" target="_blank">{{ $l['title'] }}</a>@else{{ $l['title'] }}@endif
                    <button type="button" class="mcl-retirer" title="Retirer le lien" aria-label="Retirer"><i class="fas fa-xmark"></i></button>
                    <input type="hidden" name="liens[]" value="{{ $l['cle'] }}">
                </span>
            @endforeach
        </div>
        <div class="mcl-recherche">
            <select class="form-select form-select-sm" data-lien-type aria-label="Type d'élément">
                <option value="">Tous les modules</option>
                @foreach(\App\Services\Mail\MailLiens::GROUPES as $groupe => $types)
                    @php $dispo = array_intersect_key($typesLiens, array_flip($types)); @endphp
                    @if($dispo)
                        <optgroup label="{{ $groupe }}">
                            @foreach($dispo as $t => $lib)<option value="{{ $t }}">{{ $lib }}</option>@endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            <div class="position-relative flex-grow-1">
                <input type="search" class="form-control form-control-sm" placeholder="Rechercher un projet, une tâche, un courrier, une opportunité, une dépense…" data-lien-q autocomplete="off">
                <div class="mcl-resultats d-none" data-lien-resultats></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label mb-1">Priorité</label>
            <div class="mcl-prio">
                @foreach(\App\Models\Intranet\MailSuivi::PRIORITES as $cle => $p)
                    <input type="radio" class="btn-check" name="priorite" value="{{ $cle }}" id="prio-{{ $cle }}-{{ $idc }}" @checked($c['priorite'] === $cle) autocomplete="off">
                    <label class="mcl-prio-btn" for="prio-{{ $cle }}-{{ $idc }}" style="--c:{{ $p['couleur'] }}; --f:{{ $p['fond'] }};"><i class="fas {{ $p['icone'] }} me-1"></i>{{ $p['libelle'] }}</label>
                @endforeach
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label mb-1" for="cat-{{ $idc }}">Catégorie</label>
            <input type="text" name="categorie" id="cat-{{ $idc }}" class="form-control form-control-sm" maxlength="50" list="categories-{{ $idc }}" value="{{ $c['categorie'] }}" placeholder="Demande, Facturation, Réclamation…">
            <datalist id="categories-{{ $idc }}">@foreach(\App\Models\Intranet\MailSuivi::CATEGORIES as $cat)<option value="{{ $cat }}">@endforeach</datalist>
        </div>
        <div class="col-md-4">
            <label class="form-label mb-1" for="statut-{{ $idc }}">Suivi</label>
            <select name="statut" id="statut-{{ $idc }}" class="form-select form-select-sm">
                <option value="">—</option>
                @foreach(\App\Models\Intranet\MailSuivi::STATUTS as $cle => $lib)<option value="{{ $cle }}" @selected($c['statut'] === $cle)>{{ $lib }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label mb-1" for="ech-{{ $idc }}">Échéance <span class="text-muted small">(traiter / relancer avant)</span></label>
            <input type="date" name="echeance" id="ech-{{ $idc }}" class="form-control form-control-sm" value="{{ $c['echeance'] }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch mb-1">
                <input type="hidden" name="confidentiel" value="0">
                <input class="form-check-input" type="checkbox" name="confidentiel" value="1" id="conf-{{ $idc }}" @checked($c['confidentiel'])>
                <label class="form-check-label" for="conf-{{ $idc }}" title="Le mail n'apparaît pas aux autres utilisateurs sur les fiches liées"><i class="fas fa-user-lock me-1 text-muted"></i>Confidentiel</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label mb-1" for="note-{{ $idc }}">Note interne <span class="text-muted small">(jamais envoyée au destinataire)</span></label>
            <textarea name="note" id="note-{{ $idc }}" rows="2" class="form-control form-control-sm" maxlength="5000" placeholder="Contexte, action à mener, personne à informer…">{{ $c['note'] }}</textarea>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
.mcl .form-label { font-size:.78rem; color:#475569; font-weight:600; }
.mcl-liens { display:flex; flex-wrap:wrap; gap:.35rem; margin-bottom:.4rem; }
.mcl-liens:empty { display:none; }
.mcl-lien { display:inline-flex; align-items:center; gap:.35rem; background:#EEF2FF; color:#3730A3; border-radius:999px; padding:.2rem .3rem .2rem .65rem; font-size:.78rem; max-width:100%; }
.mcl-lien a { color:#1E1B4B; font-weight:600; text-decoration:none; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:280px; }
.mcl-lien-type { font-size:.65rem; text-transform:uppercase; letter-spacing:.04em; opacity:.75; }
.mcl-retirer { border:0; background:transparent; color:#6366F1; width:20px; height:20px; border-radius:50%; padding:0; line-height:1; }
.mcl-retirer:hover { background:#C7D2FE; color:#B91C1C; }
.mcl-recherche { display:flex; gap:.4rem; }
.mcl-recherche .form-select { width:auto; max-width:210px; }
.mcl-resultats { position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:1060; background:#fff; border:1px solid #E2E8F0; border-radius:8px; box-shadow:0 8px 24px rgba(15,23,42,.12); max-height:300px; overflow-y:auto; }
.mcl-res { display:flex; gap:.6rem; align-items:flex-start; padding:.5rem .7rem; cursor:pointer; font-size:.82rem; border-bottom:1px solid #F1F5F9; }
.mcl-res:hover, .mcl-res.actif { background:#F8FAFC; }
.mcl-res i { color:#6366F1; margin-top:.15rem; width:16px; text-align:center; }
.mcl-res small { display:block; color:#94A3B8; font-size:.72rem; }
.mcl-vide { padding:.6rem .75rem; font-size:.8rem; color:#94A3B8; }
.mcl-prio { display:flex; flex-wrap:wrap; gap:.3rem; }
.mcl-prio-btn { border:1px solid #E2E8F0; border-radius:999px; padding:.22rem .7rem; font-size:.78rem; color:#64748B; cursor:pointer; background:#fff; margin:0; }
.btn-check:checked + .mcl-prio-btn { background:var(--f); color:var(--c); border-color:var(--c); font-weight:600; }
.btn-check:focus-visible + .mcl-prio-btn { outline:2px solid var(--c); outline-offset:1px; }
</style>
@endpush

@push('scripts')
<script>
/** Sélecteur de liens mail → modules (recherche filtrée par droits côté serveur). */
document.addEventListener('DOMContentLoaded', function () {
    const URL_LIENS = @json(route('intranet.mails.liens'));
    document.querySelectorAll('[data-classement]').forEach(function (root) {
        const zone = root.querySelector('[data-liens]');
        const champ = root.querySelector('[data-lien-q]');
        const typeSel = root.querySelector('[data-lien-type]');
        const res = root.querySelector('[data-lien-resultats]');
        let timer = null, actif = -1, items = [];

        const deja = cle => !!zone.querySelector('[data-cle="' + cle + '"]');
        function ajouter(it) {
            if (deja(it.cle)) return;
            const chip = document.createElement('span'); chip.className = 'mcl-lien'; chip.dataset.cle = it.cle;
            const ic = document.createElement('i'); ic.className = 'fas ' + (it.icon || 'fa-link');
            const ty = document.createElement('span'); ty.className = 'mcl-lien-type'; ty.textContent = it.label;
            const lien = document.createElement(it.url ? 'a' : 'span'); lien.textContent = it.title; if (it.url) { lien.href = it.url; lien.target = '_blank'; }
            const rm = document.createElement('button'); rm.type = 'button'; rm.className = 'mcl-retirer'; rm.title = 'Retirer le lien'; rm.setAttribute('aria-label', 'Retirer');
            const x = document.createElement('i'); x.className = 'fas fa-xmark'; rm.appendChild(x);
            const h = document.createElement('input'); h.type = 'hidden'; h.name = 'liens[]'; h.value = it.cle;
            chip.append(ic, ty, lien, rm, h); zone.appendChild(chip);
        }
        zone.addEventListener('click', e => { const b = e.target.closest('.mcl-retirer'); if (b) b.closest('.mcl-lien').remove(); });

        function fermer() { res.classList.add('d-none'); actif = -1; }
        function afficher(liste) {
            items = liste.filter(it => !deja(it.cle)); res.textContent = ''; actif = -1;
            if (!items.length) {
                const v = document.createElement('div'); v.className = 'mcl-vide'; v.textContent = 'Aucun élément trouvé (ou accès non autorisé).'; res.appendChild(v);
            }
            items.forEach(function (it, i) {
                const d = document.createElement('div'); d.className = 'mcl-res'; d.dataset.i = i;
                const ic = document.createElement('i'); ic.className = 'fas ' + (it.icon || 'fa-link');
                const corps = document.createElement('div');
                const t = document.createElement('div'); t.textContent = it.title;
                const s = document.createElement('small'); s.textContent = it.label + (it.excerpt ? ' · ' + it.excerpt : '');
                corps.append(t, s); d.append(ic, corps);
                d.addEventListener('mousedown', e => { e.preventDefault(); ajouter(it); champ.value = ''; fermer(); });
                res.appendChild(d);
            });
            res.classList.remove('d-none');
        }
        function chercher() {
            const q = champ.value.trim(), type = typeSel.value;
            if (!type && q.length < 2) return fermer();
            const p = new URLSearchParams({ q }); if (type) p.set('type', type);
            fetch(URL_LIENS + '?' + p, { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : []).then(afficher).catch(fermer);
        }
        champ.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(chercher, 250); });
        champ.addEventListener('focus', () => { if (typeSel.value || champ.value.trim().length >= 2) chercher(); });
        champ.addEventListener('blur', () => setTimeout(fermer, 150));
        typeSel.addEventListener('change', () => { champ.focus(); chercher(); });
        champ.addEventListener('keydown', function (e) {
            const els = res.querySelectorAll('.mcl-res');
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault(); if (!els.length) return;
                actif = (actif + (e.key === 'ArrowDown' ? 1 : -1) + els.length) % els.length;
                els.forEach((el, i) => el.classList.toggle('actif', i === actif));
            } else if (e.key === 'Enter') {
                e.preventDefault(); // ne soumet pas le formulaire
                if (actif >= 0 && items[actif]) { ajouter(items[actif]); champ.value = ''; fermer(); }
            } else if (e.key === 'Escape') fermer();
        });
    });
});
</script>
@endpush
@endonce
