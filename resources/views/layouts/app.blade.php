<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — OptimiZe ERP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    {{-- Restaure l'état réduit de la sidebar AVANT le rendu (évite le flash) --}}
    <script>
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    @auth
        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Overlay mobile --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- Top Navbar --}}
        @include('layouts.partials.navbar')

        {{-- Main Content --}}
        <main class="main-content">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Erreurs de validation :</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    @else
        {{-- Guest layout (login, register) --}}
        <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            @yield('content')
        </div>
    @endauth

    @auth
    {{-- Workspace picker global — dispo partout, ouvrable via window.openWorkspacePicker() --}}
    @include('layouts.partials.workspace-picker')

    {{-- Moniteur d'alerte rupture de stock — visible pour les gestionnaires
         Achats/MG + admins, uniquement dans l'espace Achats & Moyens Généraux --}}
    @canany(['update:produit', 'update:commande', 'update:dysfonctionnement'])
        @if(request()->routeIs('appro.*', 'mg.*', 'referentiel.catalogue.*'))
            @include('appro._partials.stock-alert-monitor')
        @endif
    @endcanany

    {{-- Moniteur d'alerte tâches perso — visible pour tout utilisateur ayant
         accès au module projet, uniquement dans l'espace Gestion de Projet --}}
    @can('read:tache_intranet')
        @if(request()->routeIs('projet.*', 'intranet.projets.*', 'intranet.taches.*', 'intranet.rapports.*'))
            @include('projet._partials.taches-alert-monitor')
        @endif
    @endcan

    {{-- ════════════════════════════════════════════════════
         MESSAGERIE INSTANTANÉE — BOUTON FLOTTANT
    ════════════════════════════════════════════════════ --}}
    {{-- Masqué sur la page Messagerie (déjà plein écran, évite de recouvrir le champ de saisie) --}}
    @unless(request()->routeIs('intranet.messagerie.*', 'social.groups.show'))
    <div id="chatWidget">

        {{-- Panneau principal --}}
        <div class="chat-panel" id="chatPanel">

            {{-- En-tête --}}
            <div class="chat-panel-header">
                <div class="chat-panel-title">
                    <span class="chat-panel-title-icon"><i class="fas fa-message"></i></span>
                    <span>Messagerie</span>
                </div>
                <div class="chat-panel-actions">
                    <button class="chat-action-btn" id="chatToggleView" title="Nouvelle conversation">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                    <button class="chat-action-btn" id="chatClose" title="Fermer">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- Recherche --}}
            <div class="chat-search-wrap">
                <i class="fas fa-magnifying-glass chat-search-ico"></i>
                <input type="text" class="chat-search-input" id="chatSearch"
                       placeholder="Rechercher un collaborateur…">
            </div>

            {{-- Utilisateurs connectés --}}
            <div class="chat-section-label">
                <span class="chat-online-dot"></span>
                En ligne
                <span class="chat-online-count" id="chatOnlineCount">—</span>
            </div>
            <ul class="chat-user-list" id="chatOnlineList">
                {{-- Peuplé dynamiquement --}}
                <li class="chat-user-skeleton"></li>
                <li class="chat-user-skeleton" style="opacity:.6;"></li>
            </ul>

            {{-- Tous les utilisateurs --}}
            <div class="chat-section-label" style="margin-top:.25rem;">
                <i class="fas fa-users" style="font-size:.6rem;color:#94A3B8;"></i>
                Tous
            </div>
            <ul class="chat-user-list" id="chatAllList">
                <li class="chat-user-skeleton"></li>
                <li class="chat-user-skeleton" style="opacity:.6;"></li>
                <li class="chat-user-skeleton" style="opacity:.4;"></li>
            </ul>
        </div>

        {{-- Fenêtres de conversation (générées dynamiquement) --}}
        <div id="chatWindows"></div>

        {{-- Bouton flottant --}}
        <button class="chat-fab" id="chatFab" title="Messagerie instantanée">
            <i class="fas fa-message chat-fab-icon-open"></i>
            <i class="fas fa-xmark chat-fab-icon-close d-none"></i>
            <span class="chat-fab-badge d-none" id="chatFabBadge">0</span>
            <span class="chat-fab-online-dot" id="chatFabOnlineDot"></span>
        </button>

    </div>{{-- /chatWidget --}}
    @endunless

    {{-- ════════════════════════════════════════════════════
         RÉSEAU SOCIAL — Bouton flottant vers le module Social
         (masqué quand on est déjà dans le module social)
    ════════════════════════════════════════════════════ --}}
    @unless(request()->routeIs('social.*', 'intranet.messagerie.*'))
    <a href="{{ route('social.dashboard') }}" class="rs-fab" title="Réseau social">
        <i class="fas fa-users"></i>
        <span class="rs-fab-badge d-none" id="rsFabBadgeSocial">0</span>
    </a>
    @endunless

    {{-- Bouton flottant : accès direct à la liste des Tâches
         (remplace l'ancien FAB Groupes de discussion) --}}
    @unless(request()->routeIs('intranet.taches.*', 'projet.*', 'intranet.messagerie.*', 'social.groups.show'))
    <a href="{{ route('intranet.taches.index') }}" class="rs-fab rs-fab-taches" title="Mes tâches">
        <i class="fas fa-list-check"></i>
    </a>
    @endunless

    <style>
    .rs-fab {
        position: fixed; bottom: 6rem; right: 1.5rem; z-index: 1055;
        width: 48px; height: 48px; border-radius: 50%; text-decoration: none;
        background: #0A66C2; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
        transition: background .15s;
    }
    .rs-fab:hover { background: #004182; color: #fff; }
    .rs-fab-taches { bottom: 10rem; background: #0D9488; color: #fff; }
    .rs-fab-taches:hover { background: #0F766E; color: #fff; }
    .rs-fab-badge {
        position: absolute; top: -4px; right: -4px;
        min-width: 20px; height: 20px; padding: 0 5px;
        background: #DC2626; color: #fff;
        border-radius: 10px; border: 2px solid #fff;
        font-size: .68rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .rs-fab { position: fixed; } /* garantir la référence pour le badge absolute */
    </style>

    @auth
    <script>
    (function () {
        function refreshFabBadges() {
            fetch("{{ route('social.badges') }}", { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    // Le FAB Groupes a été remplacé par le FAB Tâches (sans badge dynamique).
                    // Seul le badge Social reste actif.
                    var el = document.getElementById('rsFabBadgeSocial');
                    if (!el) return;
                    var n = parseInt(data.social, 10) || 0;
                    if (n <= 0) el.classList.add('d-none');
                    else { el.textContent = n > 99 ? '99+' : String(n); el.classList.remove('d-none'); }
                }).catch(function () {});
        }
        document.addEventListener('DOMContentLoaded', refreshFabBadges);
        // Rafraîchit toutes les 60 s
        setInterval(refreshFabBadges, 60000);
    })();
    </script>
    @endauth
    @endauth

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }

            // ── Toggle desktop : réduire / étendre la sidebar (persisté) ──
            const desktopToggle = document.getElementById('sidebarDesktopToggle');
            const SB_KEY = 'sidebarCollapsed';
            if (desktopToggle) {
                desktopToggle.addEventListener('click', function () {
                    const collapsed = document.body.classList.toggle('sidebar-collapsed');
                    try { localStorage.setItem(SB_KEY, collapsed ? '1' : '0'); } catch (e) {}
                });
                // Si l'utilisateur clique sur un accordéon alors que la sidebar est réduite,
                // on l'étend d'abord (sinon le collapse Bootstrap n'a pas d'effet visible)
                document.querySelectorAll('.sb-acc-toggle').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        if (document.body.classList.contains('sidebar-collapsed')) {
                            document.body.classList.remove('sidebar-collapsed');
                            try { localStorage.setItem(SB_KEY, '0'); } catch (e) {}
                        }
                    }, true); // capture pour tourner AVANT Bootstrap
                });
            }

            // Submenu toggle
            document.querySelectorAll('.sidebar-toggle').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.dataset.bsTarget);
                    if (target) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(target);
                        bsCollapse.toggle();
                    }
                });
            });

            // ── Recherche navbar ──────────────────────────────
            const searchToggle = document.getElementById('navSearchToggle');
            const searchBox    = document.getElementById('navSearchBox');
            const searchInput  = document.getElementById('navSearchInput');

            if (searchToggle && searchBox) {
                searchToggle.addEventListener('click', function () {
                    const isOpen = searchBox.classList.toggle('open');
                    if (isOpen) searchInput.focus();
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        searchBox.classList.remove('open');
                        searchInput.value = '';
                    }
                });
                document.addEventListener('click', function (e) {
                    if (!searchBox.contains(e.target) && e.target !== searchToggle) {
                        searchBox.classList.remove('open');
                    }
                });
            }

            // ── Compteurs messagerie (barre du haut + sidebar), toutes les 60 s ──
            (function () {
                function maj(cle, n) {
                    const nav = document.getElementById(cle === 'mail' ? 'navMailCount' : 'navChatCount');
                    [nav, ...document.querySelectorAll('[data-badge="' + cle + '"]')].forEach(function (el) {
                        if (!el) return;
                        el.textContent = n > 99 ? '99+' : n;
                        el.classList.toggle('d-none', !n);
                    });
                    const sub = document.getElementById(cle === 'mail' ? 'navMailSub' : 'navChatSub');
                    if (sub && n) sub.textContent = n + (cle === 'mail' ? ' mail(s) non lu(s)' : ' message(s) non lu(s)');
                    totaux[cle] = n;
                    document.getElementById('navMsgDot')?.classList.toggle('d-none', !(totaux.mail || totaux.chat));
                }
                const totaux = { mail: 0, chat: 0 };
                const sources = { chat: @json(route('intranet.messagerie.non-lus')), mail: @json(route('intranet.mails.non-lus')) };
                function rafraichir() {
                    if (document.hidden) return;
                    Object.entries(sources).forEach(function ([cle, url]) {
                        fetch(url, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.ok ? r.json() : null)
                            .then(d => { if (d) maj(cle, parseInt(d.total, 10) || 0); })
                            .catch(function () {});
                    });
                }
                rafraichir();
                setInterval(rafraichir, 60000);
            })();
            });

            // ── Chat Widget (messagerie instantanée 1-à-1, polling) ──
            (function () {
                const fab      = document.getElementById('chatFab');
                const panel    = document.getElementById('chatPanel');
                const closeBtn = document.getElementById('chatClose');
                const search   = document.getElementById('chatSearch');
                const onlineList = document.getElementById('chatOnlineList');
                const allList    = document.getElementById('chatAllList');
                const onlineCount = document.getElementById('chatOnlineCount');
                const onlineDot   = document.getElementById('chatFabOnlineDot');
                const fabBadge    = document.getElementById('chatFabBadge');
                const windows  = document.getElementById('chatWindows');
                const newBtn   = document.getElementById('chatToggleView');

                if (!fab) return;

                const URL_USERS   = @json(route('intranet.messagerie.utilisateurs'));
                const URL_PAGE    = @json(route('intranet.messagerie.index'));
                const URL_START   = @json(route('intranet.messagerie.demarrer', ['user' => '__ID__']));
                const URL_MSGS    = @json(route('intranet.messagerie.messages', ['conversation' => '__ID__']));
                const URL_SEND    = @json(route('intranet.messagerie.envoyer', ['conversation' => '__ID__']));
                const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
                const JSON_HEADERS = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF };

                // Couleurs avatar déterministes
                const avatarColors = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
                function avatarColor(name) {
                    let h = 0;
                    for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) & 0xffffffff;
                    return avatarColors[Math.abs(h) % avatarColors.length];
                }
                function initials(name) {
                    return name.split(' ').filter(Boolean).map(p => p[0]).join('').substring(0, 2).toUpperCase();
                }
                function esc(s) {
                    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                }

                let users = [];
                const openWindows = {}; // userId → { conversationId, lastId }

                function loadUsers() {
                    return fetch(URL_USERS, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(data => { users = data; renderUsers(); })
                        .catch(() => {});
                }

                function renderUsers() {
                    const q = search ? search.value.trim().toLowerCase() : '';
                    const match = u => !q || u.name.toLowerCase().includes(q);
                    // Conversations récentes d'abord, puis ordre alphabétique
                    const tri = (a, b) => (b.last_message_at || '').localeCompare(a.last_message_at || '') || a.name.localeCompare(b.name);
                    const online  = users.filter(u => u.online).sort(tri);
                    const offline = users.filter(u => !u.online).sort(tri);

                    onlineCount.textContent = online.length;
                    onlineDot.style.display = online.length > 0 ? 'block' : 'none';
                    onlineList.innerHTML = online.filter(match).map(itemHtml).join('') || '<li class="chat-empty">Aucun collaborateur en ligne</li>';
                    allList.innerHTML    = offline.filter(match).map(itemHtml).join('') || '<li class="chat-empty">Aucun autre collaborateur</li>';

                    const total = users.reduce((s, u) => s + (u.unread || 0), 0);
                    fabBadge.textContent = total > 99 ? '99+' : total;
                    fabBadge.classList.toggle('d-none', total === 0);
                }

                function itemHtml(u) {
                    return `
                        <li class="chat-user-item" data-id="${u.id}">
                            <div class="chat-user-avatar" style="background:${avatarColor(u.name)};">
                                ${esc(initials(u.name))}
                                <span class="chat-status-dot ${u.online ? 'online' : 'offline'}"></span>
                            </div>
                            <div class="chat-user-info">
                                <div class="chat-user-name">${esc(u.name)}</div>
                                <div class="chat-user-role">${esc(u.role)}</div>
                            </div>
                            <div class="chat-user-unread ${u.unread ? '' : 'd-none'}">${u.unread || 0}</div>
                        </li>`;
                }

                function togglePanel(force) {
                    const isOpen = panel.classList.toggle('open', force);
                    fab.querySelector('.chat-fab-icon-open').classList.toggle('d-none', isOpen);
                    fab.querySelector('.chat-fab-icon-close').classList.toggle('d-none', !isOpen);
                    if (isOpen) loadUsers();
                }
                fab.addEventListener('click', () => togglePanel());
                closeBtn.addEventListener('click', () => togglePanel(false));
                if (newBtn) newBtn.addEventListener('click', () => { window.location.href = URL_PAGE; });
                if (search) search.addEventListener('input', renderUsers);

                document.addEventListener('click', function (e) {
                    const item = e.target.closest('.chat-user-item');
                    if (!item) return;
                    const u = users.find(x => String(x.id) === item.dataset.id);
                    if (u) openChatWindow(u);
                });

                function openChatWindow(u) {
                    if (openWindows[u.id]) {
                        document.getElementById('chatWin-' + u.id)?.classList.remove('minimized');
                        return;
                    }
                    openWindows[u.id] = { conversationId: u.conversation_id, lastId: 0 };
                    const color = avatarColor(u.name);
                    const right = 80 + (Object.keys(openWindows).length - 1) * 310;

                    const win = document.createElement('div');
                    win.className = 'chat-window';
                    win.id = 'chatWin-' + u.id;
                    win.style.right = right + 'px';
                    win.innerHTML = `
                        <div class="chat-win-header" style="background:${color};">
                            <div class="chat-win-identity">
                                <div class="chat-win-avatar">${esc(initials(u.name))}
                                    <span class="chat-status-dot ${u.online ? 'online' : 'offline'}" style="border-color:${color};"></span>
                                </div>
                                <div>
                                    <div class="chat-win-name">${esc(u.name)}</div>
                                    <div class="chat-win-status">${u.online ? '<span class="chat-online-label">En ligne</span>' : 'Hors ligne'}</div>
                                </div>
                            </div>
                            <div class="chat-win-btns">
                                <button class="chat-win-btn" data-action="page" title="Ouvrir dans la messagerie"><i class="fas fa-up-right-from-square"></i></button>
                                <button class="chat-win-btn" data-action="min" title="Réduire"><i class="fas fa-minus"></i></button>
                                <button class="chat-win-btn" data-action="close" title="Fermer"><i class="fas fa-xmark"></i></button>
                            </div>
                        </div>
                        <div class="chat-win-body">
                            <div class="chat-win-empty"><i class="fas fa-message"></i><span>Démarrez la conversation</span></div>
                        </div>
                        <div class="chat-win-footer">
                            <input type="text" class="chat-win-input" maxlength="4000" placeholder="Écrire un message…">
                            <button class="chat-win-send" title="Envoyer"><i class="fas fa-paper-plane"></i></button>
                        </div>`;
                    windows.appendChild(win);

                    win.querySelector('[data-action="min"]').onclick = () => win.classList.toggle('minimized');
                    win.querySelector('[data-action="close"]').onclick = () => { win.remove(); delete openWindows[u.id]; };
                    win.querySelector('[data-action="page"]').onclick = () => ensureConversation(u).then(id => {
                        window.location.href = URL_PAGE + '/conversations/' + id;
                    });
                    const input = win.querySelector('.chat-win-input');
                    const send = () => sendChatMsg(u, input);
                    win.querySelector('.chat-win-send').onclick = send;
                    input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
                    input.focus();

                    if (u.conversation_id) pollWindow(u.id);
                }

                function ensureConversation(u) {
                    const state = openWindows[u.id];
                    if (state && state.conversationId) return Promise.resolve(state.conversationId);
                    return fetch(URL_START.replace('__ID__', u.id), { method: 'POST', headers: JSON_HEADERS })
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(data => {
                            u.conversation_id = data.conversation_id;
                            if (openWindows[u.id]) openWindows[u.id].conversationId = data.conversation_id;
                            return data.conversation_id;
                        });
                }

                function appendBubble(userId, m) {
                    const state = openWindows[userId];
                    const body = document.querySelector('#chatWin-' + userId + ' .chat-win-body');
                    if (!state || !body || m.id <= state.lastId) return;
                    body.querySelector('.chat-win-empty')?.remove();
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble ' + (m.mine ? 'mine' : 'theirs');
                    const txt = document.createElement('div'); txt.className = 'chat-bubble-text'; txt.textContent = m.contenu;
                    const time = document.createElement('div'); time.className = 'chat-bubble-time'; time.textContent = m.heure;
                    bubble.append(txt, time);
                    body.appendChild(bubble);
                    body.scrollTop = body.scrollHeight;
                    state.lastId = m.id;
                }

                function pollWindow(userId) {
                    const state = openWindows[userId];
                    if (!state || !state.conversationId) return Promise.resolve();
                    const url = URL_MSGS.replace('__ID__', state.conversationId) + (state.lastId ? '?apres=' + state.lastId : '');
                    return fetch(url, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : null)
                        .then(data => {
                            if (!data) return;
                            data.messages.forEach(m => appendBubble(userId, m));
                            const u = users.find(x => x.id === userId);
                            if (u && u.unread) { u.unread = 0; renderUsers(); }
                        }).catch(() => {});
                }

                function sendChatMsg(u, input) {
                    const contenu = input.value.trim();
                    if (!contenu) return;
                    input.value = '';
                    ensureConversation(u)
                        .then(id => fetch(URL_SEND.replace('__ID__', id), { method: 'POST', headers: JSON_HEADERS, body: JSON.stringify({ contenu }) }))
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(data => appendBubble(u.id, data.message))
                        .catch(() => { input.value = contenu; });
                }

                // Polling : fenêtres ouvertes toutes les 4 s, liste + badge toutes les 30 s
                setInterval(() => { if (!document.hidden) Object.keys(openWindows).forEach(id => pollWindow(+id)); }, 4000);
                setInterval(() => { if (!document.hidden) loadUsers(); }, 30000);

                loadUsers();
            })();
        });
    </script>

    @stack('scripts')
</body>
</html>
