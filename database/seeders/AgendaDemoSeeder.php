<?php

namespace Database\Seeders;

use App\Models\Intranet\Evenement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgendaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $auteur = User::first()?->id ?? 1;

        // Types (assumés déjà présents : rdv / reunion / formation / conf / atelier)
        $types = DB::table('intranet_type_evenements')->pluck('id', 'code');

        $events = [
            [
                'titre'      => 'Réunion hebdo Comité de Direction',
                'extrait'    => 'Point d\'avancement des chantiers prioritaires.',
                'start'      => '+1 day 09:00',
                'duration_h' => 2,
                'lieu'       => 'Salle du Conseil',
                'type_code'  => 'reunion',
                'visio'      => false,
            ],
            [
                'titre'      => 'Formation SAP Business One — session 1',
                'extrait'    => 'Initiation au module Achats et gestion fournisseurs.',
                'start'      => '+2 days 14:00',
                'duration_h' => 4,
                'lieu'       => 'Salle formation étage 3',
                'type_code'  => 'formation',
                'visio'      => false,
            ],
            [
                'titre'      => 'Conférence ANPI-Gabon — Attractivité 2026',
                'extrait'    => 'Restitution des indicateurs et perspectives.',
                'start'      => '+3 days 10:00',
                'duration_h' => 3,
                'lieu'       => 'Auditorium Libreville',
                'type_code'  => 'conf',
                'visio'      => true,
            ],
            [
                'titre'      => 'Atelier Design Thinking — Parcours entrepreneur',
                'extrait'    => 'Refonte du parcours d\'accompagnement.',
                'start'      => '+4 days 15:00',
                'duration_h' => 3,
                'lieu'       => 'Espace collaboratif',
                'type_code'  => 'atelier',
                'visio'      => false,
            ],
            [
                'titre'      => 'RDV client — Groupe Simba SA',
                'extrait'    => 'Signature convention partenariat stratégique.',
                'start'      => '+5 days 11:30',
                'duration_h' => 1,
                'lieu'       => 'Salle VIP',
                'type_code'  => 'rdv',
                'visio'      => false,
            ],
            [
                'titre'      => 'Point mensuel — Suivi budgétaire',
                'extrait'    => 'Consommation par ligne budgétaire au 26/08.',
                'start'      => '+6 days 09:30',
                'duration_h' => 1,
                'lieu'       => null,
                'type_code'  => 'reunion',
                'visio'      => true,
            ],
            [
                'titre'      => 'Journée portes ouvertes — Session Q3',
                'extrait'    => 'Accueil public, présentations, stands partenaires.',
                'start'      => '+7 days 08:00',
                'duration_h' => 8,
                'lieu'       => 'Siège ANPI-Gabon',
                'type_code'  => 'conf',
                'visio'      => false,
            ],
        ];

        foreach ($events as $e) {
            $start = new \DateTimeImmutable($e['start']);
            $end = $start->modify('+'.$e['duration_h'].' hours');
            Evenement::firstOrCreate(
                ['titre' => $e['titre']],
                [
                    'extrait'            => $e['extrait'],
                    'description'        => '<p>'.$e['extrait'].'</p>',
                    'date_debut'         => $start,
                    'date_fin'           => $end,
                    'journee_entiere'    => false,
                    'lieu'               => $e['lieu'],
                    'est_visio'          => $e['visio'],
                    'lien_visio'         => $e['visio'] ? 'https://meet.example.com/anpi-'.uniqid() : null,
                    'statut'             => 'planifie',
                    'is_public'          => true,
                    'type_evenement_id'  => $types[$e['type_code']] ?? null,
                    'created_by'         => $auteur,
                    'inscription_requise'=> false,
                ],
            );
        }
    }
}
