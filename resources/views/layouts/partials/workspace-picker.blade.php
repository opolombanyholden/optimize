{{-- ═══════════════════════════════════════════════════════════════
     Workspace Picker — Modale de choix d'espace de travail
     Disponible partout via window.openWorkspacePicker({ mandatory }).
     Auto-affichée en mode obligatoire si session('show_workspace_picker').
═══════════════════════════════════════════════════════════════ --}}
<div class="wp-overlay" id="workspacePickerOverlay" aria-modal="true" role="dialog"
     hidden
     data-auto-open="{{ session('show_workspace_picker') ? '1' : '0' }}">
    <div class="wp-panel">
        <div class="wp-header">
            <div>
                <h2 class="wp-title">Bienvenue, {{ auth()->user()->prenoms ?? auth()->user()->name }} 👋</h2>
                <p class="wp-subtitle" data-wp-subtitle-default="Choisissez votre espace de travail — ce choix est requis pour continuer."
                                     data-wp-subtitle-optional="Choisissez votre espace de travail.">
                    Choisissez votre espace de travail — ce choix est requis pour continuer.
                </p>
            </div>
            <button type="button" class="wp-close" id="workspacePickerClose" aria-label="Fermer" hidden>
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="wp-body">
            {{-- Ligne 1 : Espaces principaux --}}
            <div class="wp-section-label">Espaces principaux</div>
            <div class="wp-grid-top">
                <a href="{{ route('dashboard') }}" class="wp-card wp-card-hero wp-card-intranet" data-wp-choice="intranet">
                    <div class="wp-card-icon"><i class="fas fa-globe"></i></div>
                    <div class="wp-card-body">
                        <h3>Intranet</h3>
                        <p>Portail collaboratif : actualités, agenda, projets, courrier, annuaire.</p>
                    </div>
                    <i class="fas fa-arrow-right wp-card-arrow"></i>
                </a>
                <a href="{{ route('social.dashboard') }}" class="wp-card wp-card-hero wp-card-social" data-wp-choice="social">
                    <div class="wp-card-icon"><i class="fas fa-users"></i></div>
                    <div class="wp-card-body">
                        <h3>Réseau Social</h3>
                        <p>Fil de publications, groupes de discussion, échanges internes.</p>
                    </div>
                    <i class="fas fa-arrow-right wp-card-arrow"></i>
                </a>
                <a href="{{ route('docs.index') }}" class="wp-card wp-card-hero wp-card-docs" data-wp-choice="docs">
                    <div class="wp-card-icon"><i class="fas fa-book-open"></i></div>
                    <div class="wp-card-body">
                        <h3>Guide &amp; Docs</h3>
                        <p>Manuels de prise en main de chaque module, téléchargeables en PDF.</p>
                    </div>
                    <i class="fas fa-arrow-right wp-card-arrow"></i>
                </a>
            </div>

            {{-- Ligne 2 : Modules métier --}}
            <div class="wp-section-label" style="margin-top:2rem;">Modules métier</div>
            <div class="wp-grid-modules">
                @canany(['read:exercice','read:budget','read:grandlivre','read:compte'])
                <a href="{{ route('finance.dashboard') }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#DBEAFE; color:#1E40AF;"><i class="fas fa-coins"></i></div>
                    <div class="wp-card-body">
                        <h4>Finance</h4>
                        <p>Comptabilité, budget, factures</p>
                    </div>
                </a>
                @endcanany
                @canany(['read:employee','read:absence','read:paie','read:recrutement'])
                <a href="{{ route('rh.dashboard') }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#DCFCE7; color:#059669;"><i class="fas fa-users"></i></div>
                    <div class="wp-card-body">
                        <h4>GRH &amp; Paie</h4>
                        <p>Collaborateurs, absences, paie</p>
                    </div>
                </a>
                @endcanany
                @canany(['read:fournisseur','read:produit','read:commande','read:immobilisation','read:dysfonctionnement','read:intervention'])
                <a href="{{ route('appro.dashboard') }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#FFFBEB; color:#D97706;"><i class="fas fa-cart-flatbed"></i></div>
                    <div class="wp-card-body">
                        <h4>Achats &amp; MG</h4>
                        <p>Commandes, stocks, interventions</p>
                    </div>
                </a>
                @endcanany
                <a href="{{ route('projet.dashboard') }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#CCFBF1; color:#0D9488;"><i class="fas fa-diagram-project"></i></div>
                    <div class="wp-card-body">
                        <h4>Gestion Projet &amp; Tâche</h4>
                        <p>Projets, phases, tâches, jalons</p>
                    </div>
                </a>
                <a href="{{ route('objectifs.dashboard') }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#FCE7F3; color:#DB2777;"><i class="fas fa-bullseye"></i></div>
                    <div class="wp-card-body">
                        <h4>Stratégie</h4>
                        <p>Objectifs, KPI, pilotage</p>
                    </div>
                </a>
                <a href="{{ route('intranet.annuaire.collaborateurs.show', auth()->id()) }}" class="wp-card">
                    <div class="wp-card-icon" style="background:#F1F5F9; color:#334155;"><i class="fas fa-user-circle"></i></div>
                    <div class="wp-card-body">
                        <h4>Mon profil</h4>
                        <p>Ma fiche, mes préférences</p>
                    </div>
                </a>
                @role('super-admin|admin')
                <a href="{{ route('systeme.organisations.index') }}" class="wp-card" data-wp-choice="admin">
                    <div class="wp-card-icon" style="background:#FEE2E2; color:#DC2626;"><i class="fas fa-shield-halved"></i></div>
                    <div class="wp-card-body">
                        <h4>Administration</h4>
                        <p>Organisations, utilisateurs, rôles</p>
                    </div>
                </a>
                @endrole
            </div>
        </div>
    </div>
