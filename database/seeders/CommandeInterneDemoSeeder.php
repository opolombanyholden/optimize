<?php

namespace Database\Seeders;

use App\Models\CommandeInterne;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommandeInterneDemoSeeder extends Seeder
{
    /**
     * Seed de démo pour tester la validation N+1 sur /appro/commandes-internes.
     * Crée des commandes internes en statut EN_ATTENTE_N1 avec l'utilisateur
     * "Systeme Administrateur" (admin@optimize.local) comme N+1.
     */
    public function run(): void
    {
        $n1 = User::where('email', 'admin@optimize.local')->first();
        if (!$n1) {
            $this->command?->warn('Utilisateur admin@optimize.local introuvable — seed CI ignoré.');
            return;
        }

        // Choisit des demandeurs (autres que le N+1)
        $demandeurs = User::where('id', '!=', $n1->id)->limit(4)->get();
        if ($demandeurs->isEmpty()) {
            $this->command?->warn('Aucun autre utilisateur trouvé pour être demandeur — seed CI ignoré.');
            return;
        }

        // Produits disponibles (au moins 3)
        $produits = Produit::where('est_stockable', true)->limit(6)->get();
        if ($produits->count() < 2) {
            $this->command?->warn('Pas assez de produits en catalogue — seed CI ignoré.');
            return;
        }

        DB::transaction(function () use ($n1, $demandeurs, $produits) {
            $scenarios = [
                [
                    'objet'         => 'Fournitures bureau — trimestre en cours',
                    'justification' => 'Réapprovisionnement mensuel du service comptabilité.',
                    'date_besoin'   => now()->addWeek()->toDateString(),
                    'nb_lignes'     => 3,
                ],
                [
                    'objet'         => 'Achat matériel informatique — remplacement urgent',
                    'justification' => 'Panne définitive du poste utilisateur — remplacement demandé sous 48h.',
                    'date_besoin'   => now()->addDays(3)->toDateString(),
                    'nb_lignes'     => 2,
                ],
                [
                    'objet'         => 'Consommables imprimante',
                    'justification' => 'Stock épuisé — tirage rapports fin de mois compromis.',
                    'date_besoin'   => now()->addDays(5)->toDateString(),
                    'nb_lignes'     => 2,
                ],
                [
                    'objet'         => 'Petit outillage — atelier maintenance',
                    'justification' => 'Renouvellement outils cassés / manquants signalés par les techniciens.',
                    'date_besoin'   => now()->addWeeks(2)->toDateString(),
                    'nb_lignes'     => 4,
                ],
            ];

            foreach ($scenarios as $i => $sc) {
                $demandeur = $demandeurs[$i % $demandeurs->count()];

                // Évite les doublons : ne recrée pas si un objet identique existe déjà en attente N+1 pour ce demandeur
                $exists = CommandeInterne::where('demandeur_id', $demandeur->id)
                    ->where('objet', $sc['objet'])
                    ->where('statut', CommandeInterne::STATUT_EN_ATTENTE_N1)
                    ->exists();
                if ($exists) continue;

                $cmd = CommandeInterne::create([
                    'numero'         => CommandeInterne::genererNumero(),
                    'objet'          => $sc['objet'],
                    'justification'  => $sc['justification'],
                    'demandeur_id'   => $demandeur->id,
                    'superieur_id'   => $n1->id,
                    'date_demande'   => now()->subDays(rand(1, 3))->toDateString(),
                    'date_besoin'    => $sc['date_besoin'],
                    'statut'         => CommandeInterne::STATUT_EN_ATTENTE_N1,
                    'soumise_n1_at'  => now()->subDays(rand(1, 2)),
                ]);

                // Lignes : sélection aléatoire de produits catalogue
                $selection = $produits->random(min($sc['nb_lignes'], $produits->count()));
                foreach ($selection as $p) {
                    $cmd->lignes()->create([
                        'produit_id'          => $p->id,
                        'designation'         => $p->designation,
                        'quantite_demandee'   => (float) rand(1, 10),
                        'unite'               => $p->unite_mesure,
                        'prix_unitaire_estime' => (float) $p->prix_unitaire,
                    ]);
                }
                $cmd->recalculerMontant();
            }
        });

        $this->command?->info('Seed CommandeInterneDemoSeeder : commandes en attente N+1 créées.');
    }
}
