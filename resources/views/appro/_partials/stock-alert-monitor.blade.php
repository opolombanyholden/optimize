{{-- ═══════════════════════════════════════════════════════════════
     Stock rupture alert monitor
     Visible pour les gestionnaires Achats/MG + admins uniquement.
     Affiche un pop-up d'alerte immédiatement à l'entrée dans l'espace
     (si dernier affichage > 5 min) puis toutes les 5 min tant que la
     personne reste dans /appro/*, /mg/* ou /referentiel/catalogue/*.
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="apStockMonitorModal" tabindex="-1" aria-labelledby="apStockMonitorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border:1px solid #E5E7EB; border-radius:8px; overflow:hidden;">
            <div class="modal-header" style="background:#FEF2F2; border-bottom:1px solid #FCA5A5; padding:1rem 1.5rem;">
                <div>
                    <h5 class="modal-title" id="apStockMonitorLabel" style="color:#DC2626; font-weight:800; margin:0;">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        Alerte rupture — <span id="apStockMonitorCount">0</span> article(s)
                    </h5>
                    <p style="margin:.3rem 0 0; font-size:.78rem; color:#64748B;">
                        Vérification automatique toutes les 2 minutes.
                        <span id="apStockMonitorTimestamp" style="opacity:.7;"></span>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm mb-0" style="font-size:.8rem;">
                    <thead style="background:#F1F5F9;">
                        <tr>
                            <th class="ps-3">Article</th>
                            <th class="text-end">Stock actuel</th>
                            <th class="text-end">Seuil alerte</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody id="apStockMonitorTbody">
                        {{-- Rempli dynamiquement --}}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" style="background:#FEF2F2; border-top:1px solid #FCA5A5; padding:.85rem 1.5rem; justify-content:space-between;">
                <a href="#" id="apStockMonitorCommande" class="btn btn-sm" style="background:#D97706; color:#fff; font-weight:600; padding:.5rem .85rem;">
                    <i class="fas fa-cart-plus me-1"></i>Créer une commande fournisseur
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const CHECK_URL   = @json(route('appro.stock.rupture-check'));
    const INTERVAL_MS = 2 * 60 * 1000;           // 2 minutes
    const STORAGE_KEY = 'apStockAlertLastShown'; // timestamp dernier affichage effectif (partagé entre onglets)

    // Références DOM et modale — initialisées dans boot() (après DOMContentLoaded + bootstrap dispo)
    let modalEl, tbody, countEl, tsEl, cmdBtn, modal;

    function fmt(n) {
        return String(n).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1 ');
    }
    // Empêche les URL javascript:/data: dans href (défense en profondeur)
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

    function render(data) {
        countEl.textContent = data.count;
        cmdBtn.setAttribute('href', safeUrl(data.commande_url));
        tsEl.textContent = data.checked_at ? '· Vérifié à ' + new Date(data.checked_at).toLocaleTimeString('fr-FR') : '';

        // Vide le tbody proprement (sans innerHTML)
        while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

        (data.articles || []).forEach(a => {
            const editUrl = safeUrl(a.url_edit);
            const tr = document.createElement('tr');

            // Colonne 1 : lien vers l'article
            const td1  = el('td', { class: 'ps-3' });
            const link = el('a', { href: editUrl, style: 'color:#0F172A;text-decoration:none;font-weight:600;' }, a.designation);
            td1.appendChild(link);
            tr.appendChild(td1);

            // Colonne 2 : stock actuel + unité + badge éventuel
            const td2   = el('td', { class: 'text-end' });
            const stock = el('span', { style: 'font-weight:700;color:' + (a.rupture ? '#DC2626' : '#D97706') + ';' }, fmt(a.stock));
            const unit  = el('span', { style: 'color:#94A3B8;font-size:.72rem;margin-left:.25rem;' }, a.unite || '');
            td2.append(stock, unit);
            if (a.rupture) {
                td2.appendChild(el('span',
                    { style: 'display:inline-block;font-size:.62rem;padding:.1rem .4rem;border-radius:4px;background:#FEE2E2;color:#DC2626;font-weight:700;margin-left:.35rem;' },
                    'RUPTURE'));
            }
            tr.appendChild(td2);

            // Colonne 3 : seuil
            tr.appendChild(el('td', { class: 'text-end', style: 'color:#64748B;' }, fmt(a.seuil)));

            // Colonne 4 : bouton réappro
            const td4 = el('td', { class: 'text-end pe-3' });
            const btn = el('a',
                { href: editUrl + '#tab-stock',
                  style: 'background:#DC2626;color:#fff;font-size:.68rem;font-weight:600;padding:.28rem .55rem;border-radius:4px;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;' },
                'Réappro.');
            const icon = el('i', { class: 'fas fa-arrow-up' });
            btn.insertBefore(icon, btn.firstChild);
            td4.appendChild(btn);
            tr.appendChild(td4);

            tbody.appendChild(tr);
        });
    }

    async function check(force = false) {
        if (!modal) return; // boot() pas encore terminé
        try {
            const r = await fetch(CHECK_URL, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!r.ok) return; // 403 pour non-autorisés → silencieux
            const data = await r.json();
            if (!data || data.count === 0) return;

            const last = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            const elapsed = Date.now() - last;
            if (!force && last > 0 && elapsed < INTERVAL_MS) return; // pas encore l'heure

            render(data);
            modal.show();
            localStorage.setItem(STORAGE_KEY, Date.now().toString());
        } catch (e) { /* silencieux */ }
    }

    // ── Boot différé : attend le DOM parsé + bootstrap chargé ────────────────
    function onReady(fn) {
        if (document.readyState !== 'loading') return fn();
        document.addEventListener('DOMContentLoaded', fn);
    }
    function whenBootstrap(fn, retries = 40) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) return fn();
        if (retries <= 0) return; // abandon silencieux après ~4s
        setTimeout(function () { whenBootstrap(fn, retries - 1); }, 100);
    }
    function boot() {
        modalEl = document.getElementById('apStockMonitorModal');
        tbody   = document.getElementById('apStockMonitorTbody');
        countEl = document.getElementById('apStockMonitorCount');
        tsEl    = document.getElementById('apStockMonitorTimestamp');
        cmdBtn  = document.getElementById('apStockMonitorCommande');
        if (!modalEl || !tbody || !countEl || !cmdBtn) return;

        modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: true });

        // Check immédiat à l'arrivée sur la page (respecte le délai 2 min via localStorage)
        check(false);
        // Puis polling toutes les 2 min tant que l'onglet reste ouvert
        setInterval(function () { check(false); }, INTERVAL_MS);
    }
    onReady(function () { whenBootstrap(boot); });
})();
</script>