</div>

<style>
/* Overlay plein écran — fond sombre transparent laissant deviner l'écran derrière */
.wp-overlay {
    position:fixed; inset:0; z-index:2000;
    background:rgba(15, 23, 42, .55);
    display:flex; align-items:center; justify-content:center;
    padding:1rem;
    opacity:0;
    overflow-y:auto;
}
.wp-overlay[hidden] { display:none !important; }
.wp-overlay.wp-open { animation:wp-fade-in .35s ease-out forwards; }
@keyframes wp-fade-in {
    from { opacity:0; }
    to   { opacity:1; }
}

/* Bloc central blanc — contient uniquement le contenu (titre + cartes) */
.wp-panel {
    background:#fff; width:100%; max-width:960px;
    max-height:calc(100vh - 2rem); overflow-y:auto;
    transform:translateY(12px);
    position:relative;
}
.wp-overlay.wp-open .wp-panel { animation:wp-slide-in .35s ease-out .05s forwards; }
@keyframes wp-slide-in {
    to { transform:translateY(0); }
}

.wp-header {
    padding:1.5rem 2rem; border-bottom:1px solid #E0DFDC;
    display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;
}
.wp-title { font-size:1.5rem; font-weight:700; color:#191919; margin:0; }
.wp-subtitle { color:#666; margin:.35rem 0 0; font-size:.9rem; }

.wp-close {
    background:transparent; border:none; color:#666; font-size:1.1rem;
    width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;
    cursor:pointer; transition:background .15s, color .15s;
}
.wp-close:hover { background:#F4F2EE; color:#191919; }
.wp-close[hidden] { display:none !important; }

.wp-body { padding:1.5rem 2rem 2rem; }
.wp-section-label {
    font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em;
    color:#666; margin-bottom:.85rem;
}

.wp-grid-top { display:grid; grid-template-columns:repeat(3, 1fr); gap:1rem; margin-bottom:1rem; }
@media(max-width:900px) { .wp-grid-top { grid-template-columns:1fr 1fr; } }
@media(max-width:600px) { .wp-grid-top { grid-template-columns:1fr; } }

.wp-grid-modules {
    display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
    gap:.75rem;
}

.wp-card {
    display:flex; align-items:center; gap:1rem;
    padding:1rem 1.2rem; background:#fff; border:1px solid #E0DFDC;
    text-decoration:none; color:inherit;
    transition:border-color .15s, background .15s;
    position:relative;
}
.wp-card:hover { border-color:#0A66C2; background:#FAFAF9; color:inherit; }
.wp-card-icon {
    width:52px; height:52px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; background:#F4F2EE; color:#666;
}
.wp-card-body { flex:1; min-width:0; }
.wp-card h3, .wp-card h4 { margin:0; color:#191919; font-weight:700; }
.wp-card h3 { font-size:1.15rem; }
.wp-card h4 { font-size:.95rem; }
.wp-card p { margin:.2rem 0 0; font-size:.82rem; color:#666; line-height:1.35; }
.wp-card-arrow { color:#9CA3AF; font-size:.85rem; }
.wp-card:hover .wp-card-arrow { color:#0A66C2; }

.wp-card-hero { padding:1.35rem 1.5rem; }
.wp-card-hero .wp-card-icon { width:60px; height:60px; font-size:1.6rem; }
.wp-card-intranet .wp-card-icon { background:#DBEAFE; color:#0A66C2; }
.wp-card-social   .wp-card-icon { background:#FEF3C7; color:#B45309; }
.wp-card-docs     .wp-card-icon { background:#E9D5FF; color:#7C3AED; }
</style>

<script>
(function () {
    var overlay = document.getElementById('workspacePickerOverlay');
    if (!overlay) return;
    var closeBtn = document.getElementById('workspacePickerClose');
    var subtitle = overlay.querySelector('.wp-subtitle');
    var previousBodyOverflow = '';
    var currentMandatory = true;

    function onKeydown(e) {
        if (e.key !== 'Escape') return;
        if (currentMandatory) { e.preventDefault(); return; }
        close();
    }
    function onOverlayClick(e) {
        if (e.target !== overlay) return;
        if (currentMandatory) { e.preventDefault(); return; }
        close();
    }

    function open(opts) {
        opts = opts || {};
        currentMandatory = opts.mandatory !== false;
        // Sous-titre + bouton fermer selon le mode
        if (subtitle) {
            subtitle.textContent = currentMandatory
                ? (subtitle.dataset.wpSubtitleDefault || subtitle.textContent)
                : (subtitle.dataset.wpSubtitleOptional || subtitle.textContent);
        }
        if (closeBtn) closeBtn.hidden = currentMandatory;
        overlay.hidden = false;
        overlay.classList.add('wp-open');
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', onKeydown);
        overlay.addEventListener('click', onOverlayClick);
    }
    function close() {
        overlay.classList.remove('wp-open');
        overlay.hidden = true;
        document.body.style.overflow = previousBodyOverflow;
        document.removeEventListener('keydown', onKeydown);
        overlay.removeEventListener('click', onOverlayClick);
    }

    // API publique
    window.openWorkspacePicker = open;
    window.closeWorkspacePicker = close;

    if (closeBtn) closeBtn.addEventListener('click', close);

    // Auto-ouverture (mode obligatoire) si session flash présente
    document.addEventListener('DOMContentLoaded', function () {
        if (overlay.dataset.autoOpen === '1') open({ mandatory: true });
    });
})();
</script>
