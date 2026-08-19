<?php

namespace Database\Seeders;

use App\Models\Vitrine\Atout;
use App\Models\Vitrine\Capture;
use App\Models\Vitrine\Module;
use App\Models\Vitrine\Setting;
use App\Models\Vitrine\SlideHero;
use Illuminate\Database\Seeder;

class VitrineSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSlides();
        $this->seedModules();
        $this->seedCaptures();
        $this->seedAtouts();
        $this->seedSettings();

        $this->command->info(sprintf(
            'Vitrine seeded: %d slides, %d modules, %d captures, %d atouts, %d settings.',
            SlideHero::count(), Module::count(), Capture::count(), Atout::count(), Setting::count()
        ));
    }

    private function seedSlides(): void
    {
        $slides = [
            [
                'eyebrow' => 'ERP nouvelle génération', 'eyebrow_icone' => 'fa-bolt', 'eyebrow_couleur' => '#0D9488',
                'titre' => 'Centralisez votre gestion <br>dans <span class="highlight">une seule plateforme</span>',
                'sous_titre' => 'OptimiZe unifie Finance, RH, Achats, Projets et Objectifs stratégiques pour une vision 360° de votre organisation. Simple, sécurisé, conçu pour les entreprises africaines.',
                'mockup_type' => 'dashboard',
                'stats' => [['num'=>'7','label'=>'Modules intégrés'],['num'=>'100%','label'=>'Cloud sécurisé'],['num'=>'XAF','label'=>'Multi-devises']],
                'ordre' => 1,
            ],
            [
                'eyebrow' => 'Gestion de projet PMBOK', 'eyebrow_icone' => 'fa-diagram-project', 'eyebrow_couleur' => '#0F766E',
                'titre' => 'Pilotez vos projets avec la <span class="highlight">méthodologie PMP</span>',
                'sous_titre' => 'WBS, jalons, ressources, EVM, risques, validation de clôture multi-niveaux. Tout ce dont un Chef de projet a besoin, dans une interface fluide et conforme aux standards internationaux.',
                'mockup_type' => 'wbs',
                'stats' => [['num'=>'14','label'=>'Composants PMP'],['num'=>'100%','label'=>'Cascade auto'],['num'=>'N+1','label'=>'Validation']],
                'ordre' => 2,
            ],
            [
                'eyebrow' => 'Pilotage stratégique OKR', 'eyebrow_icone' => 'fa-bullseye', 'eyebrow_couleur' => '#BE185D',
                'titre' => 'Alignez vos équipes sur les <span class="highlight">bons objectifs</span>',
                'sous_titre' => 'Définissez vos OKR, suivez vos KPI en temps réel, déclinez vos objectifs en plans d\'action concrets. La performance commence par la mesure.',
                'mockup_type' => 'okr',
                'stats' => [['num'=>'OKR','label'=>'Méthodologie'],['num'=>'∞','label'=>'KPI personnalisés'],['num'=>'360°','label'=>'Évaluations']],
                'ordre' => 3,
            ],
            [
                'eyebrow' => 'Gouvernance & sécurité', 'eyebrow_icone' => 'fa-shield-halved', 'eyebrow_couleur' => '#4F46E5',
                'titre' => 'Validation, traçabilité et <span class="highlight">contrôle total</span>',
                'sous_titre' => 'Workflow d\'approbation multi-niveaux, demandes de modification verrouillées, journal d\'audit complet. Vos données sont protégées par les plus hauts standards.',
                'mockup_type' => 'validation',
                'stats' => [['num'=>'RBAC','label'=>'Permissions fines'],['num'=>'100%','label'=>'Audit trail'],['num'=>'RGPD','label'=>'Compliant']],
                'ordre' => 4,
            ],
        ];
        foreach ($slides as $s) SlideHero::firstOrCreate(['titre' => $s['titre']], $s + ['est_actif' => true]);
    }

    private function seedModules(): void
    {
        $modules = [
            ['nom' => 'Finance & Budget',         'icone' => 'fa-coins',           'couleur' => '#4F46E5', 'description' => 'Comptabilité analytique, exercices budgétaires, grand livre, rapprochements bancaires.', 'features' => ['Exercices multi-annuels','Grand livre & comptes','Suivi budgétaire'], 'ordre' => 1],
            ['nom' => 'RH & Paiement',            'icone' => 'fa-users',           'couleur' => '#16A34A', 'description' => 'Gestion des employés, contrats, absences, paie, formations et carrière.',                'features' => ['Dossier employé complet','Workflow d\'absences','Paie & rubriques'],     'ordre' => 2],
            ['nom' => 'Achats & Appro',           'icone' => 'fa-cart-flatbed',    'couleur' => '#D97706', 'description' => 'Fournisseurs, catalogue produits, commandes, gestion de stock et approvisionnements.','features' => ['Pipeline fournisseurs','Commandes validées','Gestion de stock'],          'ordre' => 3],
            ['nom' => 'Moyens Généraux',          'icone' => 'fa-building',        'couleur' => '#0891B2', 'description' => 'Immobilisations, amortissements, dysfonctionnements, interventions techniques.',     'features' => ['Inventaire patrimoine','Amortissements auto','Tickets incidents'],         'ordre' => 4],
            ['nom' => 'Projets / PMP',            'icone' => 'fa-diagram-project', 'couleur' => '#0D9488', 'description' => 'Gestion de projet conforme PMBOK : WBS, jalons, EVM, risques, parties prenantes.',   'features' => ['WBS hiérarchique','Earned Value Management','Validation de clôture'],      'ordre' => 5],
            ['nom' => 'Objectifs & KPI',          'icone' => 'fa-bullseye',        'couleur' => '#DB2777', 'description' => 'OKR, indicateurs de performance, plans d\'action, évaluations 360°.',                  'features' => ['Hiérarchie OKR','KPI temps réel','Plans d\'action déclinés'],              'ordre' => 6],
            ['nom' => 'Intranet collaboratif',    'icone' => 'fa-comments',        'couleur' => '#7C3AED', 'description' => 'Annonces, agenda, CRM, GED, wiki, annuaire — la communication interne unifiée.',     'features' => ['Communication interne','CRM & opportunités','GED & médiathèque'],         'ordre' => 7],
            ['nom' => 'Administration',           'icone' => 'fa-shield-halved',   'couleur' => '#475569', 'description' => 'Utilisateurs, rôles, permissions fines, paramétrage des référentiels métier.',       'features' => ['RBAC complet','Multi-organisations','Audit trail'],                       'ordre' => 8],
        ];
        foreach ($modules as $m) Module::firstOrCreate(['nom' => $m['nom']], $m + ['est_actif' => true]);
    }

    private function seedCaptures(): void
    {
        $captures = [
            ['titre' => 'Vue 360° de votre organisation',     'tag' => 'Tableau de bord', 'tag_couleur' => '#0D9488', 'url_affichee' => '/dashboard',         'mockup_type' => 'dashboard', 'description' => 'KPI en un coup d\'œil, modules accessibles en 1 clic, alertes contextuelles.', 'ordre' => 1],
            ['titre' => 'Décomposition projet structurée',    'tag' => 'PMP / WBS',       'tag_couleur' => '#0F766E', 'url_affichee' => '/projet/5/wbs',      'mockup_type' => 'wbs',       'description' => 'Phases, sous-phases, tâches avec progression cascade automatique.',          'ordre' => 2],
            ['titre' => 'Vue Kanban et drag & drop',          'tag' => 'Tâches',          'tag_couleur' => '#5B21B6', 'url_affichee' => '/intranet/taches',   'mockup_type' => 'kanban',    'description' => 'Plusieurs vues : liste, table, kanban. Glisser-déposer entre colonnes.',     'ordre' => 3],
            ['titre' => 'Trombinoscope dynamique',            'tag' => 'Annuaire',        'tag_couleur' => '#0E7490', 'url_affichee' => '/intranet/annuaire', 'mockup_type' => 'annuaire',  'description' => 'Recherche, filtres par service, fiche détaillée pour chaque collaborateur.','ordre' => 4],
            ['titre' => 'Pilotage stratégique en temps réel', 'tag' => 'OKR & KPI',       'tag_couleur' => '#BE185D', 'url_affichee' => '/objectifs',         'mockup_type' => 'okr',       'description' => 'OKR pondérés, KPI avec alertes, plans d\'action déclinés.',                'ordre' => 5],
            ['titre' => 'Workflow de clôture multi-niveaux', 'tag' => 'Validation',      'tag_couleur' => '#4338CA', 'url_affichee' => '/projet/cloture',    'mockup_type' => 'validation','description' => 'Soumission, validation N+1, traçabilité complète, cascade automatique.',  'ordre' => 6],
        ];
        foreach ($captures as $c) Capture::firstOrCreate(['titre' => $c['titre']], $c + ['est_actif' => true]);
    }

    private function seedAtouts(): void
    {
        $atouts = [
            ['titre' => 'Tout en un',           'icone' => 'fa-bolt',           'gradient_from' => '#0D9488', 'gradient_to' => '#0F766E', 'description' => '7 modules intégrés. Fini les outils éparpillés et les ressaisies inutiles.',                       'ordre' => 1],
            ['titre' => 'Sécurité enterprise', 'icone' => 'fa-shield-halved',  'gradient_from' => '#4F46E5', 'gradient_to' => '#7C3AED', 'description' => 'RBAC, audit trail, validation N+1, données chiffrées. Souveraineté garantie.',                  'ordre' => 2],
            ['titre' => 'Pilotage temps réel',  'icone' => 'fa-chart-line',     'gradient_from' => '#DB2777', 'gradient_to' => '#BE185D', 'description' => 'Dashboards riches, KPI cascade, alertes proactives. Décidez vite et bien.',                       'ordre' => 3],
            ['titre' => 'Déploiement rapide',   'icone' => 'fa-rocket',         'gradient_from' => '#F59E0B', 'gradient_to' => '#D97706', 'description' => 'Installation en jours, pas en mois. Données de démo et formation incluses.',                     'ordre' => 4],
            ['titre' => 'Cloud ou on-premise',  'icone' => 'fa-cloud',          'gradient_from' => '#0891B2', 'gradient_to' => '#0E7490', 'description' => 'Au choix : SaaS clé en main ou installation sur vos serveurs. Vos données, vos règles.',         'ordre' => 5],
            ['titre' => 'Support local',        'icone' => 'fa-handshake',      'gradient_from' => '#16A34A', 'gradient_to' => '#15803D', 'description' => 'Équipe basée en Afrique, formations en français, accompagnement personnalisé.',                'ordre' => 6],
        ];
        foreach ($atouts as $a) Atout::firstOrCreate(['titre' => $a['titre']], $a + ['est_actif' => true]);
    }

    private function seedSettings(): void
    {
        $settings = [
            // General
            ['cle' => 'site_titre',        'valeur' => 'OptimiZe — La solution ERP intégrée', 'libelle' => 'Titre du site (balise <title>)',  'groupe' => 'general', 'type' => 'text',     'ordre' => 1],
            ['cle' => 'site_description',  'valeur' => 'OptimiZe centralise Finance, RH, Achats, Projets et Objectifs dans une seule plateforme moderne et sécurisée.', 'libelle' => 'Description meta SEO', 'groupe' => 'general', 'type' => 'textarea', 'ordre' => 2],
            ['cle' => 'marque_nom',        'valeur' => 'OptimiZe',                            'libelle' => 'Nom de la marque',                'groupe' => 'general', 'type' => 'text',     'ordre' => 3],
            ['cle' => 'marque_slogan',     'valeur' => 'La solution ERP intégrée pour les organisations africaines ambitieuses. Conçu et développé par Yubile Technologie.', 'libelle' => 'Slogan footer', 'groupe' => 'general', 'type' => 'textarea', 'ordre' => 4],

            // Sections
            ['cle' => 'modules_titre',       'valeur' => 'Tout ce qu\'il faut pour piloter votre organisation', 'libelle' => 'Titre section Modules',      'groupe' => 'sections', 'type' => 'text',     'ordre' => 1],
            ['cle' => 'modules_subtitle',    'valeur' => 'De la finance à la stratégie, OptimiZe couvre tous les besoins métier d\'une PME ou ETI moderne.', 'libelle' => 'Sous-titre section Modules', 'groupe' => 'sections', 'type' => 'textarea', 'ordre' => 2],
            ['cle' => 'captures_titre',      'valeur' => 'Une interface moderne, conçue pour la productivité', 'libelle' => 'Titre section Captures',     'groupe' => 'sections', 'type' => 'text',     'ordre' => 3],
            ['cle' => 'captures_subtitle',   'valeur' => 'Découvrez les écrans clés de la plateforme, optimisés pour l\'efficacité quotidienne.',          'libelle' => 'Sous-titre section Captures','groupe' => 'sections', 'type' => 'textarea', 'ordre' => 4],
            ['cle' => 'atouts_titre',        'valeur' => 'Conçu pour les entreprises africaines exigeantes',  'libelle' => 'Titre section Atouts',       'groupe' => 'sections', 'type' => 'text',     'ordre' => 5],
            ['cle' => 'atouts_subtitle',     'valeur' => 'Une solution moderne qui combine standards internationaux et compréhension fine du contexte local.', 'libelle' => 'Sous-titre section Atouts', 'groupe' => 'sections', 'type' => 'textarea', 'ordre' => 6],
            ['cle' => 'cta_titre',           'valeur' => 'Prêt à transformer votre organisation ?',           'libelle' => 'Titre CTA',                  'groupe' => 'sections', 'type' => 'text',     'ordre' => 7],
            ['cle' => 'cta_subtitle',        'valeur' => 'Demandez une démo personnalisée et découvrez comment OptimiZe peut accélérer votre croissance.', 'libelle' => 'Sous-titre CTA',         'groupe' => 'sections', 'type' => 'textarea', 'ordre' => 8],
            ['cle' => 'logos_titre',         'valeur' => 'Conçu pour les organisations africaines ambitieuses', 'libelle' => 'Titre bandeau partenaires', 'groupe' => 'sections', 'type' => 'text',     'ordre' => 9],

            // Contact
            ['cle' => 'contact_email',     'valeur' => 'contact@yubile-tech.com',  'libelle' => 'Email de contact',          'groupe' => 'contact', 'type' => 'text', 'ordre' => 1],
            ['cle' => 'contact_adresse',   'valeur' => 'Libreville, Gabon',         'libelle' => 'Adresse',                   'groupe' => 'contact', 'type' => 'text', 'ordre' => 2],
            ['cle' => 'copyright_owner',   'valeur' => 'Yubile Technologie',        'libelle' => 'Propriétaire copyright',    'groupe' => 'contact', 'type' => 'text', 'ordre' => 3],

            // Réseaux sociaux
            ['cle' => 'social_linkedin',   'valeur' => '',  'libelle' => 'URL LinkedIn',  'groupe' => 'social', 'type' => 'url', 'ordre' => 1],
            ['cle' => 'social_twitter',    'valeur' => '',  'libelle' => 'URL Twitter/X', 'groupe' => 'social', 'type' => 'url', 'ordre' => 2],
            ['cle' => 'social_facebook',   'valeur' => '',  'libelle' => 'URL Facebook',  'groupe' => 'social', 'type' => 'url', 'ordre' => 3],

            // Partenaires (JSON)
            ['cle' => 'partenaires', 'valeur' => json_encode([
                ['nom' => 'YUBILE TECH',  'icone' => 'fa-building'],
                ['nom' => 'GREEN GROUP',  'icone' => 'fa-leaf'],
                ['nom' => 'ENERGY+',      'icone' => 'fa-bolt'],
                ['nom' => 'STARTUP HUB',  'icone' => 'fa-rocket'],
                ['nom' => 'AFRO CORP',    'icone' => 'fa-globe'],
            ], JSON_UNESCAPED_UNICODE), 'libelle' => 'Partenaires (JSON : nom + icone FA)', 'groupe' => 'partenaires', 'type' => 'textarea', 'ordre' => 1],
        ];

        foreach ($settings as $s) Setting::firstOrCreate(['cle' => $s['cle']], $s);
    }
}
