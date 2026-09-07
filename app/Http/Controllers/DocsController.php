<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DocsController extends Controller
{
    /**
     * Liste des guides disponibles.
     * Structure : slug => métadonnées. Ajouter un guide = ajouter une entrée + créer la vue docs/guides/{slug}.blade.php
     */
    protected array $guides = [
        'achats-mg' => [
            'title'       => 'Achats & Moyens Généraux',
            'subtitle'    => 'Manuel du responsable — configuration du référentiel, commandes, stock, engagements, dysfonctionnements et interventions.',
            'icon'        => 'fa-cart-flatbed',
            'accent'      => '#D97706',
            'accent_soft' => '#FEF3C7',
            'audience'    => 'Responsable Achats & MG',
            'duration'    => '≈ 45 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-28',
        ],
        'intranet' => [
            'title'       => 'Intranet & Collaboration',
            'subtitle'    => 'Manuel du collaborateur — annonces, actualités, agenda, CRM, courrier, ressources et médiathèque, wiki, annuaire.',
            'icon'        => 'fa-globe',
            'accent'      => '#0A66C2',
            'accent_soft' => '#DBEAFE',
            'audience'    => 'Tous les collaborateurs · animateurs de communication',
            'duration'    => '≈ 40 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-29',
        ],
        'social' => [
            'title'       => 'Réseau Social interne',
            'subtitle'    => 'Publications, likes, commentaires, groupes de discussion, partage de contenu.',
            'icon'        => 'fa-users',
            'accent'      => '#B45309',
            'accent_soft' => '#FEF3C7',
            'audience'    => 'Tous collaborateurs',
            'duration'    => 'Bientôt disponible',
            'chapters'    => 0,
            'status'      => 'soon',
        ],
        'finance' => [
            'title'       => 'Finance & Budget',
            'subtitle'    => 'Manuel du responsable financier — exercice budgétaire, planification, modifications, exécution, comptabilité générale, factures et trésorerie.',
            'icon'        => 'fa-coins',
            'accent'      => '#4F46E5',
            'accent_soft' => '#E0E7FF',
            'audience'    => 'Responsable financier, comptable, DAF',
            'duration'    => '≈ 55 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-29',
        ],
        'rh' => [
            'title'       => 'GRH & Paie',
            'subtitle'    => 'Manuel du DRH — référentiel, cycle de vie collaborateur, grades et avancements, absences, paie, performance.',
            'icon'        => 'fa-user-tie',
            'accent'      => '#059669',
            'accent_soft' => '#D1FAE5',
            'audience'    => 'DRH, gestionnaire de paie, RRH',
            'duration'    => '≈ 50 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-29',
        ],
        'projet' => [
            'title'       => 'Gestion de Projet & Tâche (PMP)',
            'subtitle'    => 'Manuel du chef de projet — planification WBS, jalons, tâches, maîtrise EVM, risques, gouvernance et clôture.',
            'icon'        => 'fa-diagram-project',
            'accent'      => '#0D9488',
            'accent_soft' => '#CCFBF1',
            'audience'    => 'Chef de projet, membre d\'équipe, PMO, directeur opérationnel',
            'duration'    => '≈ 50 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-31',
        ],
        'strategie' => [
            'title'       => 'Stratégie & KPI',
            'subtitle'    => 'Manuel du pilote stratégique — objectifs, KPI, plans d\'action, évaluations, cycle d\'amélioration continue.',
            'icon'        => 'fa-bullseye',
            'accent'      => '#DB2777',
            'accent_soft' => '#FCE7F3',
            'audience'    => 'Direction générale, contrôle de gestion, responsable qualité',
            'duration'    => '≈ 45 min',
            'chapters'    => 5,
            'status'      => 'ready',
            'version'     => '1.0',
            'updated'     => '2026-08-31',
        ],
        'admin' => [
            'title'       => 'Administration système',
            'subtitle'    => 'Organisations, utilisateurs, rôles, permissions, paramétrage global.',
            'icon'        => 'fa-shield-halved',
            'accent'      => '#DC2626',
            'accent_soft' => '#FEE2E2',
            'audience'    => 'Super-administrateur, administrateur',
            'duration'    => 'Bientôt disponible',
            'chapters'    => 0,
            'status'      => 'soon',
        ],
    ];

    public function index()
    {
        return view('docs.index', ['guides' => $this->guides]);
    }

    public function show(string $slug)
    {
        abort_unless(isset($this->guides[$slug]), 404);
        $meta = $this->guides[$slug];
        abort_unless($meta['status'] === 'ready', 404, 'Guide non encore publié');

        $view = 'docs.guides.' . $slug;
        abort_unless(view()->exists($view), 404);

        return view('docs.show', [
            'guide' => $meta,
            'slug'  => $slug,
            'body'  => $view,
            'forPdf' => false,
        ]);
    }

    public function pdf(string $slug)
    {
        abort_unless(isset($this->guides[$slug]), 404);
        $meta = $this->guides[$slug];
        abort_unless($meta['status'] === 'ready', 404, 'Guide non encore publié');

        $view = 'docs.guides.' . $slug;
        abort_unless(view()->exists($view), 404);

        $pdf = Pdf::loadView('docs.pdf', [
            'guide'  => $meta,
            'slug'   => $slug,
            'body'   => $view,
            'forPdf' => true,
        ])->setPaper('A4', 'portrait')
          ->setOptions([
              'defaultFont'         => 'DejaVu Sans',
              'isRemoteEnabled'     => false,
              'isHtml5ParserEnabled'=> true,
              'chroot'              => realpath(base_path()),
          ]);

        $filename = 'OptimiZe-Guide-' . Str::slug($meta['title']) . '.pdf';
        return $pdf->download($filename);
    }
}
