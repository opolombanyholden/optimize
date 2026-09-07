{{-- ═══════════════════════════════════════════════════════════════
     Alerte tâches personnelles — moniteur toutes les 2 min
     Affiche : tâches en retard + tâches non démarrées assignées à l'user.
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="prTachesAlertModal" tabindex="-1" aria-labelledby="prTachesAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border:1px solid #E5E7EB; border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#EEF4F3; border-bottom:1px solid #99F6E4; padding:1rem 1.5rem;">
                <div>
                    <h5 class="modal-title" id="prTachesAlertLabel" style="color:#0D9488; font-weight:800; margin:0;">
                        <i class="fas fa-list-check me-2"></i>
                        Alerte tâches — <span id="prTachesCount">0</span> action(s) requise(s)
                    </h5>
                    <p style="margin:.3rem 0 0; font-size:.78rem; color:#64748B;">
                        Vérification automatique toutes les 2 minutes.
                        <span id="prTachesTimestamp" style="opacity:.7;"></span>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Section 1 : Tâches en retard --}}
                <div id="prTachesRetardSection" style="display:none;">
                    <div style="padding:.65rem 1.25rem; background:#FEF2F2; border-bottom:1px solid #FECACA; font-weight:700; color:#DC2626; font-size:.85rem;">
                        <i class="fas fa-clock-rotate-left me-1"></i> En dépassement (<span id="prTachesRetardCount">0</span>)
                    </div>
                    <table class="table table-sm mb-0" style="font-size:.82rem;">
                        <thead style="background:#F1F5F9;">
                            <tr>
                                <th class="ps-3">Tâche</th>
                                <th>Projet</th>
                                <th class="text-end">Échéance</th>
                                <th class="text-end pe-3">Retard</th>
                            </tr>
                        </thead>
                        <tbody id="prTachesRetardTbody"></tbody>
                    </table>
                </div>

                {{-- Section 2 : Tâches non démarrées --}}
                <div id="prTachesNonDemSection" style="display:none;">
                    <div style="padding:.65rem 1.25rem; background:#FEF3C7; border-bottom:1px solid #FDE68A; font-weight:700; color:#B45309; font-size:.85rem;">
                        <i class="fas fa-hourglass-start me-1"></i> Non démarrées (<span id="prTachesNonDemCount">0</span>)
                    </div>
                    <table class="table table-sm mb-0" style="font-size:.82rem;">
                        <thead style="background:#F1F5F9;">
                            <tr>
                                <th class="ps-3">Tâche</th>
                                <th>Projet</th>
                                <th class="text-end">Début prévu</th>
                                <th class="text-end pe-3">Échéance</th>
                            </tr>
                        </thead>
                        <tbody id="prTachesNonDemTbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="background:#EEF4F3; border-top:1px solid #99F6E4; padding:.85rem 1.5rem; justify-content:space-between;">
                <a href="{{ route('intranet.taches.index', ['scopes' => ['mes']]) }}" class="btn btn-sm" style="background:#0D9488; color:#fff; font-weight:600; padding:.5rem .85rem;">
                    <i class="fas fa-list-check me-1"></i>Voir toutes mes tâches
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Plus tard</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const CHECK_URL   = @json(route('projet.alertes.taches-check'));
    const INTERVAL_MS = 2 * 60 * 1000;
    const STORAGE_KEY = 'prTachesAlertLastShown';

    let modalEl, retardTbody, retardSection, retardCount,
        nonDemTbody, nonDemSection, nonDemCount, countEl, tsEl, modal = null;

    function safeUrl(u) {
        const s = String(u ?? '');
        return /^(https?:|\/|#|mailto:)/i.test(s) ? s : '#';
    }
    function el(tag, attrs, text) {
        const n = document.createElement(tag);
        if (attrs) for (const k in attrs) n.setAttribute(k, attrs[k]);
        if (text !== undefined && text !== null) n.textContent = String(text);
        return n;
    }

    function renderRetard(items) {
        while (retardTbody.firstChild) retardTbody.removeChild(retardTbody.firstChild);
        items.forEach(t => {
            const tr = document.createElement('tr');
            const td1 = el('td', { class: 'ps-3' });
            td1.appendChild(el('a', { href: safeUrl(t.url), style: 'color:#0F172A;text-decoration:none;font-weight:600;' }, t.label));
            tr.appendChild(td1);
            tr.appendChild(el('td', { style: 'color:#64748B; font-size:.78rem;' }, t.projet || '—'));
            tr.appendChild(el('td', { class: 'text-end', style: 'color:#DC2626; font-weight:600;' }, t.date_fin || '—'));
            const jrs = Math.abs(parseInt(t.jours, 10) || 0);
            const badge = el('span', { style: 'background:#FEE2E2;color:#DC2626;font-size:.7rem;font-weight:700;padding:.15rem .45rem;border-radius:4px;' }, jrs + ' j');
            const td4 = el('td', { class: 'text-end pe-3' });
            td4.appendChild(badge);
            tr.appendChild(td4);
            retardTbody.appendChild(tr);
        });
    }

    function renderNonDem(items) {
        while (nonDemTbody.firstChild) nonDemTbody.removeChild(nonDemTbody.firstChild);
        items.forEach(t => {
            const tr = document.createElement('tr');
            const td1 = el('td', { class: 'ps-3' });
            td1.appendChild(el('a', { href: safeUrl(t.url), style: 'color:#0F172A;text-decoration:none;font-weight:600;' }, t.label));
            tr.appendChild(td1);
            tr.appendChild(el('td', { style: 'color:#64748B; font-size:.78rem;' }, t.projet || '—'));
            tr.appendChild(el('td', { class: 'text-end', style: 'color:#64748B;' }, t.date_debut || '—'));
            tr.appendChild(el('td', { class: 'text-end pe-3', style: 'color:#64748B;' }, t.date_fin || '—'));
            nonDemTbody.appendChild(tr);
        });
    }

    function render(data) {
        countEl.textContent = data.count;
        tsEl.textContent = data.checked_at ? '· Vérifié à ' + new Date(data.checked_at).toLocaleTimeString('fr-FR') : '';

        const retard = data.retard || [];
        const nonDem = data.non_demarrees || [];

        retardSection.style.display = retard.length > 0 ? '' : 'none';
        retardCount.textContent = retard.length;
        if (retard.length) renderRetard(retard);

        nonDemSection.style.display = nonDem.length > 0 ? '' : 'none';
        nonDemCount.textContent = nonDem.length;
        if (nonDem.length) renderNonDem(nonDem);
    }

    async function check(force = false) {
        if (!modal) return;
        try {
            const r = await fetch(CHECK_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!r.ok) return;
            const data = await r.json();
            if (!data || data.count === 0) return;

            const last = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            const elapsed = Date.now() - last;
            if (!force && last > 0 && elapsed < INTERVAL_MS) return;

            render(data);
            modal.show();
            localStorage.setItem(STORAGE_KEY, Date.now().toString());
        } catch (e) { /* silencieux */ }
    }

    function onReady(fn) {
        if (document.readyState !== 'loading') return fn();
        document.addEventListener('DOMContentLoaded', fn);
    }
    function whenBootstrap(fn, retries = 40) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) return fn();
        if (retries <= 0) return;
        setTimeout(function () { whenBootstrap(fn, retries - 1); }, 100);
    }
    function boot() {
        modalEl        = document.getElementById('prTachesAlertModal');
        retardSection  = document.getElementById('prTachesRetardSection');
        retardTbody    = document.getElementById('prTachesRetardTbody');
        retardCount    = document.getElementById('prTachesRetardCount');
        nonDemSection  = document.getElementById('prTachesNonDemSection');
        nonDemTbody    = document.getElementById('prTachesNonDemTbody');
        nonDemCount    = document.getElementById('prTachesNonDemCount');
        countEl        = document.getElementById('prTachesCount');
        tsEl           = document.getElementById('prTachesTimestamp');
        if (!modalEl || !retardTbody || !nonDemTbody) return;

        modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });
        check(false);
        setInterval(function () { check(false); }, INTERVAL_MS);
    }
    onReady(function () { whenBootstrap(boot); });
})();
</script>
