<?php

namespace Database\Seeders;

use App\Models\Referentiel\LocaliteAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Localités administratives par défaut (Gabon principalement).
 * L'admin peut compléter via /admin/referentiel-rh/{type}.
 */
class LocalisationsAdminSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Pays (focus Afrique centrale)
            'pays' => [
                'Gabon', 'Cameroun', 'Congo (Brazzaville)', 'République Démocratique du Congo',
                'Guinée équatoriale', 'Sao Tomé-et-Principe', 'République centrafricaine',
                'Tchad', 'France', 'Autre',
            ],
            // Provinces du Gabon (9)
            'province' => [
                'Estuaire', 'Haut-Ogooué', 'Moyen-Ogooué', 'Ngounié', 'Nyanga',
                'Ogooué-Ivindo', 'Ogooué-Lolo', 'Ogooué-Maritime', 'Woleu-Ntem',
            ],
            // Départements administratifs (extrait — 8 sur 50)
            'departement-admin' => [
                'Komo-Mondah', 'Komo', 'Noya', 'Mpassa', 'Lemboumbi-Leyou',
                'Lolo-Bouenguidi', 'Tsamba-Magotsi', 'Bendje',
            ],
            // Préfectures
            'prefecture' => [
                'Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Lambaréné',
                'Mouila', 'Tchibanga', 'Makokou', 'Koulamoutou', 'Bitam',
            ],
            // Sous-préfectures
            'sous-prefecture' => [
                'Akanda', 'Owendo', 'Ntoum', 'Cocobeach', 'Kango',
                'Mitzic', 'Booué', 'Lastoursville', 'Mounana',
            ],
            // Communes (centres urbains du Gabon)
            'commune' => [
                'Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Lambaréné',
                'Mouila', 'Tchibanga', 'Makokou', 'Koulamoutou', 'Bitam',
                'Akanda', 'Owendo', 'Ntoum', 'Moanda',
            ],
            // Arrondissements (Libreville en compte 6, autres entre 2 et 4)
            'arrondissement' => [
                '1er arrondissement', '2e arrondissement', '3e arrondissement',
                '4e arrondissement', '5e arrondissement', '6e arrondissement',
            ],
            // Quartiers (Libreville — extrait)
            'quartier' => [
                'Glass', 'Akébé', 'Nzeng-Ayong', 'Lalala', 'Awendjé',
                'Nombakélé', 'Mont-Bouët', 'PK5', 'PK8', 'PK12',
                'Cocotiers', 'Batterie IV', 'Quaben', 'Sotega', 'Charbonnages',
                'Plein-Ciel', 'Belle-Vue', 'Atong-Abé', 'Akournam', 'Sibang',
                'Okala', 'Bambouchine', 'Toulon', 'Cap Estérias',
            ],
            // Cantons (extraits — typiquement zones rurales)
            'canton' => [
                'Canton Ekobah', 'Canton Komo-Océan', 'Canton Lébamba',
                'Canton Bifoun', 'Canton Mébomba',
            ],
            // Regroupements de villages (exemples)
            'regroupement-village' => [
                'Regroupement Mvomékak', 'Regroupement Ndjolé', 'Regroupement Mékambo',
                'Regroupement Boumango',
            ],
            // Villages (échantillon)
            'village' => [
                'Akok', 'Andem', 'Mékak', 'Nzomoé', 'Ovan',
                'Ekanga', 'Mékouma', 'Bissok', 'Allarmintang', 'Mbé',
                'Tsamba', 'Mékambo', 'Bifoun', 'Lopé',
            ],
        ];

        $count = 0;
        foreach ($data as $type => $libelles) {
            foreach ($libelles as $i => $libelle) {
                $code = strtoupper(Str::slug($type . '-' . $libelle, '_'));
                LocaliteAdmin::updateOrCreate(
                    ['type' => $type, 'code' => $code],
                    ['libelle' => $libelle, 'ordre' => $i + 1, 'statut' => 1]
                );
                $count++;
            }
        }

        $this->command?->info("Localisations administratives seedées : $count entrées sur 11 niveaux");
    }
}
