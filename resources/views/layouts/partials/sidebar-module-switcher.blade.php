{{-- ─── MODULE SWITCHER ─────────────────────────────────────
     Affiché en bas de chaque sidebar de module.
     Permet de naviguer rapidement vers un autre module
     sans repasser par le portail.
     ──────────────────────────────────────────────────────── --}}
<div class="sb-switcher">
    <div class="sb-switcher-label">Changer de module</div>
    <div class="sb-switcher-grid">
        @canany(['read:exercice','read:budget','read:grandlivre','read:compte'])
        <a href="{{ route('finance.dashboard') }}"
           class="sb-switcher-btn finance {{ request()->routeIs('finance.*') ? 'active' : '' }}"
           title="Finance & Budget">
            <i class="fas fa-coins"></i>
        </a>
        @endcanany

        @canany(['read:employee','read:absence','read:paie','read:recrutement'])
        <a href="{{ route('rh.dashboard') }}"
           class="sb-switcher-btn rh {{ request()->routeIs('rh.*') ? 'active' : '' }}"
           title="RH & Paiement">
            <i class="fas fa-users"></i>
        </a>
        @endcanany

        @canany(['read:fournisseur','read:produit','read:commande','read:immobilisation','read:dysfonctionnement','read:intervention'])
        <a href="{{ route('appro.commandes.index') }}"
           class="sb-switcher-btn appro {{ request()->routeIs('appro.*', 'mg.*', 'referentiel.*') ? 'active' : '' }}"
           title="Achats & Moyens Généraux">
            <i class="fas fa-cart-flatbed"></i>
        </a>
        @endcanany

        <a href="{{ route('projet.dashboard') }}"
           class="sb-switcher-btn projet {{ request()->routeIs('projet.*') ? 'active' : '' }}"
           title="Projets / Tâches">
            <i class="fas fa-diagram-project"></i>
        </a>

        <a href="{{ route('objectifs.dashboard') }}"
           class="sb-switcher-btn objectifs {{ request()->routeIs('objectifs.*') ? 'active' : '' }}"
           title="Objectifs & KPI">
            <i class="fas fa-bullseye"></i>
        </a>

        <a href="{{ route('dashboard') }}"
           class="sb-switcher-btn portal"
           title="Retour au portail">
            <i class="fas fa-home"></i>
        </a>
    </div>
</div>
