<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Intranet\Media;
use App\Models\Intranet\News;
use App\Models\Intranet\Publication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Répartir des anniversaires sur les 7 prochains jours ──────
        $employees = Employee::orderBy('id')->take(6)->get();
        foreach ($employees as $i => $emp) {
            // Décale la date de naissance pour tomber dans les jours à venir
            $target = Carbon::today()->addDays($i)->subYears(30 + $i);
            $emp->update(['date_naissance' => $target]);
        }

        // ── 2. Publier 3 News en visibilité publique ─────────────────────
        $newsData = [
            [
                'title' => 'Lancement du nouveau portail collaboratif',
                'extrait' => "Nous ouvrons officiellement l'accès à OptimiZe pour tous les collaborateurs. Découvrez les nouveaux modules Achats, Moyens Généraux, RH et Finance intégrés.",
                'rubrique' => 'annonce',
            ],
            [
                'title' => 'Formation SAP : sessions d\'octobre',
                'extrait' => "Les inscriptions pour la formation SAP Business One sont ouvertes. Trois sessions prévues, priorité aux responsables projets.",
                'rubrique' => 'formation',
            ],
            [
                'title' => 'Journée portes ouvertes ANPI-Gabon',
                'extrait' => "Retour sur la JPO de la semaine dernière : plus de 200 visiteurs, 12 partenariats signés, une belle vitrine pour nos activités.",
                'rubrique' => 'evenement',
            ],
        ];

        $auteur = User::first();
        foreach ($newsData as $i => $data) {
            $news = News::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'content'    => '<p>'.$data['extrait'].'</p><p>Retrouvez le détail sur la page interne dédiée.</p>',
                    'created_by' => $auteur?->id,
                ]),
            );
            Publication::updateOrCreate(
                ['publishable_type' => News::class, 'publishable_id' => $news->id],
                [
                    'visibilite' => 'public',
                    'publie_le'  => now()->subDays($i),
                    'created_by' => $auteur?->id,
                ],
            );
        }

        // ── 3. Créer quelques médias de démo (URL Unsplash publiques) ─────
        $medias = [
            ['titre' => 'Équipe siège 2026',           'type' => 'image', 'ext' => 'photos-1522071820081-009f0129c71c'],
            ['titre' => 'Atelier logistique',           'type' => 'image', 'ext' => 'photos-1553413077-190dd305871c'],
            ['titre' => 'Salle de conférence',          'type' => 'image', 'ext' => 'photos-1497366216548-37526070297c'],
            ['titre' => 'Point d\'accueil ANPI',        'type' => 'image', 'ext' => 'photos-1497366754035-f200968a6e72'],
            ['titre' => 'Visite ministérielle',         'type' => 'image', 'ext' => 'photos-1560250097-0b93528c311a'],
            ['titre' => 'Team building 2026',           'type' => 'image', 'ext' => 'photos-1522202176988-66273c2fd55f'],
        ];
        foreach ($medias as $i => $m) {
            $url = "https://images.unsplash.com/{$m['ext']}?w=600";
            Media::firstOrCreate(
                ['titre' => $m['titre']],
                [
                    'type'          => $m['type'],
                    'fichier'       => $url,
                    'nom_original'  => \Illuminate\Support\Str::slug($m['titre']).'.jpg',
                    'url_externe'   => $url,
                    'thumbnail_url' => $url,
                    'mime_type'     => 'image/jpeg',
                    'taille'        => 0,
                    'is_public'     => true,
                    'created_by'    => $auteur?->id,
                ],
            );
        }
    }
}
