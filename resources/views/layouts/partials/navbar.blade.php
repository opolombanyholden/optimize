{{-- ============================================================
     TOP NAVBAR — Contextuel selon le module actif
     ============================================================ --}}

@php
    $navMod = 'portal';
    if      (request()->routeIs('finance.*'))    $navMod = 'finance';
    elseif  (request()->routeIs('rh.*'))         $navMod = 'rh';
    elseif  (request()->routeIs('appro.*'))      $navMod = 'appro';
    elseif  (request()->routeIs('mg.*'))         $navMod = 'mg';
    elseif  (request()->routeIs('projet.*'))     $navMod = 'projet';
    elseif  (request()->routeIs('intranet.projets.*'))  $navMod = 'projet';
    elseif  (request()->routeIs('intranet.taches.*'))   $navMod = 'projet';
    elseif  (request()->routeIs('intranet.rapports.*')) $navMod = 'projet';
    elseif  (request()->routeIs('objectifs.*'))  $navMod = 'objectifs';
    elseif  (request()->routeIs('systeme.*'))    $navMod = 'admin';

    $navModules = [
        'finance'   => ['label' => 'Finance & Budget',          'color' => '#4F46E5', 'bg' => '#EEF2FF', 'icon' => 'fa-coins'],
        'rh'        => ['label' => 'RH & Paiement',             'color' => '#059669', 'bg' => '#ECFDF5', 'icon' => 'fa-users'],
        'appro'     => ['label' => 'Achat & Appro',             'color' => '#D97706', 'bg' => '#FFFBEB', 'icon' => 'fa-cart-flatbed'],
        'mg'        => ['label' => 'Moyens Généraux',           'color' => '#0891B2', 'bg' => '#ECFEFF', 'icon' => 'fa-building'],
        'projet'    => ['label' => 'Projets / Tâches',           'color' => '#0D9488', 'bg' => '#F0FDFA', 'icon' => 'fa-diagram-project'],
        'objectifs' => ['label' => 'Objectifs & KPI',           'color' => '#DB2777', 'bg' => '#FDF2F8', 'icon' => 'fa-bullseye'],
        'admin'     => ['label' => 'Administration',            'color' => '#475569', 'bg' => '#F8FAFC', 'icon' => 'fa-shield-halved'],
        'portal'    => ['label' => 'Intranet · Portail',        'color' => '#7C3AED', 'bg' => '#F5F3FF', 'icon' => 'fa-home'],
    ];
    $currentMod = $navModules[$navMod];
@endphp

