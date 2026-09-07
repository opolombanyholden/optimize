{{-- ============================================================
     SIDEBAR CONTEXTUEL — OptimiZe ERP
     Détection automatique du module actif :
     → Portal/Intranet | Finance | RH | Appro | MG | Admin
     ============================================================ --}}

@php
    $mod = 'portal';
    // Types de référentiel RH métier (hors géographiques) — restent dans la sidebar RH
    $rhReferentielTypes = ['types-contrat','postes','departements','types-evenement','niveaux-qualification','nationalites','groupes-rubriques'];

    if      (request()->routeIs('finance.*'))    $mod = 'finance';
    elseif  (request()->routeIs('rh.*'))         $mod = 'rh';
    elseif  (request()->routeIs('admin.referentiel-rh.*') && in_array(request()->route('type'), $rhReferentielTypes))
                                                 $mod = 'rh';
    elseif  (request()->routeIs('appro.*'))      $mod = 'logistique';
    elseif  (request()->routeIs('mg.*'))         $mod = 'logistique';
    elseif  (request()->routeIs('referentiel.*')) $mod = 'logistique';
    elseif  (request()->routeIs('projet.*'))     $mod = 'projet';
    elseif  (request()->routeIs('intranet.projets.*'))  $mod = 'projet';
    elseif  (request()->routeIs('intranet.taches.*'))   $mod = 'projet';
    elseif  (request()->routeIs('intranet.rapports.*')) $mod = 'projet';
    elseif  (request()->routeIs('admin.roles-projet.*')) $mod = 'projet';
    elseif  (request()->routeIs('objectifs.*'))  $mod = 'objectifs';
    elseif  (request()->routeIs('social.*'))     $mod = 'social';
    elseif  (request()->routeIs('systeme.*'))    $mod = 'admin';
    elseif  (request()->routeIs('admin.*'))     $mod = 'admin';

    $modules = [
        'finance'    => ['name' => 'Finance & Budget',            'icon' => 'fa-coins',            'color' => '#818CF8', 'gradient' => 'linear-gradient(135deg,#4F46E5,#7C3AED)', 'route' => 'finance.dashboard'],
        'rh'         => ['name' => 'RH & Paiement',               'icon' => 'fa-users',            'color' => '#34D399', 'gradient' => 'linear-gradient(135deg,#059669,#0891B2)', 'route' => 'rh.dashboard'],
        'logistique' => ['name' => 'Achats & Moyens Généraux',    'icon' => 'fa-cart-flatbed',     'color' => '#FCD34D', 'gradient' => 'linear-gradient(135deg,#D97706,#DC2626)', 'route' => 'appro.commandes.index'],
        'projet'     => ['name' => 'Projets / Tâches',            'icon' => 'fa-diagram-project',  'color' => '#2DD4BF', 'gradient' => 'linear-gradient(135deg,#0D9488,#0F766E)', 'route' => 'dashboard'],
        'objectifs'  => ['name' => 'Objectifs & KPI',             'icon' => 'fa-bullseye',         'color' => '#F472B6', 'gradient' => 'linear-gradient(135deg,#DB2777,#BE185D)', 'route' => 'dashboard'],
    ];
@endphp

