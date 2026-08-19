{{-- ============================================================
     PARTIAL — En-tête projet + navigation PMP (réutilisable)
     Variable attendue : $projet, $currentPage (string)
     ============================================================ --}}

@php
    $currentPage = $currentPage ?? '';
    $pmpLinks = [
        ['route' => 'projet.overview',              'label' => 'Dashboard',    'icon' => 'fa-gauge-high',           'key' => 'overview'],
        ['route' => 'projet.wbs.index',              'label' => 'WBS',          'icon' => 'fa-sitemap',              'key' => 'wbs'],
        ['route' => 'projet.jalons.index',           'label' => 'Jalons',       'icon' => 'fa-flag-checkered',       'key' => 'jalons'],
        ['route' => 'projet.taches.index',            'label' => 'Tâches',       'icon' => 'fa-list-check',           'key' => 'taches'],
        ['route' => 'projet.ressources.index',       'label' => 'Ressources',   'icon' => 'fa-people-carry-box',     'key' => 'ressources'],
        ['route' => 'projet.feuilles-temps.index',   'label' => 'Temps',        'icon' => 'fa-clock-rotate-left',    'key' => 'feuilles-temps'],
        ['route' => 'projet.livrables.index',        'label' => 'Livrables',    'icon' => 'fa-box-open',             'key' => 'livrables'],
        ['route' => 'projet.couts.index',            'label' => 'Coûts',        'icon' => 'fa-coins',                'key' => 'couts'],
        ['route' => 'projet.evm.index',              'label' => 'EVM',          'icon' => 'fa-chart-line',           'key' => 'evm'],
        ['route' => 'projet.risques.index',          'label' => 'Risques',      'icon' => 'fa-triangle-exclamation', 'key' => 'risques'],
        ['route' => 'projet.problemes.index',        'label' => 'Problèmes',    'icon' => 'fa-circle-exclamation',   'key' => 'problemes'],
        ['route' => 'projet.changements.index',      'label' => 'Changements',  'icon' => 'fa-code-compare',         'key' => 'changements'],
        ['route' => 'projet.parties-prenantes.index', 'label' => 'Parties P.',  'icon' => 'fa-users-between-lines',  'key' => 'parties-prenantes'],
        ['route' => 'projet.lecons.index',           'label' => 'Leçons',       'icon' => 'fa-lightbulb',            'key' => 'lecons'],
    ];
@endphp

{{-- Navigation tabs PMP --}}
<div class="pmp-tabs-scroll mb-4">
    <div class="pmp-tabs">
        @foreach($pmpLinks as $link)
        <a href="{{ route($link['route'], $projet) }}"
           class="pmp-tab {{ $currentPage === $link['key'] ? 'active' : '' }}">
            <i class="fas {{ $link['icon'] }}"></i>
            <span>{{ $link['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>