<header class="top-navbar" id="topNavbar" data-module="{{ $navMod }}">

    {{-- Toggle button mobile --}}
    <button class="btn btn-link text-dark d-lg-none me-2 p-0" id="sidebarToggle">
        <i class="fas fa-bars fa-lg"></i>
    </button>

    {{-- Module badge + breadcrumb --}}
    <div class="navbar-context d-flex align-items-center gap-2">
        {{-- Module pill --}}
        <div class="navbar-module-pill" style="background:{{ $currentMod['bg'] }}; color:{{ $currentMod['color'] }};">
            <i class="fas {{ $currentMod['icon'] }}"></i>
            <span>{{ $currentMod['label'] }}</span>
        </div>
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            @yield('breadcrumb')
        </nav>
    </div>

    {{-- Droite --}}
    <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">

        {{-- Recherche --}}
        <div class="navbar-search-wrap d-none d-md-flex" id="navSearchWrap">
            <button class="navbar-icon-btn" id="navSearchToggle" title="Rechercher" aria-label="Rechercher">
                <i class="fas fa-magnifying-glass"></i>
            </button>
            <div class="navbar-search-box" id="navSearchBox">
                <i class="fas fa-magnifying-glass navbar-search-ico"></i>
                <input type="text" class="navbar-search-input" id="navSearchInput"
                       placeholder="Rechercher…" autocomplete="off">
                <kbd class="navbar-search-kbd">Esc</kbd>
            </div>
        </div>

        {{-- Création rapide --}}
        <div class="dropdown">
            <button class="navbar-icon-btn navbar-btn-create" data-bs-toggle="dropdown"
                    title="Création rapide" aria-label="Création rapide">
                <i class="fas fa-plus"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown navbar-create-menu" style="width:240px;">
                <div class="navbar-dropdown-header">Création rapide</div>

                <div class="navbar-create-section">Intranet</div>
                <a class="navbar-dropdown-item" href="{{ route('intranet.annonces.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#F5F3FF;color:#7C3AED;"><i class="fas fa-bullhorn"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouvelle annonce</div></div>
                </a>
                <a class="navbar-dropdown-item" href="{{ route('intranet.evenements.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#F5F3FF;color:#7C3AED;"><i class="fas fa-calendar-plus"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouvel événement</div></div>
                </a>
                <a class="navbar-dropdown-item" href="{{ route('intranet.projets.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#F5F3FF;color:#7C3AED;"><i class="fas fa-diagram-project"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouveau projet</div></div>
                </a>
                <a class="navbar-dropdown-item" href="{{ route('intranet.taches.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#F5F3FF;color:#7C3AED;"><i class="fas fa-list-check"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouvelle tâche</div></div>
                </a>

                <div style="height:1px;background:#F1F5F9;margin:.3rem .5rem;"></div>
                <div class="navbar-create-section">ERP</div>
                @canany(['read:exercice','read:budget'])
                <a class="navbar-dropdown-item" href="{{ route('finance.budgets.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#EEF2FF;color:#4F46E5;"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div><div class="navbar-dropdown-item-title">Saisie budgétaire</div></div>
                </a>
                @endcanany
                @can('read:employee')
                <a class="navbar-dropdown-item" href="{{ route('rh.employees.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#ECFDF5;color:#059669;"><i class="fas fa-user-plus"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouvel employé</div></div>
                </a>
                @endcan
                @can('read:commande')
                <a class="navbar-dropdown-item" href="{{ route('appro.commandes.create') }}">
                    <span class="navbar-dropdown-icon" style="background:#FFFBEB;color:#D97706;"><i class="fas fa-file-invoice"></i></span>
                    <div><div class="navbar-dropdown-item-title">Nouvelle commande</div></div>
                </a>
                @endcan
            </div>
        </div>

        {{-- Messagerie --}}
        <div class="dropdown">
            <button class="navbar-icon-btn position-relative" data-bs-toggle="dropdown"
                    title="Messagerie" aria-label="Messagerie">
                <i class="fas fa-envelope"></i>
                @if(!empty($mailsNonLus) && $mailsNonLus > 0)
                <span class="navbar-notif-dot" style="background:#F87171;"></span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown" style="width:300px;">
                <div class="navbar-dropdown-header">
                    <span>Messagerie</span>
                    @if(!empty($mailsNonLus) && $mailsNonLus > 0)
                    <span class="navbar-notif-count" style="background:#FEE2E2;color:#DC2626;">{{ $mailsNonLus }}</span>
                    @endif
                </div>
                {{-- Onglets Mail / Chat --}}
                <div class="navbar-msg-tabs">
                    <button class="navbar-msg-tab active" data-tab="mail">
                        <i class="fas fa-at"></i> Mail pro
                        @if(!empty($mailsNonLus) && $mailsNonLus > 0)
                        <span class="navbar-msg-count">{{ $mailsNonLus }}</span>
                        @endif
                    </button>
                    <button class="navbar-msg-tab" data-tab="chat">
                        <i class="fas fa-message"></i> Instantané
                    </button>
                </div>
                {{-- Contenu Mail --}}
                <div class="navbar-msg-panel" id="msgPanelMail">
                    @if(!empty($mailsNonLus) && $mailsNonLus > 0)
                    <a class="navbar-dropdown-item" href="#" style="opacity:.55;cursor:not-allowed;" title="Bientôt">
                        <span class="navbar-dropdown-icon" style="background:#FEE2E2;color:#DC2626;"><i class="fas fa-envelope-open-text"></i></span>
                        <div>
                            <div class="navbar-dropdown-item-title">{{ $mailsNonLus }} mail(s) non lu(s)</div>
                            <div class="navbar-dropdown-item-sub">Messagerie professionnelle</div>
                        </div>
                    </a>
                    @else
                    <div class="text-center py-3 text-muted" style="font-size:.82rem;">
                        <i class="fas fa-inbox d-block mb-1" style="font-size:1.2rem;"></i>
                        Aucun mail non lu
                    </div>
                    @endif
                    <div class="navbar-dropdown-footer">
                        <span style="opacity:.55;font-size:.7rem;">Module messagerie bientôt disponible</span>
                    </div>
                </div>
                {{-- Contenu Chat --}}
                <div class="navbar-msg-panel d-none" id="msgPanelChat">
                    <div class="text-center py-3 text-muted" style="font-size:.82rem;">
                        <i class="fas fa-message d-block mb-1" style="font-size:1.2rem;"></i>
                        Messagerie instantanée
                        <div style="font-size:.75rem;margin-top:.25rem;">Bientôt disponible</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outils ERP --}}
        <div class="dropdown">
            <button class="navbar-icon-btn" data-bs-toggle="dropdown"
                    title="Modules ERP" aria-label="Modules ERP">
                <i class="fas fa-grip"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown navbar-tools-menu" style="width:260px;">
                <div class="navbar-dropdown-header">Modules ERP</div>
                <div class="navbar-tools-grid">
                    <a href="{{ route('dashboard') }}" class="navbar-tool-btn {{ $navMod === 'portal' ? 'active' : '' }}" style="--tool-color:#7C3AED;">
                        <span class="navbar-tool-icon"><i class="fas fa-home"></i></span>
                        <span class="navbar-tool-label">Portail</span>
                    </a>
                    @canany(['read:exercice','read:budget','read:grandlivre','read:compte'])
                    <a href="{{ route('finance.exercices.index') }}" class="navbar-tool-btn {{ $navMod === 'finance' ? 'active' : '' }}" style="--tool-color:#4F46E5;">
                        <span class="navbar-tool-icon"><i class="fas fa-coins"></i></span>
                        <span class="navbar-tool-label">Finance</span>
                    </a>
                    @endcanany
                    @canany(['read:employee','read:absence','read:paie','read:recrutement'])
                    <a href="{{ route('rh.employees.index') }}" class="navbar-tool-btn {{ $navMod === 'rh' ? 'active' : '' }}" style="--tool-color:#059669;">
                        <span class="navbar-tool-icon"><i class="fas fa-users"></i></span>
                        <span class="navbar-tool-label">RH</span>
                    </a>
                    @endcanany
                    @canany(['read:fournisseur','read:produit','read:commande'])
                    <a href="{{ route('appro.fournisseurs.index') }}" class="navbar-tool-btn {{ $navMod === 'appro' ? 'active' : '' }}" style="--tool-color:#D97706;">
                        <span class="navbar-tool-icon"><i class="fas fa-cart-flatbed"></i></span>
                        <span class="navbar-tool-label">Achat</span>
                    </a>
                    @endcanany
                    @canany(['read:immobilisation','read:dysfonctionnement','read:intervention'])
                    <a href="{{ route('mg.immobilisations.index') }}" class="navbar-tool-btn {{ $navMod === 'mg' ? 'active' : '' }}" style="--tool-color:#0891B2;">
                        <span class="navbar-tool-icon"><i class="fas fa-building"></i></span>
                        <span class="navbar-tool-label">Moy. Gén.</span>
                    </a>
                    @endcanany
                    <a href="{{ route('projet.dashboard') }}" class="navbar-tool-btn {{ $navMod === 'projet' ? 'active' : '' }}" style="--tool-color:#0D9488;">
                        <span class="navbar-tool-icon"><i class="fas fa-diagram-project"></i></span>
                        <span class="navbar-tool-label">Projets</span>
                    </a>
                    <a href="{{ route('objectifs.dashboard') }}" class="navbar-tool-btn {{ $navMod === 'objectifs' ? 'active' : '' }}" style="--tool-color:#DB2777;">
                        <span class="navbar-tool-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="navbar-tool-label">Objectifs</span>
                    </a>
                    @role('super-admin|admin')
                    <a href="{{ route('systeme.organisations.index') }}" class="navbar-tool-btn {{ $navMod === 'admin' ? 'active' : '' }}" style="--tool-color:#475569;">
                        <span class="navbar-tool-icon"><i class="fas fa-shield-halved"></i></span>
                        <span class="navbar-tool-label">Admin</span>
                    </a>
                    @endrole
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="dropdown">
            <button class="navbar-icon-btn position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                @php $hasNotifs = (isset($absencesEnCours) && $absencesEnCours > 0) || (isset($dysfonctionnementsOuverts) && $dysfonctionnementsOuverts > 0); @endphp
                @if($hasNotifs)
                <span class="navbar-notif-dot"></span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown" style="width:300px; min-width:300px;">
                <div class="navbar-dropdown-header">
                    <span>Notifications</span>
                    @if($hasNotifs)
                    <span class="navbar-notif-count">{{ ($absencesEnCours ?? 0) + ($dysfonctionnementsOuverts ?? 0) }}</span>
                    @endif
                </div>
                @if(isset($absencesEnCours) && $absencesEnCours > 0)
                <a class="navbar-dropdown-item" href="{{ route('rh.absences.index') }}">
                    <span class="navbar-dropdown-icon" style="background:#ECFDF5; color:#059669;"><i class="fas fa-user-clock"></i></span>
                    <div>
                        <div class="navbar-dropdown-item-title">Absences en attente</div>
                        <div class="navbar-dropdown-item-sub">{{ $absencesEnCours }} demande(s) à traiter</div>
                    </div>
                </a>
                @endif
                @if(isset($dysfonctionnementsOuverts) && $dysfonctionnementsOuverts > 0)
                <a class="navbar-dropdown-item" href="{{ route('mg.dysfonctionnements.index') }}">
                    <span class="navbar-dropdown-icon" style="background:#FEE2E2; color:#DC2626;"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <div class="navbar-dropdown-item-title">Incidents ouverts</div>
                        <div class="navbar-dropdown-item-sub">{{ $dysfonctionnementsOuverts }} incident(s) non résolu(s)</div>
                    </div>
                </a>
                @endif
                @if(!$hasNotifs)
                <div class="text-center py-3 text-muted" style="font-size:.82rem;">
                    <i class="fas fa-check-circle text-success mb-1 d-block" style="font-size:1.2rem;"></i>
                    Aucune notification
                </div>
                @endif
                <div class="navbar-dropdown-footer">
                    <a href="{{ route('rh.absences.index') }}">Voir toutes les notifications</a>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="navbar-vdivider d-none d-md-block"></div>

        {{-- User --}}
        <div class="dropdown">
            <button class="navbar-user-btn" data-bs-toggle="dropdown">
                <div class="navbar-avatar" style="background:{{ $currentMod['color'] }};">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->prenoms ?? '', 0, 1)) }}
                </div>
                <div class="d-none d-md-flex flex-column text-start" style="line-height:1.2;">
                    <span style="font-size:.82rem; font-weight:600; color:#1E293B;">{{ auth()->user()->prenoms ?? auth()->user()->name }}</span>
                    <span style="font-size:.7rem; color:#94A3B8;">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</span>
                </div>
                <i class="fas fa-chevron-down d-none d-md-block" style="font-size:.6rem; color:#94A3B8;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown" style="width:220px;">
                <div class="navbar-dropdown-header">Mon compte</div>
                <a class="navbar-dropdown-item" href="{{ route('intranet.annuaire.collaborateurs.show', auth()->user()) }}">
                    <span class="navbar-dropdown-icon" style="background:#F1F5F9; color:#475569;"><i class="fas fa-user"></i></span>
                    <div><div class="navbar-dropdown-item-title">Mon profil</div></div>
                </a>
                <a class="navbar-dropdown-item" href="#" style="opacity:.55;cursor:not-allowed;" title="Bientôt">
                    <span class="navbar-dropdown-icon" style="background:#F1F5F9; color:#475569;"><i class="fas fa-key"></i></span>
                    <div><div class="navbar-dropdown-item-title">Changer le mot de passe</div></div>
                </a>
                <div style="height:1px; background:#F1F5F9; margin:.25rem 0;"></div>
                <form action="{{ route('logout') }}" method="POST" style="padding:0 .5rem .35rem;">
                    @csrf
                    <button type="submit" class="navbar-logout-item">
                        <span class="navbar-dropdown-icon" style="background:#FEE2E2; color:#DC2626;"><i class="fas fa-arrow-right-from-bracket"></i></span>
                        <div><div class="navbar-dropdown-item-title" style="color:#DC2626;">Déconnexion</div></div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