<aside class="sidebar" id="sidebar">

    {{-- ════════════════════════════════════════════════════
         PORTAL MODE  (dashboard / intranet)
    ════════════════════════════════════════════════════ --}}
    @if($mod === 'portal')

    {{-- Brand --}}
    <div class="sb-brand">
        <div class="sb-brand-logo">
            <i class="fas fa-bolt"></i>
        </div>
        <div class="sb-brand-text">
            <span class="sb-brand-name"><span>Optimi</span>Ze</span>
            <span class="sb-brand-sub">Intranet · Portail</span>
        </div>
    </div>

    <nav class="sb-nav">

        {{-- ── Accueil ──────────────────────────────────────── --}}
        <ul class="sb-group mt-1">
            <li>
                <a href="{{ route('dashboard') }}" class="sb-link sb-link-home {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-home"></i></span>
                    <span class="sb-label">Tableau de bord</span>
                </a>
            </li>
        </ul>

        @php
        $accOpen = [
            'communication' => request()->routeIs('intranet.annonces.*','intranet.news.*','intranet.messagerie.*'),
            'agenda'        => request()->routeIs('intranet.calendrier','intranet.evenements.*'),
            'crm'           => request()->routeIs('intranet.contacts.*','intranet.organisations.*','intranet.opportunites.*','intranet.pipeline'),
            'documents'     => request()->routeIs('intranet.courriers.*','intranet.ressources.*','intranet.mediatheque.*','intranet.archives.*','intranet.templates.*'),
            'connaissances' => request()->routeIs('intranet.wiki.*'),
            'annuaire'      => request()->routeIs('intranet.annuaire.*'),
        ];
        if (!array_filter($accOpen)) $accOpen['communication'] = true;
        @endphp

        <div class="sb-accordion" id="sbAccordionIntranet">

            {{-- ══════ COMMUNICATION ═══════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['communication'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-communication"
                        aria-expanded="{{ $accOpen['communication'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-comments"></i></span>
                        <span class="sb-acc-name">Communication</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($annoncesUrgentes) || !empty($mailsNonLus))
                        <span class="sb-acc-badge-dot urgent"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['communication'] ? 'show' : '' }}" id="acc-communication" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.annonces.index') }}" class="sb-link {{ request()->routeIs('intranet.annonces.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-bullhorn"></i></span>
                                <span class="sb-label">Annonces</span>
                                @if(!empty($annoncesUrgentes))<span class="sb-badge-alert" style="background:rgba(220,38,38,.2);color:#FCA5A5;">{{ $annoncesUrgentes }}</span>
                                @elseif(!empty($totalAnnonces))<span class="sb-badge-alert">{{ $totalAnnonces }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.news.index') }}" class="sb-link {{ request()->routeIs('intranet.news.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-newspaper"></i></span>
                                <span class="sb-label">Actualités</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="sb-link" style="opacity:.55;cursor:not-allowed;" title="Module en développement">
                                <span class="sb-icon"><i class="fas fa-at"></i></span>
                                <span class="sb-label">Messagerie pro</span>
                                <span class="sb-badge-alert" style="background:rgba(148,163,184,.2);color:#94A3B8;font-size:.55rem;">Bientôt</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ══════ AGENDA ══════════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['agenda'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-agenda"
                        aria-expanded="{{ $accOpen['agenda'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-calendar-days"></i></span>
                        <span class="sb-acc-name">Agenda</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($evenementsAVenir))
                        <span class="sb-acc-badge-dot"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['agenda'] ? 'show' : '' }}" id="acc-agenda" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.calendrier') }}" class="sb-link {{ request()->routeIs('intranet.calendrier') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-calendar-days"></i></span>
                                <span class="sb-label">Calendrier</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.evenements.index') }}" class="sb-link {{ request()->routeIs('intranet.evenements.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-calendar-check"></i></span>
                                <span class="sb-label">Événements</span>
                                @if(!empty($evenementsAVenir))<span class="sb-badge-alert">{{ $evenementsAVenir }}</span>@endif
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ══════ CRM ══════════════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['crm'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-crm"
                        aria-expanded="{{ $accOpen['crm'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-handshake"></i></span>
                        <span class="sb-acc-name">CRM</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($opportunitesOuvertes))
                        <span class="sb-acc-badge-dot"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['crm'] ? 'show' : '' }}" id="acc-crm" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.contacts.index') }}" class="sb-link {{ request()->routeIs('intranet.contacts.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-address-card"></i></span>
                                <span class="sb-label">Contacts</span>
                                @if(!empty($totalContacts))<span class="sb-badge-alert">{{ $totalContacts }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.organisations.index') }}" class="sb-link {{ request()->routeIs('intranet.organisations.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-building-user"></i></span>
                                <span class="sb-label">Organisations</span>
                                @if(!empty($totalOrganisations))<span class="sb-badge-alert">{{ $totalOrganisations }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.opportunites.index') }}" class="sb-link {{ request()->routeIs('intranet.opportunites.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-fire-flame-curved"></i></span>
                                <span class="sb-label">Opportunités</span>
                                @if(!empty($opportunitesOuvertes))<span class="sb-badge-alert">{{ $opportunitesOuvertes }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.pipeline') }}" class="sb-link {{ request()->routeIs('intranet.pipeline') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-chart-gantt"></i></span>
                                <span class="sb-label">Pipeline</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ══════ DOCUMENTS & GED ═════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['documents'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-documents"
                        aria-expanded="{{ $accOpen['documents'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-folder-open"></i></span>
                        <span class="sb-acc-name">Documents & GED</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($courriersEnAttente))
                        <span class="sb-acc-badge-dot urgent"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['documents'] ? 'show' : '' }}" id="acc-documents" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.courriers.index') }}" class="sb-link {{ request()->routeIs('intranet.courriers.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-envelope-open-text"></i></span>
                                <span class="sb-label">Courrier</span>
                                @if(!empty($courriersEnAttente))<span class="sb-badge-alert" style="background:rgba(220,38,38,.2);color:#FCA5A5;">{{ $courriersEnAttente }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.ressources.index') }}" class="sb-link {{ request()->routeIs('intranet.ressources.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-folder-open"></i></span>
                                <span class="sb-label">Ressources</span>
                                @if(!empty($totalDocuments))<span class="sb-badge-alert">{{ $totalDocuments }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.mediatheque.index') }}" class="sb-link {{ request()->routeIs('intranet.mediatheque.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-photo-film"></i></span>
                                <span class="sb-label">Médiathèque</span>
                                @if(!empty($totalMedias))<span class="sb-badge-alert">{{ $totalMedias }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.archives.index') }}" class="sb-link {{ request()->routeIs('intranet.archives.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-box-archive"></i></span>
                                <span class="sb-label">Archives</span>
                                @if(!empty($totalArchives))<span class="sb-badge-alert">{{ $totalArchives }}</span>@endif
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.templates.index') }}" class="sb-link {{ request()->routeIs('intranet.templates.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-file-circle-plus"></i></span>
                                <span class="sb-label">Templates</span>
                                @if(!empty($totalTemplates))<span class="sb-badge-alert">{{ $totalTemplates }}</span>@endif
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Projets & Tâches + Objectifs & KPI déplacés vers modules ERP --}}

            {{-- ══════ BASE DE CONNAISSANCES ═══════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['connaissances'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-connaissances"
                        aria-expanded="{{ $accOpen['connaissances'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-book-open"></i></span>
                        <span class="sb-acc-name">Connaissances</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($totalWikiArticles))
                        <span class="sb-acc-badge-dot"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['connaissances'] ? 'show' : '' }}" id="acc-connaissances" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.wiki.index') }}" class="sb-link {{ request()->routeIs('intranet.wiki.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-book-open"></i></span>
                                <span class="sb-label">Wiki</span>
                                @if(!empty($totalWikiArticles))<span class="sb-badge-alert">{{ $totalWikiArticles }}</span>@endif
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- ══════ ANNUAIRE ════════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['annuaire'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-annuaire"
                        aria-expanded="{{ $accOpen['annuaire'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-address-book"></i></span>
                        <span class="sb-acc-name">Annuaire</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['annuaire'] ? 'show' : '' }}" id="acc-annuaire" data-bs-parent="#sbAccordionIntranet">
                    <ul class="sb-group sb-module-intranet sb-acc-body">
                        <li>
                            <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" class="sb-link {{ request()->routeIs('intranet.annuaire.collaborateurs.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-address-book"></i></span>
                                <span class="sb-label">Collaborateurs</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.annuaire.services.index') }}" class="sb-link {{ request()->routeIs('intranet.annuaire.services.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-sitemap"></i></span>
                                <span class="sb-label">Entités</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('intranet.annuaire.equipes.index') }}" class="sb-link {{ request()->routeIs('intranet.annuaire.equipes.*') ? 'active' : '' }}">
                                <span class="sb-icon"><i class="fas fa-people-group"></i></span>
                                <span class="sb-label">Équipes</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>{{-- /sb-accordion --}}

    </nav>


    {{-- ════════════════════════════════════════════════════
         FINANCE MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'finance')

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#4F46E5,#7C3AED); --mod-color: #818CF8;">
        <div class="sb-mod-icon"><i class="fas fa-coins"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Finance & Budget</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">
        {{-- Lien direct Tableau de bord (hors accordéon) --}}
        <ul class="sb-group sb-module-finance" style="margin-bottom:.35rem;">
            <li><a href="{{ route('finance.dashboard') }}" class="sb-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-gauge-high"></i></span><span class="sb-label">Tableau de bord</span>
            </a></li>
        </ul>

        @php
            $accOpenFi = [
                'comptabilite'     => request()->routeIs('finance.exercices.*','finance.budgets.*','finance.modifications-budgetaires.*','finance.comptes.*','finance.grand-livre.*','finance.execution-budgetaire'),
                'depenses'         => request()->routeIs('finance.ordres.*','finance.operations.*','finance.factures.*','finance.clients.*'),
                'referentiels-fin' => request()->routeIs('finance.referentiels.*'),
            ];
            if (!array_filter($accOpenFi)) $accOpenFi['comptabilite'] = true;
        @endphp

        <div class="sb-accordion sb-accordion-finance" id="sbAccordionFinance">

            {{-- ══════ COMPTABILITÉ ══════════════════════════ --}}
            @canany(['read:exercice','read:budget','read:compte','read:grandlivre'])
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenFi['comptabilite'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-fi-comptabilite"
                        aria-expanded="{{ $accOpenFi['comptabilite'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-book"></i></span>
                        <span class="sb-acc-name">Comptabilité</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpenFi['comptabilite'] ? 'show' : '' }}" id="acc-fi-comptabilite" data-bs-parent="#sbAccordionFinance">
                    <ul class="sb-group sb-module-finance sb-acc-body">
                        @can('read:exercice')
                        <li><a href="{{ route('finance.exercices.index') }}" class="sb-link {{ request()->routeIs('finance.exercices.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-calendar-alt"></i></span><span class="sb-label">Exercices</span>
                        </a></li>
                        @endcan
                        @can('read:budget')
                        <li><a href="{{ route('finance.budgets.index') }}" class="sb-link {{ request()->routeIs('finance.budgets.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-wallet"></i></span><span class="sb-label">Budgets</span>
                        </a></li>
                        <li><a href="{{ route('finance.execution-budgetaire') }}" class="sb-link {{ request()->routeIs('finance.execution-budgetaire') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-gauge-high"></i></span><span class="sb-label">Exécution budgétaire</span>
                        </a></li>
                        <li><a href="{{ route('finance.modifications-budgetaires.index') }}" class="sb-link {{ request()->routeIs('finance.modifications-budgetaires.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-exchange-alt"></i></span><span class="sb-label">Modifications budg.</span>
                        </a></li>
                        @endcan
                        @can('read:compte')
                        <li><a href="{{ route('finance.comptes.index') }}" class="sb-link {{ request()->routeIs('finance.comptes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-piggy-bank"></i></span><span class="sb-label">Comptes de trésorerie</span>
                        </a></li>
                        @endcan
                        @can('read:grandlivre')
                        <li><a href="{{ route('finance.grand-livre.index') }}" class="sb-link {{ request()->routeIs('finance.grand-livre.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-book-open"></i></span><span class="sb-label">Grand-livre</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endcanany

            {{-- ══════ DÉPENSES & RECETTES ══════════════════ --}}
            @canany(['read:operation','read:facture','read:client'])
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenFi['depenses'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-fi-depenses"
                        aria-expanded="{{ $accOpenFi['depenses'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-money-bill-transfer"></i></span>
                        <span class="sb-acc-name">Dépenses & Recettes</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpenFi['depenses'] ? 'show' : '' }}" id="acc-fi-depenses" data-bs-parent="#sbAccordionFinance">
                    <ul class="sb-group sb-module-finance sb-acc-body">
                        @can('read:operation')
                        <li><a href="{{ route('finance.ordres.index') }}" class="sb-link {{ request()->routeIs('finance.ordres.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-exchange-alt"></i></span><span class="sb-label">Ordres de paiement</span>
                        </a></li>
                        <li><a href="{{ route('finance.operations.index') }}" class="sb-link {{ request()->routeIs('finance.operations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-money-bill-transfer"></i></span><span class="sb-label">Opérations (legacy)</span>
                        </a></li>
                        @endcan
                        @can('read:facture')
                        <li><a href="{{ route('finance.factures.index') }}" class="sb-link {{ request()->routeIs('finance.factures.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-invoice"></i></span><span class="sb-label">Factures</span>
                        </a></li>
                        @endcan
                        @can('read:client')
                        <li><a href="{{ route('finance.clients.index') }}" class="sb-link {{ request()->routeIs('finance.clients.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-handshake"></i></span><span class="sb-label">Clients</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endcanany

            {{-- ══════ RÉFÉRENTIELS ═════════════════════════ --}}
            @canany(['read:budget','read:titre','read:ligne','read:rubrique_operation'])
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenFi['referentiels-fin'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-fi-referentiels"
                        aria-expanded="{{ $accOpenFi['referentiels-fin'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-database"></i></span>
                        <span class="sb-acc-name">Référentiels</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpenFi['referentiels-fin'] ? 'show' : '' }}" id="acc-fi-referentiels" data-bs-parent="#sbAccordionFinance">
                    <ul class="sb-group sb-module-finance sb-acc-body">
                        @can('read:budget')
                        <li><a href="{{ route('finance.referentiels.sources.index') }}" class="sb-link {{ request()->routeIs('finance.referentiels.sources.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-money-check-alt"></i></span><span class="sb-label">Sources de financement</span>
                        </a></li>
                        @endcan
                        @can('read:titre')
                        <li><a href="{{ route('finance.referentiels.titres.index') }}" class="sb-link {{ request()->routeIs('finance.referentiels.titres.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">Titres / Familles</span>
                        </a></li>
                        @endcan
                        @can('read:ligne')
                        <li><a href="{{ route('finance.referentiels.lignes.index') }}" class="sb-link {{ request()->routeIs('finance.referentiels.lignes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-list"></i></span><span class="sb-label">Lignes (codes analyt.)</span>
                        </a></li>
                        @endcan
                        @can('read:rubrique_operation')
                        <li><a href="{{ route('finance.referentiels.rubriques.index') }}" class="sb-link {{ request()->routeIs('finance.referentiels.rubriques.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-tags"></i></span><span class="sb-label">Rubriques opérations</span>
                        </a></li>
                        <li><a href="{{ route('finance.referentiels.ordres-modeles.index') }}" class="sb-link {{ request()->routeIs('finance.referentiels.ordres-modeles.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-invoice"></i></span><span class="sb-label">Modèles d'ordre</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>
            @endcanany

        </div>{{-- /sb-accordion-finance --}}
    </nav>


    {{-- ════════════════════════════════════════════════════
         RH MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'rh')

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#059669,#0891B2); --mod-color: #34D399;">
        <div class="sb-mod-icon"><i class="fas fa-users"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">RH & Paiement</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">
        {{-- Liens directs (hors accordéon) — Tableau de bord + Audit log --}}
        <ul class="sb-group sb-module-rh" style="margin-bottom:.35rem;">
            <li><a href="{{ route('rh.dashboard') }}" class="sb-link {{ request()->routeIs('rh.dashboard') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-gauge-high"></i></span><span class="sb-label">Tableau de bord</span>
            </a></li>
            @can('read:employee')
            <li><a href="{{ route('rh.audit-log') }}" class="sb-link {{ request()->routeIs('rh.audit-log') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-shield-halved"></i></span><span class="sb-label">Audit log</span>
            </a></li>
            @endcan
        </ul>

        @php
        $accOpen = [
            'personnel'      => request()->routeIs('rh.employees.*','rh.affilies.*','rh.evenements-carriere.*'),
            'carriere'       => request()->routeIs('rh.competences.*','rh.qualifications.*','rh.formations.*'),
            'temps'          => request()->routeIs('rh.absences.*','rh.conges-soldes.*','rh.plannings.*','rh.missions.*','rh.pointages.*'),
            'performance'    => request()->routeIs('rh.evaluations-performance.*','rh.sanctions.*','rh.departs.*'),
            'paie'           => request()->routeIs('rh.paie.*','rh.rubriques.*','rh.payements.*','rh.payements-globals.*','rh.campagnes-paie.*','rh.declarations-sociales.*','rh.echantillons-paie.*'),
            'recrutement'    => request()->routeIs('rh.recrutements.*','rh.postulants.*'),
            'referentiels-rh'=> request()->routeIs('rh.grades.*') || (request()->routeIs('admin.referentiel-rh.*') && in_array(request()->route('type'), $rhReferentielTypes)),
        ];
        if (!array_filter($accOpen)) $accOpen['personnel'] = true;
        @endphp

        <div class="sb-accordion sb-accordion-rh" id="sbAccordionRh">

            {{-- ══════ ADMINISTRATION DU PERSONNEL ═════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['personnel'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-personnel"
                        aria-expanded="{{ $accOpen['personnel'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-id-card-clip"></i></span>
                        <span class="sb-acc-name">Personnel</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['personnel'] ? 'show' : '' }}" id="acc-rh-personnel" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:employee')
                        <li><a href="{{ route('rh.employees.index') }}" class="sb-link {{ request()->routeIs('rh.employees.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-id-card"></i></span><span class="sb-label">Employés</span>
                        </a></li>
                        @endcan
                        @can('read:affilie')
                        <li><a href="{{ route('rh.affilies.index') }}" class="sb-link {{ request()->routeIs('rh.affilies.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-group"></i></span><span class="sb-label">Ayants-droit</span>
                        </a></li>
                        @endcan
                        @can('read:evenement_carriere')
                        <li><a href="{{ route('rh.evenements-carriere.index') }}" class="sb-link {{ request()->routeIs('rh.evenements-carriere.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-arrow-trend-up"></i></span><span class="sb-label">Évènements carrière</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ CARRIÈRE & COMPÉTENCES ══════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['carriere'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-carriere"
                        aria-expanded="{{ $accOpen['carriere'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-graduation-cap"></i></span>
                        <span class="sb-acc-name">Carrière & Compétences</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['carriere'] ? 'show' : '' }}" id="acc-rh-carriere" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:competence')
                        <li><a href="{{ route('rh.competences.index') }}" class="sb-link {{ request()->routeIs('rh.competences.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-brain"></i></span><span class="sb-label">Compétences</span>
                        </a></li>
                        @endcan
                        @can('read:qualification')
                        <li><a href="{{ route('rh.qualifications.index') }}" class="sb-link {{ request()->routeIs('rh.qualifications.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-graduation-cap"></i></span><span class="sb-label">Qualifications</span>
                        </a></li>
                        @endcan
                        @can('read:formation')
                        <li><a href="{{ route('rh.formations.index') }}" class="sb-link {{ request()->routeIs('rh.formations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-chalkboard-user"></i></span><span class="sb-label">Formations</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ TEMPS & ACTIVITÉ ════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['temps'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-temps"
                        aria-expanded="{{ $accOpen['temps'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-user-clock"></i></span>
                        <span class="sb-acc-name">Temps & Activité</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($absencesEnCours))<span class="sb-acc-badge-dot"></span>@endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['temps'] ? 'show' : '' }}" id="acc-rh-temps" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:absence')
                        <li><a href="{{ route('rh.absences.index') }}" class="sb-link {{ request()->routeIs('rh.absences.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-clock"></i></span><span class="sb-label">Absences & Congés</span>
                            @if(isset($absencesEnCours) && $absencesEnCours > 0)
                                <span class="sb-badge-alert">{{ $absencesEnCours }}</span>
                            @endif
                        </a></li>
                        @endcan
                        @can('read:conge_solde')
                        <li><a href="{{ route('rh.conges-soldes.index') }}" class="sb-link {{ request()->routeIs('rh.conges-soldes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-umbrella-beach"></i></span><span class="sb-label">Soldes de congés</span>
                        </a></li>
                        @endcan
                        @can('read:planning')
                        <li><a href="{{ route('rh.plannings.index') }}" class="sb-link {{ request()->routeIs('rh.plannings.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-calendar-week"></i></span><span class="sb-label">Plannings</span>
                        </a></li>
                        @endcan
                        @can('read:mission')
                        <li><a href="{{ route('rh.missions.index') }}" class="sb-link {{ request()->routeIs('rh.missions.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-route"></i></span><span class="sb-label">Missions</span>
                        </a></li>
                        @endcan
                        @can('read:paie')
                        <li><a href="{{ route('rh.pointages.index') }}" class="sb-link {{ request()->routeIs('rh.pointages.*') && !request()->routeIs('rh.pointages.qr-generique.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-stopwatch"></i></span><span class="sb-label">Pointages</span>
                        </a></li>
                        <li><a href="{{ route('pointage.validation-n1') }}" class="sb-link {{ request()->routeIs('pointage.validation-n1*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-shield"></i></span><span class="sb-label">Validation N+1</span>
                        </a></li>
                        @endcan
                        @can('update:employee')
                        <li><a href="{{ route('rh.pointages.qr-generique.admin') }}" class="sb-link {{ request()->routeIs('rh.pointages.qr-generique.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-qrcode"></i></span><span class="sb-label">QR générique</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ PERFORMANCE & DISCIPLINE ════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['performance'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-performance"
                        aria-expanded="{{ $accOpen['performance'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-star-half-stroke"></i></span>
                        <span class="sb-acc-name">Performance & Discipline</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['performance'] ? 'show' : '' }}" id="acc-rh-performance" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:performance')
                        <li><a href="{{ route('rh.evaluations-performance.index') }}" class="sb-link {{ request()->routeIs('rh.evaluations-performance.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-star-half-stroke"></i></span><span class="sb-label">Évaluations</span>
                        </a></li>
                        @endcan
                        @can('read:sanction')
                        <li><a href="{{ route('rh.sanctions.index') }}" class="sb-link {{ request()->routeIs('rh.sanctions.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-gavel"></i></span><span class="sb-label">Sanctions</span>
                        </a></li>
                        @endcan
                        @can('read:depart')
                        <li><a href="{{ route('rh.departs.index') }}" class="sb-link {{ request()->routeIs('rh.departs.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-door-open"></i></span><span class="sb-label">Départs / Sorties</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ PAIE ════════════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['paie'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-paie"
                        aria-expanded="{{ $accOpen['paie'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-money-check-alt"></i></span>
                        <span class="sb-acc-name">Paie</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['paie'] ? 'show' : '' }}" id="acc-rh-paie" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:paie')
                        <li><a href="{{ route('rh.paie.index') }}" class="sb-link {{ request()->routeIs('rh.paie.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-money-check-alt"></i></span><span class="sb-label">Bulletins de paie</span>
                        </a></li>
                        @endcan
                        @can('read:rubrique')
                        <li><a href="{{ route('rh.rubriques.index') }}" class="sb-link {{ request()->routeIs('rh.rubriques.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="sb-label">Rubriques de paie</span>
                        </a></li>
                        @endcan
                        @can('read:payement')
                        <li><a href="{{ route('rh.payements.index') }}" class="sb-link {{ request()->routeIs('rh.payements.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-money-bill-transfer"></i></span><span class="sb-label">Payements</span>
                        </a></li>
                        @endcan
                        @can('read:payement_global')
                        <li><a href="{{ route('rh.payements-globals.index') }}" class="sb-link {{ request()->routeIs('rh.payements-globals.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-chart-pie"></i></span><span class="sb-label">Masse salariale</span>
                        </a></li>
                        @endcan
                        @can('read:paie')
                        <li><a href="{{ route('rh.echantillons-paie.index') }}" class="sb-link {{ request()->routeIs('rh.echantillons-paie.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-layer-group"></i></span><span class="sb-label">Échantillons de paie</span>
                        </a></li>
                        <li><a href="{{ route('rh.campagnes-paie.index') }}" class="sb-link {{ request()->routeIs('rh.campagnes-paie.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-rocket"></i></span><span class="sb-label">Campagnes de paie</span>
                        </a></li>
                        <li><a href="{{ route('rh.declarations-sociales.index') }}" class="sb-link {{ request()->routeIs('rh.declarations-sociales.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-building-columns"></i></span><span class="sb-label">Déclarations sociales</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ RECRUTEMENT ═════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['recrutement'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-recrutement"
                        aria-expanded="{{ $accOpen['recrutement'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-user-plus"></i></span>
                        <span class="sb-acc-name">Recrutement</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['recrutement'] ? 'show' : '' }}" id="acc-rh-recrutement" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        @can('read:recrutement')
                        <li><a href="{{ route('rh.recrutements.index') }}" class="sb-link {{ request()->routeIs('rh.recrutements.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-plus"></i></span><span class="sb-label">Campagnes</span>
                        </a></li>
                        @endcan
                        @can('read:postulant')
                        <li><a href="{{ route('rh.postulants.index') }}" class="sb-link {{ request()->routeIs('rh.postulants.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-id-badge"></i></span><span class="sb-label">Candidatures</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ RÉFÉRENTIELS RH ═══════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['referentiels-rh'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-rh-referentiels"
                        aria-expanded="{{ $accOpen['referentiels-rh'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-database"></i></span>
                        <span class="sb-acc-name">Référentiels</span>
                    </span>
                    <span class="sb-acc-right">
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['referentiels-rh'] ? 'show' : '' }}" id="acc-rh-referentiels" data-bs-parent="#sbAccordionRh">
                    <ul class="sb-group sb-module-rh sb-acc-body">
                        <li><a href="{{ route('rh.grades.index') }}" class="sb-link {{ request()->routeIs('rh.grades.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-medal"></i></span><span class="sb-label">Grades</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'types-contrat') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'types-contrat' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-contract"></i></span><span class="sb-label">Types de contrat</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'postes') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'postes' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-briefcase"></i></span><span class="sb-label">Postes</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'departements') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'departements' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">Départements</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'types-evenement') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'types-evenement' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-arrow-trend-up"></i></span><span class="sb-label">Types évén. carrière</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'niveaux-qualification') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'niveaux-qualification' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-graduation-cap"></i></span><span class="sb-label">Niveaux qualification</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'nationalites') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'nationalites' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-flag"></i></span><span class="sb-label">Nationalités</span>
                        </a></li>
                        <li><a href="{{ route('admin.referentiel-rh.index', 'groupes-rubriques') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'groupes-rubriques' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-layer-group"></i></span><span class="sb-label">Groupes rubriques paie</span>
                        </a></li>
                        @can('read:rubrique')
                        <li><a href="{{ route('rh.rubriques.index') }}" class="sb-link {{ request()->routeIs('rh.rubriques.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-tags"></i></span><span class="sb-label">Rubriques de paie</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

        </div>

    </nav>


    {{-- ════════════════════════════════════════════════════
         APPRO MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'logistique')

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#D97706,#DC2626); --mod-color: #FCD34D;">
        <div class="sb-mod-icon"><i class="fas fa-cart-flatbed"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Achats & Moyens Généraux</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">
        {{-- ── Accueil module (hors accordéon) ──────────── --}}
        <ul class="sb-group sb-module-appro" style="margin-top:.75rem;">
            <li><a href="{{ route('appro.dashboard') }}" class="sb-link sb-link-home {{ request()->routeIs('appro.dashboard') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-house"></i></span><span class="sb-label">Dashboard</span>
            </a></li>
        </ul>

        {{-- ── Vue d'ensemble (hors accordéon) ─────────── --}}
        <div class="sb-section-header appro" style="margin-top:.75rem;">
            <span class="sb-section-dot"></span>
            <span class="sb-section-name">Vue d'ensemble</span>
        </div>
        <ul class="sb-group sb-module-appro">
            <li><a href="{{ route('appro.commandes-internes.index') }}" class="sb-link {{ request()->routeIs('appro.commandes-internes.index') && !request('onglet') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-gauge-high"></i></span><span class="sb-label">Mes demandes</span>
            </a></li>
        </ul>

        @php
        $accOpen = [
            'achats'        => request()->routeIs('appro.commandes.*','appro.fournisseurs.*','appro.devis-fournisseur.*','appro.livraisons-fournisseur.*','appro.contrats.*'),
            'demandes'      => request()->routeIs('appro.commandes-internes.*'),
            'stock'         => request()->routeIs('appro.stock.*','appro.inventaires.*'),
            'evaluations'   => request()->routeIs('appro.evaluations.*','appro.campagnes.*'),
            'immobilisations' => request()->routeIs('mg.immobilisations.*'),
            'maintenance'   => request()->routeIs('mg.dysfonctionnements.*','mg.interventions.*'),
            'referentiels'  => request()->routeIs('referentiel.*'),
        ];
        if (!array_filter($accOpen)) $accOpen['achats'] = true;
        @endphp

        <div class="sb-accordion sb-accordion-appro" id="sbAccordionLogistique">

            {{-- ══════ ACHATS EXTERNES ═════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['achats'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-achats"
                        aria-expanded="{{ $accOpen['achats'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-file-invoice"></i></span>
                        <span class="sb-acc-name">Achats fournisseurs</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpen['achats'] ? 'show' : '' }}" id="acc-log-achats" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        @can('read:commande')
                        <li><a href="{{ route('appro.commandes.index') }}" class="sb-link {{ request()->routeIs('appro.commandes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-invoice"></i></span><span class="sb-label">Commandes fournisseurs</span>
                            @if(isset($commandesEnCours) && $commandesEnCours > 0)
                                <span class="sb-badge-alert" style="background:rgba(217,119,6,.25);color:#FCD34D;">{{ $commandesEnCours }}</span>
                            @endif
                        </a></li>
                        <li><a href="{{ route('appro.devis-fournisseur.index') }}" class="sb-link {{ request()->routeIs('appro.devis-fournisseur.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-signature"></i></span><span class="sb-label">Devis fournisseur</span>
                        </a></li>
                        <li><a href="{{ route('appro.livraisons-fournisseur.index') }}" class="sb-link {{ request()->routeIs('appro.livraisons-fournisseur.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-truck-loading"></i></span><span class="sb-label">Livraisons fournisseur</span>
                        </a></li>
                        <li><a href="{{ route('appro.contrats.index') }}" class="sb-link {{ request()->routeIs('appro.contrats.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-contract"></i></span><span class="sb-label">Engagements fournisseurs</span>
                        </a></li>
                        @endcan
                        @can('read:fournisseur')
                        <li><a href="{{ route('appro.fournisseurs.index') }}" class="sb-link {{ request()->routeIs('appro.fournisseurs.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-truck"></i></span><span class="sb-label">Fournisseurs</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ DEMANDES INTERNES ═══════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['demandes'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-demandes"
                        aria-expanded="{{ $accOpen['demandes'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-inbox"></i></span>
                        <span class="sb-acc-name">Demandes internes</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpen['demandes'] ? 'show' : '' }}" id="acc-log-demandes" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        <li><a href="{{ route('appro.commandes-internes.index', ['onglet' => 'mes']) }}" class="sb-link {{ request()->routeIs('appro.commandes-internes.*') && request('onglet') === 'mes' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-clock"></i></span><span class="sb-label">Mes demandes</span>
                        </a></li>
                        <li><a href="{{ route('appro.commandes-internes.index', ['onglet' => 'a_valider']) }}" class="sb-link {{ request('onglet') === 'a_valider' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-check"></i></span><span class="sb-label">À valider (N+1)</span>
                        </a></li>
                        @can('update:commande')
                        <li><a href="{{ route('appro.commandes-internes.index', ['onglet' => 'appro']) }}" class="sb-link {{ request('onglet') === 'appro' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-hand-holding"></i></span><span class="sb-label">Traitement Appro</span>
                        </a></li>
                        @endcan
                        <li><a href="{{ route('appro.commandes-internes.create') }}" class="sb-link {{ request()->routeIs('appro.commandes-internes.create') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-plus"></i></span><span class="sb-label">Nouvelle demande</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ GESTION DES STOCKS ══════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['stock'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-stock"
                        aria-expanded="{{ $accOpen['stock'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-boxes-stacked"></i></span>
                        <span class="sb-acc-name">Gestion des stocks</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(isset($stockAlertes) && $stockAlertes > 0)
                            <span class="sb-acc-badge-dot urgent"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['stock'] ? 'show' : '' }}" id="acc-log-stock" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        <li><a href="{{ route('appro.stock.dashboard') }}" class="sb-link {{ request()->routeIs('appro.stock.dashboard') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-gauge-high"></i></span><span class="sb-label">Tableau de bord</span>
                            @if(isset($stockAlertes) && $stockAlertes > 0)
                                <span class="sb-badge-alert" style="background:rgba(220,38,38,.2);color:#FCA5A5;">{{ $stockAlertes }}</span>
                            @endif
                        </a></li>
                        <li><a href="{{ route('appro.stock.emplacements') }}" class="sb-link {{ request()->routeIs('appro.stock.emplacements') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-warehouse"></i></span><span class="sb-label">Stock par emplacement</span>
                        </a></li>
                        <li><a href="{{ route('appro.stock.mouvements') }}" class="sb-link {{ request()->routeIs('appro.stock.mouvements') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-arrow-right-arrow-left"></i></span><span class="sb-label">Journal des mouvements</span>
                        </a></li>
                        <li><a href="{{ route('appro.inventaires.index') }}" class="sb-link {{ request()->routeIs('appro.inventaires.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-clipboard-check"></i></span><span class="sb-label">Inventaires</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ ÉVALUATIONS PRESTATAIRES ════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['evaluations'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-eval"
                        aria-expanded="{{ $accOpen['evaluations'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-star"></i></span>
                        <span class="sb-acc-name">Évaluation prestataires</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpen['evaluations'] ? 'show' : '' }}" id="acc-log-eval" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        <li><a href="{{ route('appro.evaluations.index') }}" class="sb-link {{ request()->routeIs('appro.evaluations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-star-half-stroke"></i></span><span class="sb-label">Évaluations</span>
                        </a></li>
                        <li><a href="{{ route('appro.campagnes.index') }}" class="sb-link {{ request()->routeIs('appro.campagnes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-bullhorn"></i></span><span class="sb-label">Campagnes</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ IMMOBILISATIONS ═════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['immobilisations'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-immo"
                        aria-expanded="{{ $accOpen['immobilisations'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-building-columns"></i></span>
                        <span class="sb-acc-name">Immobilisations</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpen['immobilisations'] ? 'show' : '' }}" id="acc-log-immo" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        @can('read:immobilisation')
                        <li><a href="{{ route('mg.immobilisations.index') }}" class="sb-link {{ request()->routeIs('mg.immobilisations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-building-columns"></i></span><span class="sb-label">Registre & amortissements</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ MAINTENANCE (tickets + interventions) ═══ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['maintenance'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-maint"
                        aria-expanded="{{ $accOpen['maintenance'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-tools"></i></span>
                        <span class="sb-acc-name">Action Interne</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(isset($dysfonctionnementsOuverts) && $dysfonctionnementsOuverts > 0)
                            <span class="sb-acc-badge-dot urgent"></span>
                        @endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpen['maintenance'] ? 'show' : '' }}" id="acc-log-maint" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        @can('read:dysfonctionnement')
                        <li><a href="{{ route('mg.dysfonctionnements.index') }}" class="sb-link {{ request()->routeIs('mg.dysfonctionnements.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-ticket"></i></span><span class="sb-label">Tickets (dysfonctionnements)</span>
                            @if(isset($dysfonctionnementsOuverts) && $dysfonctionnementsOuverts > 0)
                                <span class="sb-badge-alert" style="background:rgba(220,38,38,.2);color:#FCA5A5;">{{ $dysfonctionnementsOuverts }}</span>
                            @endif
                        </a></li>
                        @endcan
                        @can('read:intervention')
                        <li><a href="{{ route('mg.interventions.index') }}" class="sb-link {{ request()->routeIs('mg.interventions.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-wrench"></i></span><span class="sb-label">Interventions</span>
                        </a></li>
                        @endcan
                    </ul>
                </div>
            </div>

            {{-- ══════ RÉFÉRENTIELS ════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpen['referentiels'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-log-ref"
                        aria-expanded="{{ $accOpen['referentiels'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-sliders"></i></span>
                        <span class="sb-acc-name">Référentiels</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpen['referentiels'] ? 'show' : '' }}" id="acc-log-ref" data-bs-parent="#sbAccordionLogistique">
                    <ul class="sb-group sb-module-appro sb-acc-body">
                        <li><a href="{{ route('referentiel.catalogue.index') }}" class="sb-link {{ request()->routeIs('referentiel.catalogue.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-book"></i></span><span class="sb-label">Catalogue biens/services</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.familles.index') }}" class="sb-link {{ request()->routeIs('referentiel.familles.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">Familles d'articles</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.evaluations.index') }}" class="sb-link {{ request()->routeIs('referentiel.evaluations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-star"></i></span><span class="sb-label">Critères d'évaluation</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.mg-thematiques.index') }}" class="sb-link {{ request()->routeIs('referentiel.mg-thematiques.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-tags"></i></span><span class="sb-label">Thématiques MG</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.familles-dysfonctionnement.index') }}" class="sb-link {{ request()->routeIs('referentiel.familles-dysfonctionnement.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-layer-group"></i></span><span class="sb-label">Familles de dysfonctionnement</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.types-dysfonctionnement.index') }}" class="sb-link {{ request()->routeIs('referentiel.types-dysfonctionnement.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-triangle-exclamation"></i></span><span class="sb-label">Types de dysfonctionnement</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.natures-intervention.index') }}" class="sb-link {{ request()->routeIs('referentiel.natures-intervention.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-wrench"></i></span><span class="sb-label">Natures d'intervention</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.types-engagement.index') }}" class="sb-link {{ request()->routeIs('referentiel.types-engagement.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-contract"></i></span><span class="sb-label">Types d'engagement</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.frequences-paiement.index') }}" class="sb-link {{ request()->routeIs('referentiel.frequences-paiement.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-calendar-check"></i></span><span class="sb-label">Fréquences de paiement</span>
                        </a></li>
                        <li><a href="{{ route('referentiel.emplacements.index') }}" class="sb-link {{ request()->routeIs('referentiel.emplacements.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-warehouse"></i></span><span class="sb-label">Emplacements de stockage</span>
                        </a></li>
                    </ul>
                </div>
            </div>

        </div>

    </nav>


    {{-- ════════════════════════════════════════════════════
         PROJET AVANCÉ MODE  (PMP / PMBOK)
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'projet')

    @php
        $currentProjet = request()->route('projet');
        if ($currentProjet && !($currentProjet instanceof \App\Models\Intranet\Projet)) {
            $currentProjet = \App\Models\Intranet\Projet::find($currentProjet);
        }
        // Persist last-viewed project so sidebar stays usable on pages without {projet} param
        if ($currentProjet) {
            session(['projet.last_viewed' => $currentProjet->id]);
        } else {
            $lastId = session('projet.last_viewed');
            if ($lastId) {
                $currentProjet = \App\Models\Intranet\Projet::find($lastId);
            }
            if (!$currentProjet && auth()->check()) {
                $uid = auth()->id();
                $currentProjet = \App\Models\Intranet\Projet::where('chef_projet_id', $uid)
                    ->orWhereHas('membres', fn($q) => $q->where('users.id', $uid))
                    ->orderByDesc('updated_at')
                    ->first();
            }
        }
        // Helper : génère le href + attrs pour un lien projet-dépendant
        $projetLink = function(string $route, string $activePattern) use ($currentProjet) {
            $isActive = request()->routeIs($activePattern) ? 'active' : '';
            if ($currentProjet) {
                return [
                    'href'  => route($route, $currentProjet),
                    'class' => "sb-link $isActive",
                    'title' => '',
                ];
            }
            return [
                'href'  => route('intranet.projets.index'),
                'class' => "sb-link sb-link-needs-project",
                'title' => "Sélectionnez d'abord un projet",
            ];
        };
    @endphp

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#0D9488,#0F766E); --mod-color: #2DD4BF;">
        <div class="sb-mod-icon"><i class="fas fa-diagram-project"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Projets / Tâches</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    {{-- L'indicateur du « projet courant » a été retiré. Le contexte projet
         est visible depuis /projet/{id}/overview et /intranet/projets. --}}

    <nav class="sb-nav">

        {{-- Liens directs (hors accordéon) — priorités du module --}}
        <ul class="sb-group sb-module-projet" style="margin-bottom:.35rem;">
            {{-- Tableau de bord --}}
            <li>
                <a href="{{ route('projet.dashboard') }}" class="sb-link {{ request()->routeIs('projet.dashboard') ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="sb-label">Tableau de bord</span>
                    @if(!empty($projetsEnCours))<span class="sb-badge-alert">{{ $projetsEnCours }}</span>@endif
                </a>
            </li>

            {{-- PROJETS — accès direct liste + création rapide --}}
            <li>
                <div class="sb-link-row priority">
                    <a href="{{ route('intranet.projets.index') }}" class="sb-link {{ request()->routeIs('intranet.projets.*') && !request()->routeIs('intranet.projets.create') ? 'active' : '' }}">
                        <span class="sb-icon"><i class="fas fa-folder-tree"></i></span>
                        <span class="sb-label">Projets</span>
                    </a>
                    <a href="{{ route('intranet.projets.create') }}" class="sb-quick-add {{ request()->routeIs('intranet.projets.create') ? 'active' : '' }}"
                       title="Nouveau projet" aria-label="Nouveau projet">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </li>

            {{-- TÂCHES — accès direct liste + création rapide --}}
            <li>
                <div class="sb-link-row priority">
                    <a href="{{ route('intranet.taches.index') }}" class="sb-link {{ request()->routeIs('intranet.taches.*') && !request()->routeIs('intranet.taches.create') ? 'active' : '' }}">
                        <span class="sb-icon"><i class="fas fa-list-check"></i></span>
                        <span class="sb-label">Tâches</span>
                        @if(!empty($tachesUrgentes))<span class="sb-badge-alert" style="background:rgba(220,38,38,.2);color:#FCA5A5;">{{ $tachesUrgentes }}</span>
                        @elseif(!empty($tachesEnCours))<span class="sb-badge-alert">{{ $tachesEnCours }}</span>@endif
                    </a>
                    <a href="{{ route('intranet.taches.create') }}" class="sb-quick-add {{ request()->routeIs('intranet.taches.create') ? 'active' : '' }}"
                       title="Nouvelle tâche" aria-label="Nouvelle tâche">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </li>
        </ul>

        @php
        $accOpenPr = [
            'planification'    => request()->routeIs('projet.wbs.*','projet.jalons.*','projet.ressources.*','intranet.calendrier'),
            'execution'        => request()->routeIs('projet.feuilles-temps.*','projet.livrables.*'),
            'maitrise'         => request()->routeIs('projet.couts.*','projet.evm.*','projet.changements.*'),
            'risques'          => request()->routeIs('projet.risques.*','projet.problemes.*'),
            'gouvernance'      => request()->routeIs('projet.parties-prenantes.*','projet.lecons.*','intranet.rapports.*','projet.demandes.*'),
            'referentiels-pr'  => request()->routeIs('admin.roles-projet.*'),
        ];
        if (!array_filter($accOpenPr)) $accOpenPr['planification'] = true;
        @endphp

        <div class="sb-accordion sb-accordion-projet" id="sbAccordionProjet">

            {{-- ══════ PLANIFICATION ═══════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['planification'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-planification"
                        aria-expanded="{{ $accOpenPr['planification'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-sitemap"></i></span>
                        <span class="sb-acc-name">Planification</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenPr['planification'] ? 'show' : '' }}" id="acc-pr-planification" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        @php $L = $projetLink('projet.wbs.index', 'projet.wbs.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">WBS & Phases</span>
                        </a></li>
                        @php $L = $projetLink('projet.jalons.index', 'projet.jalons.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-flag-checkered"></i></span><span class="sb-label">Jalons</span>
                        </a></li>
                        @php $L = $projetLink('projet.ressources.index', 'projet.ressources.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-people-carry-box"></i></span><span class="sb-label">Ressources</span>
                        </a></li>
                        <li><a href="{{ route('intranet.calendrier') }}" class="sb-link {{ request()->routeIs('intranet.calendrier') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-calendar-days"></i></span><span class="sb-label">Calendrier</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ EXÉCUTION ═══════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['execution'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-execution"
                        aria-expanded="{{ $accOpenPr['execution'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-bars-progress"></i></span>
                        <span class="sb-acc-name">Exécution</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenPr['execution'] ? 'show' : '' }}" id="acc-pr-execution" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        {{-- Note : « Tâches » a été promu en lien direct au-dessus de l'accordéon. --}}
                        @php $L = $projetLink('projet.feuilles-temps.index', 'projet.feuilles-temps.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-clock-rotate-left"></i></span><span class="sb-label">Feuilles de temps</span>
                        </a></li>
                        @php $L = $projetLink('projet.livrables.index', 'projet.livrables.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-box-open"></i></span><span class="sb-label">Livrables</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ MAÎTRISE ════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['maitrise'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-maitrise"
                        aria-expanded="{{ $accOpenPr['maitrise'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="sb-acc-name">Maîtrise</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenPr['maitrise'] ? 'show' : '' }}" id="acc-pr-maitrise" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        @php $L = $projetLink('projet.couts.index', 'projet.couts.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-coins"></i></span><span class="sb-label">Coûts & Budget</span>
                        </a></li>
                        @php $L = $projetLink('projet.evm.index', 'projet.evm.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-chart-line"></i></span><span class="sb-label">Valeur acquise (EVM)</span>
                        </a></li>
                        @php $L = $projetLink('projet.changements.index', 'projet.changements.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-code-compare"></i></span><span class="sb-label">Changements</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ RISQUES ═════════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['risques'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-risques"
                        aria-expanded="{{ $accOpenPr['risques'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-triangle-exclamation"></i></span>
                        <span class="sb-acc-name">Risques</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenPr['risques'] ? 'show' : '' }}" id="acc-pr-risques" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        @php $L = $projetLink('projet.risques.index', 'projet.risques.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-triangle-exclamation"></i></span><span class="sb-label">Registre des risques</span>
                        </a></li>
                        @php $L = $projetLink('projet.problemes.index', 'projet.problemes.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-circle-exclamation"></i></span><span class="sb-label">Journal des problèmes</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ GOUVERNANCE & CLÔTURE ═══════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['gouvernance'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-gouvernance"
                        aria-expanded="{{ $accOpenPr['gouvernance'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-users-between-lines"></i></span>
                        <span class="sb-acc-name">Gouvernance</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($rapportsBrouillon))<span class="sb-acc-badge-dot"></span>@endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpenPr['gouvernance'] ? 'show' : '' }}" id="acc-pr-gouvernance" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        @php $L = $projetLink('projet.parties-prenantes.index', 'projet.parties-prenantes.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-users-between-lines"></i></span><span class="sb-label">Parties prenantes</span>
                        </a></li>
                        @php $L = $projetLink('projet.lecons.index', 'projet.lecons.*'); @endphp
                        <li><a href="{{ $L['href'] }}" class="{{ $L['class'] }}" @if($L['title']) title="{{ $L['title'] }}" @endif>
                            <span class="sb-icon"><i class="fas fa-lightbulb"></i></span><span class="sb-label">Leçons apprises</span>
                        </a></li>
                        <li><a href="{{ route('intranet.rapports.index') }}" class="sb-link {{ request()->routeIs('intranet.rapports.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-file-lines"></i></span><span class="sb-label">Rapports & CR</span>
                            @if(!empty($rapportsBrouillon))<span class="sb-badge-alert">{{ $rapportsBrouillon }}</span>@endif
                        </a></li>
                        <li><a href="{{ route('projet.demandes.index') }}" class="sb-link {{ request()->routeIs('projet.demandes.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-pen-fancy"></i></span><span class="sb-label">Demandes modif.</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ RÉFÉRENTIELS PROJET ═══════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenPr['referentiels-pr'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-pr-referentiels"
                        aria-expanded="{{ $accOpenPr['referentiels-pr'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-database"></i></span>
                        <span class="sb-acc-name">Référentiels</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenPr['referentiels-pr'] ? 'show' : '' }}" id="acc-pr-referentiels" data-bs-parent="#sbAccordionProjet">
                    <ul class="sb-group sb-module-projet sb-acc-body">
                        <li><a href="{{ route('admin.roles-projet.index') }}" class="sb-link {{ request()->routeIs('admin.roles-projet.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-user-tag"></i></span><span class="sb-label">Rôles projet</span>
                        </a></li>
                    </ul>
                </div>
            </div>

        </div>{{-- /sb-accordion-projet --}}
    </nav>


    {{-- ════════════════════════════════════════════════════
         OBJECTIFS & KPI MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'objectifs')

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#DB2777,#BE185D); --mod-color: #F472B6;">
        <div class="sb-mod-icon"><i class="fas fa-bullseye"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Objectifs & KPI</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">

        {{-- Lien direct (hors accordéon) --}}
        <ul class="sb-group sb-module-objectifs" style="margin-bottom:.35rem;">
            <li>
                <a href="{{ route('objectifs.dashboard') }}" class="sb-link {{ request()->routeIs('objectifs.dashboard') ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="sb-label">Tableau de bord</span>
                </a>
            </li>
        </ul>

        @php
        $accOpenOb = [
            'objectifs'    => request()->routeIs('objectifs.objectifs.*','objectifs.plans-action.*'),
            'indicateurs'  => request()->routeIs('objectifs.kpi.*'),
            'evaluations'  => request()->routeIs('objectifs.evaluations.*'),
        ];
        if (!array_filter($accOpenOb)) $accOpenOb['objectifs'] = true;
        @endphp

        <div class="sb-accordion sb-accordion-objectifs" id="sbAccordionObjectifs">

            {{-- ══════ OBJECTIFS ═══════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenOb['objectifs'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-ob-objectifs"
                        aria-expanded="{{ $accOpenOb['objectifs'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="sb-acc-name">Objectifs</span>
                    </span>
                    <span class="sb-acc-right">
                        @if(!empty($objectifsActifs))<span class="sb-acc-badge-dot"></span>@endif
                        <i class="fas fa-chevron-down sb-acc-chevron"></i>
                    </span>
                </button>
                <div class="collapse {{ $accOpenOb['objectifs'] ? 'show' : '' }}" id="acc-ob-objectifs" data-bs-parent="#sbAccordionObjectifs">
                    <ul class="sb-group sb-module-objectifs sb-acc-body">
                        <li><a href="{{ route('objectifs.objectifs.index', ['type' => 'strategique']) }}" class="sb-link {{ request()->routeIs('objectifs.objectifs.*') && request('type') === 'strategique' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-bullseye"></i></span><span class="sb-label">Stratégiques</span>
                            @if(!empty($objectifsActifs))<span class="sb-badge-alert">{{ $objectifsActifs }}</span>@endif
                        </a></li>
                        <li><a href="{{ route('objectifs.objectifs.index', ['type' => 'operationnel']) }}" class="sb-link {{ request()->routeIs('objectifs.objectifs.*') && request('type') === 'operationnel' ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-crosshairs"></i></span><span class="sb-label">Opérationnels</span>
                        </a></li>
                        <li><a href="{{ route('objectifs.objectifs.index') }}" class="sb-link {{ request()->routeIs('objectifs.objectifs.*') && !request('type') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-list-check"></i></span><span class="sb-label">Tous les objectifs</span>
                        </a></li>
                        <li><a href="{{ route('objectifs.plans-action.index') }}" class="sb-link {{ request()->routeIs('objectifs.plans-action.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-list-ul"></i></span><span class="sb-label">Plans d'action</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ INDICATEURS (KPI) ═══════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenOb['indicateurs'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-ob-indicateurs"
                        aria-expanded="{{ $accOpenOb['indicateurs'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="sb-acc-name">Indicateurs</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenOb['indicateurs'] ? 'show' : '' }}" id="acc-ob-indicateurs" data-bs-parent="#sbAccordionObjectifs">
                    <ul class="sb-group sb-module-objectifs sb-acc-body">
                        <li><a href="{{ route('objectifs.kpi.index') }}" class="sb-link {{ request()->routeIs('objectifs.kpi.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-chart-line"></i></span><span class="sb-label">Tableau de KPI</span>
                            @if(!empty($totalKpi))<span class="sb-badge-alert">{{ $totalKpi }}</span>@endif
                        </a></li>
                    </ul>
                </div>
            </div>

            {{-- ══════ ÉVALUATIONS ═════════════════════════ --}}
            <div class="sb-acc-item">
                <button class="sb-acc-toggle {{ $accOpenOb['evaluations'] ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#acc-ob-evaluations"
                        aria-expanded="{{ $accOpenOb['evaluations'] ? 'true' : 'false' }}">
                    <span class="sb-acc-left">
                        <span class="sb-acc-icon"><i class="fas fa-star-half-stroke"></i></span>
                        <span class="sb-acc-name">Évaluations</span>
                    </span>
                    <span class="sb-acc-right"><i class="fas fa-chevron-down sb-acc-chevron"></i></span>
                </button>
                <div class="collapse {{ $accOpenOb['evaluations'] ? 'show' : '' }}" id="acc-ob-evaluations" data-bs-parent="#sbAccordionObjectifs">
                    <ul class="sb-group sb-module-objectifs sb-acc-body">
                        <li><a href="{{ route('objectifs.evaluations.index') }}" class="sb-link {{ request()->routeIs('objectifs.evaluations.*') ? 'active' : '' }}">
                            <span class="sb-icon"><i class="fas fa-star-half-stroke"></i></span><span class="sb-label">Toutes les évaluations</span>
                        </a></li>
                    </ul>
                </div>
            </div>

        </div>{{-- /sb-accordion-objectifs --}}
    </nav>


    {{-- ════════════════════════════════════════════════════
         SOCIAL MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'social')

    <div class="sb-module-brand" style="--mod-grd: #0A66C2; --mod-color: #F5B800;">
        <div class="sb-mod-icon"><i class="fas fa-users"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Réseau social</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">
        <ul class="sb-group" style="margin-top:.75rem;">
            <li>
                <a href="{{ route('social.dashboard') }}" class="sb-link {{ request()->routeIs('social.dashboard') && (request()->query('filter','feed') === 'feed') ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-house"></i></span><span class="sb-label">Fil d'actualité</span>
                </a>
            </li>
            <li>
                <a href="{{ route('social.dashboard', ['filter' => 'mine']) }}" class="sb-link {{ request()->query('filter') === 'mine' ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-user-pen"></i></span><span class="sb-label">Mes publications</span>
                </a>
            </li>
            <li>
                <a href="{{ route('social.groups.index') }}" class="sb-link {{ request()->routeIs('social.groups.*') ? 'active' : '' }}">
                    <span class="sb-icon"><i class="fas fa-comments"></i></span><span class="sb-label">Groupes de discussion</span>
                </a>
            </li>
            <li>
                <a href="{{ route('intranet.mediatheque.index') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-images"></i></span><span class="sb-label">Médiathèque</span>
                </a>
            </li>
        </ul>

        {{-- ── Modules métier ────────────────────────────── --}}
        <div class="sb-section-header" style="margin-top:1.25rem;">
            <span class="sb-section-dot" style="background:#475569;"></span>
            <span class="sb-section-name" style="color:#475569;">Modules</span>
        </div>
        <ul class="sb-group">
            <li>
                <a href="{{ route('dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-globe"></i></span><span class="sb-label">Intranet</span>
                </a>
            </li>
            @canany(['read:exercice','read:budget','read:grandlivre','read:compte'])
            <li>
                <a href="{{ route('finance.dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-coins"></i></span><span class="sb-label">Finance & Budget</span>
                </a>
            </li>
            @endcanany
            @canany(['read:employee','read:absence','read:paie','read:recrutement'])
            <li>
                <a href="{{ route('rh.dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-users"></i></span><span class="sb-label">RH & Paiement</span>
                </a>
            </li>
            @endcanany
            @canany(['read:fournisseur','read:produit','read:commande','read:immobilisation','read:dysfonctionnement','read:intervention'])
            <li>
                <a href="{{ route('appro.dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-cart-flatbed"></i></span><span class="sb-label">Achats & MG</span>
                </a>
            </li>
            @endcanany
            <li>
                <a href="{{ route('projet.dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-diagram-project"></i></span><span class="sb-label">Projets / Tâches</span>
                </a>
            </li>
            <li>
                <a href="{{ route('objectifs.dashboard') }}" class="sb-link">
                    <span class="sb-icon"><i class="fas fa-bullseye"></i></span><span class="sb-label">Objectifs & KPI</span>
                </a>
            </li>
        </ul>
    </nav>

    {{-- ════════════════════════════════════════════════════
         ADMINISTRATION MODE
    ════════════════════════════════════════════════════ --}}
    @elseif($mod === 'admin')

    <div class="sb-module-brand" style="--mod-grd: linear-gradient(135deg,#1E293B,#334155); --mod-color: #94A3B8;">
        <div class="sb-mod-icon"><i class="fas fa-shield-halved"></i></div>
        <div class="sb-mod-brand-text">
            <span class="sb-mod-name">Administration</span>
            <a href="{{ route('dashboard') }}" class="sb-back-portal">
                <i class="fas fa-arrow-left"></i> Portail
            </a>
        </div>
    </div>

    <nav class="sb-nav">
        {{-- Accès direct — 4 actions prioritaires --}}
        <ul class="sb-group sb-module-admin" style="margin-top:.5rem; margin-bottom:.35rem;">
            <li><a href="{{ route('admin.users.index') }}" class="sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-users-cog"></i></span><span class="sb-label">Utilisateurs</span>
            </a></li>
            <li><a href="{{ route('admin.roles.index') }}" class="sb-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-shield-halved"></i></span><span class="sb-label">Rôles</span>
            </a></li>
            <li><a href="{{ route('admin.permissions.index') }}" class="sb-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-key"></i></span><span class="sb-label">Permissions</span>
            </a></li>
            <li><a href="{{ route('systeme.organisations.index') }}" class="sb-link {{ request()->routeIs('systeme.organisations.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">Organisations</span>
            </a></li>
            <li><a href="{{ route('admin.config.index') }}" class="sb-link {{ request()->routeIs('admin.config.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-sliders"></i></span><span class="sb-label">Configuration</span>
            </a></li>
        </ul>

        <div class="sb-section-header admin">
            <span class="sb-section-dot"></span>
            <span class="sb-section-name">Référentiels intranet</span>
        </div>
        <ul class="sb-group sb-module-admin">
            <li><a href="{{ route('admin.statuts.index') }}" class="sb-link {{ request()->routeIs('admin.statuts.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-flag"></i></span><span class="sb-label">Statuts</span>
            </a></li>
            <li><a href="{{ route('admin.priorites.index') }}" class="sb-link {{ request()->routeIs('admin.priorites.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-flag-checkered"></i></span><span class="sb-label">Priorités</span>
            </a></li>
        </ul>

        <div class="sb-section-header admin">
            <span class="sb-section-dot"></span>
            <span class="sb-section-name">Référentiels RH</span>
        </div>
        <ul class="sb-group sb-module-admin">
            <li><a href="{{ route('admin.referentiel-rh.index', 'types-contrat') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'types-contrat' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-file-contract"></i></span><span class="sb-label">Types de contrat</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'postes') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'postes' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-briefcase"></i></span><span class="sb-label">Postes</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'departements') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'departements' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-sitemap"></i></span><span class="sb-label">Départements</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'types-evenement') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'types-evenement' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-arrow-trend-up"></i></span><span class="sb-label">Types évèn. carrière</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'niveaux-qualification') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'niveaux-qualification' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-graduation-cap"></i></span><span class="sb-label">Niveaux de qualification</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'nationalites') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'nationalites' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-flag"></i></span><span class="sb-label">Nationalités</span>
            </a></li>
            <li><a href="{{ route('admin.referentiel-rh.index', 'groupes-rubriques') }}" class="sb-link {{ request()->routeIs('admin.referentiel-rh.*') && request()->route('type') === 'groupes-rubriques' ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-layer-group"></i></span><span class="sb-label">Groupes rubriques paie</span>
            </a></li>
            @php
                $locTypes = ['pays-loc','provinces','departements-admin','prefectures','sous-prefectures','communes','arrondissements','quartiers','cantons','regroupements-village','villages'];
                $isLoc = request()->routeIs('admin.referentiel-rh.*') && in_array(request()->route('type'), $locTypes);
            @endphp
            <li><a href="{{ route('admin.referentiel-rh.index', 'pays-loc') }}" class="sb-link {{ $isLoc ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-map-location-dot"></i></span><span class="sb-label">Localisation administrative</span>
            </a></li>
        </ul>

        <div class="sb-section-header admin">
            <span class="sb-section-dot"></span>
            <span class="sb-section-name">Paramétrage</span>
        </div>
        <ul class="sb-group sb-module-admin">
            <li><a href="{{ route('admin.roles-projet.index') }}" class="sb-link {{ request()->routeIs('admin.roles-projet.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-user-tag"></i></span><span class="sb-label">Rôles projet</span>
            </a></li>
            <li><a href="{{ route('admin.vitrine.index') }}" class="sb-link {{ request()->routeIs('admin.vitrine.*') ? 'active' : '' }}">
                <span class="sb-icon"><i class="fas fa-globe"></i></span><span class="sb-label">Vitrine (site public)</span>
            </a></li>
        </ul>
    </nav>

    @endif

    {{-- ─── USER FOOTER (tous modes) ──────────────────────── --}}
    <div class="sb-user-footer">
        <div class="sb-user-avatar" style="@if($mod !== 'portal') background: var(--mod-grd, linear-gradient(135deg,#4F46E5,#7C3AED)); @endif">
            {{ strtoupper(substr(auth()->user()->prenoms ?? auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="sb-user-info">
            <span class="sb-user-name">{{ auth()->user()->prenoms ?? '' }} {{ auth()->user()->name }}</span>
            <span class="sb-user-role">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-logout-btn" title="Déconnexion">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </button>
        </form>
    </div>

</aside>
