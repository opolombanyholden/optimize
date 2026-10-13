<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManipuleTableurs;
use App\Http\Controllers\Controller;
use App\Models\Referentiel\Departement;
use App\Models\Referentiel\GroupeRubrique;
use App\Models\Referentiel\LocaliteAdmin;
use App\Models\Referentiel\Nationalite;
use App\Models\Referentiel\NiveauQualification;
use App\Models\Referentiel\Poste;
use App\Models\Referentiel\TypeContrat;
use App\Models\TypeEvenementCarriere;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur générique des référentiels RH (types de contrat, postes, départements…)
 * et de la localisation administrative — CRUD + import / export (CSV `;` ou XLSX).
 *
 * Une seule classe gère toutes les entités via le segment de route "type".
 */
class ReferentielRhController extends Controller
{
    use ManipuleTableurs;

    /** Colonnes d'export = colonnes d'import (aller-retour possible). */
    private const COLONNES = ['code', 'libelle', 'description', 'ordre', 'statut'];

    private const MAP = [
        'types-contrat' => [
            'model'    => TypeContrat::class,
            'libelle'  => 'Types de contrat',
            'singulier'=> 'Type de contrat',
            'icon'     => 'fa-file-contract',
        ],
        'postes' => [
            'model'    => Poste::class,
            'libelle'  => 'Postes',
            'singulier'=> 'Poste',
            'icon'     => 'fa-briefcase',
        ],
        'departements' => [
            'model'    => Departement::class,
            'libelle'  => 'Départements',
            'singulier'=> 'Département',
            'icon'     => 'fa-sitemap',
        ],
        'types-evenement' => [
            'model'    => TypeEvenementCarriere::class,
            'libelle'  => 'Types d\'évènement de carrière',
            'singulier'=> 'Type d\'évènement',
            'icon'     => 'fa-arrow-trend-up',
        ],
        'niveaux-qualification' => [
            'model'    => NiveauQualification::class,
            'libelle'  => 'Niveaux de qualification',
            'singulier'=> 'Niveau de qualification',
            'icon'     => 'fa-graduation-cap',
        ],
        'nationalites' => [
            'model'    => Nationalite::class,
            'libelle'  => 'Nationalités',
            'singulier'=> 'Nationalité',
            'icon'     => 'fa-flag',
        ],
        'groupes-rubriques' => [
            'model'    => GroupeRubrique::class,
            'libelle'  => 'Groupes de rubriques (Paie)',
            'singulier'=> 'Groupe de rubriques',
            'icon'     => 'fa-layer-group',
        ],
        // ─── Localisation administrative (1 table polymorphe, filtre par type) ───
        'pays-loc'             => ['model' => LocaliteAdmin::class, 'libelle' => 'Pays',                   'singulier' => 'Pays',                 'icon' => 'fa-earth-africa',  'type_filter' => 'pays',                 'group' => 'Localisation administrative'],
        'provinces'            => ['model' => LocaliteAdmin::class, 'libelle' => 'Provinces',              'singulier' => 'Province',             'icon' => 'fa-map',           'type_filter' => 'province',             'group' => 'Localisation administrative'],
        'departements-admin'   => ['model' => LocaliteAdmin::class, 'libelle' => 'Départements (admin)',   'singulier' => 'Département',          'icon' => 'fa-map-pin',       'type_filter' => 'departement-admin',    'group' => 'Localisation administrative'],
        'prefectures'          => ['model' => LocaliteAdmin::class, 'libelle' => 'Préfectures',            'singulier' => 'Préfecture',           'icon' => 'fa-landmark',      'type_filter' => 'prefecture',           'group' => 'Localisation administrative'],
        'sous-prefectures'     => ['model' => LocaliteAdmin::class, 'libelle' => 'Sous-préfectures',       'singulier' => 'Sous-préfecture',      'icon' => 'fa-landmark-flag', 'type_filter' => 'sous-prefecture',      'group' => 'Localisation administrative'],
        'communes'             => ['model' => LocaliteAdmin::class, 'libelle' => 'Communes',               'singulier' => 'Commune',              'icon' => 'fa-city',          'type_filter' => 'commune',              'group' => 'Localisation administrative'],
        'arrondissements'      => ['model' => LocaliteAdmin::class, 'libelle' => 'Arrondissements',        'singulier' => 'Arrondissement',       'icon' => 'fa-square',        'type_filter' => 'arrondissement',       'group' => 'Localisation administrative'],
        'quartiers'            => ['model' => LocaliteAdmin::class, 'libelle' => 'Quartiers',              'singulier' => 'Quartier',             'icon' => 'fa-house-chimney', 'type_filter' => 'quartier',             'group' => 'Localisation administrative'],
        'cantons'              => ['model' => LocaliteAdmin::class, 'libelle' => 'Cantons',                'singulier' => 'Canton',               'icon' => 'fa-tree',          'type_filter' => 'canton',               'group' => 'Localisation administrative'],
        'regroupements-village'=> ['model' => LocaliteAdmin::class, 'libelle' => 'Regroupements de villages', 'singulier' => 'Regroupement de village', 'icon' => 'fa-people-roof', 'type_filter' => 'regroupement-village', 'group' => 'Localisation administrative'],
        'villages'             => ['model' => LocaliteAdmin::class, 'libelle' => 'Villages',               'singulier' => 'Village',              'icon' => 'fa-house',         'type_filter' => 'village',              'group' => 'Localisation administrative'],
    ];

    private function meta(string $type): array
    {
        abort_unless(isset(self::MAP[$type]), 404);
        return self::MAP[$type] + ['type' => $type];
    }

    /**
     * Applique le filtre type sur le query si la MAP le spécifie (table polymorphe).
     */
    private function applyTypeFilter($query, array $meta)
    {
        if (!empty($meta['type_filter'])) {
            $query->where('type', $meta['type_filter']);
        }
        return $query;
    }

    /** Pour les tables polymorphes, l'unicité du code est scopée par type. */
    private function uniqueCodeRule(array $meta, ?int $excludeId = null): string
    {
        $table = (new $meta['model'])->getTable();
        $rule = "unique:{$table},code";
        if ($excludeId) $rule .= ",{$excludeId}";
        if (!empty($meta['type_filter'])) {
            // ignore id col par défaut, puis where type=...
            $rule .= ($excludeId ? '' : ',NULL') . ',id,type,' . $meta['type_filter'];
        }
        return $rule;
    }

    /** Requête filtrée commune à la liste et à l'export (q / statut). */
    private function buildQuery(Request $request, array $meta)
    {
        return $this->applyTypeFilter($meta['model']::query(), $meta)
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('code', 'ilike', "%$s%")))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderBy('ordre')
            ->orderBy('libelle');
    }

    public function index(Request $request, string $type)
    {
        $meta  = $this->meta($type);
        $items = $this->buildQuery($request, $meta)
            ->paginate(30)
            ->withQueryString();

        $parStatut = $this->applyTypeFilter($meta['model']::query(), $meta)
            ->selectRaw('statut, count(*) as n')->groupBy('statut')->pluck('n', 'statut');
        $stats = [
            'total'    => (int) $parStatut->sum(),
            'actifs'   => (int) ($parStatut[1] ?? 0),
            'inactifs' => (int) $parStatut->except(1)->sum(),
        ];

        return view('admin.referentiel-rh.index', [
            'items'   => $items,
            'meta'    => $meta,
            'stats'   => $stats,
        ]);
    }

    public function store(Request $request, string $type)
    {
        $meta = $this->meta($type);

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50', $this->uniqueCodeRule($meta)],
            'libelle'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre'       => ['nullable', 'integer', 'min:0'],
            'statut'      => ['nullable', 'integer', 'in:0,1'],
        ]);
        $data['code'] = strtoupper(Str::slug($data['code'], '_'));
        $data['statut'] = $data['statut'] ?? 1;
        $data['ordre'] = $data['ordre'] ?? 0;
        if (!empty($meta['type_filter'])) {
            $data['type'] = $meta['type_filter'];
        }

        $meta['model']::create($data);

        return redirect()->route('admin.referentiel-rh.index', $type)->with('success', $meta['singulier'] . ' ajouté(e).');
    }

    public function update(Request $request, string $type, int $id)
    {
        $meta = $this->meta($type);
        /** @var Model $item */
        $item = $meta['model']::findOrFail($id);

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50', $this->uniqueCodeRule($meta, $id)],
            'libelle'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre'       => ['nullable', 'integer', 'min:0'],
            'statut'      => ['nullable', 'integer', 'in:0,1'],
        ]);
        $data['code'] = strtoupper(Str::slug($data['code'], '_'));

        $item->update($data);

        return redirect()->route('admin.referentiel-rh.index', $type)->with('success', $meta['singulier'] . ' mis(e) à jour.');
    }

    public function destroy(string $type, int $id)
    {
        $meta = $this->meta($type);
        $item = $meta['model']::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.referentiel-rh.index', $type)->with('success', $meta['singulier'] . ' supprimé(e).');
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT / IMPORT
    // ═══════════════════════════════════════════════════════════════
    public function export(Request $request, string $type): StreamedResponse
    {
        $meta = $this->meta($type);
        $q = $this->buildQuery($request, $meta);

        return $this->telechargerTableur(
            strtolower((string) $request->query('format', 'csv')),
            Str::slug($meta['libelle']), $meta['libelle'], self::COLONNES,
            fn(\Closure $ecrire) => $q->chunk(500, fn($items) => $items->each(fn($it) => $ecrire([
                $it->code,
                $it->libelle,
                $it->description,
                $it->ordre,
                $it->statut == 1 ? 'actif' : 'inactif',
            ]))),
        );
    }

    public function importTemplate(string $type): StreamedResponse
    {
        $meta = $this->meta($type);

        return response()->streamDownload(function () use ($meta) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLONNES, ';');
            fputcsv($out, self::safeCsvRow(['EXEMPLE_1', $meta['singulier'].' exemple 1', 'Description facultative', '1', 'actif']), ';');
            fputcsv($out, self::safeCsvRow(['EXEMPLE_2', $meta['singulier'].' exemple 2', '', '2', 'inactif']), ';');
            fclose($out);
        }, Str::slug($meta['libelle']).'-modele.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request, string $type)
    {
        $meta = $this->meta($type);
        $request->validate([
            'fichier'        => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], // 5 Mo max
            'mettre_a_jour'  => ['nullable', 'boolean'],
            'ignore_erreurs' => ['nullable', 'boolean'],
        ]);

        $rows = $this->lireTableur($request->file('fichier'));
        if ($rows === null) {
            return back()->with('error', 'Impossible de lire le fichier ou fichier vide.');
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($rows));
        $missing = array_diff(['code', 'libelle'], $header);
        if (!empty($missing)) {
            return back()->with('error', 'Colonnes obligatoires manquantes : '.implode(', ', $missing));
        }

        $majExistants = (bool) $request->input('mettre_a_jour');
        $ignore       = (bool) $request->input('ignore_erreurs');

        // Existants par code, y compris supprimés (l'unicité DB les inclut)
        $existants = $this->applyTypeFilter($meta['model']::withTrashed(), $meta)->get()->keyBy('code');

        $operations = [];
        $erreurs    = [];
        $vus        = [];
        $lineNum    = 1; // en-tête = ligne 1

        foreach ($rows as $row) {
            $lineNum++;
            if (($data = self::normaliserLigne($header, $row)) === null) continue;
            $val = fn(string $k) => ($data[$k] ?? '') !== '' ? $data[$k] : null;

            $errs = [];
            $code = strtoupper(Str::slug((string) $val('code'), '_'));
            $existant = $existants[$code] ?? null;

            if ($code === '') $errs[] = 'code vide';
            elseif (mb_strlen($code) > 50) $errs[] = 'code trop long (max 50)';
            elseif (isset($vus[$code])) $errs[] = "code {$code} en double dans le fichier (ligne {$vus[$code]})";
            elseif ($existant && !$majExistants) $errs[] = "code {$code} déjà existant (cochez « Mettre à jour les existants »)";
            if ($code !== '') $vus[$code] = $lineNum;

            if (!$val('libelle')) $errs[] = 'libellé vide';
            elseif (mb_strlen($val('libelle')) > 255) $errs[] = 'libellé trop long (max 255)';

            $ordre = $val('ordre');
            if ($ordre !== null && !ctype_digit($ordre)) $errs[] = 'ordre doit être un entier positif';

            $statut = null;
            if ($val('statut') !== null && ($statut = self::parseStatut($val('statut'))) === null) {
                $errs[] = 'statut invalide (actif / inactif)';
            }

            if (!empty($errs)) {
                $erreurs[] = ['ligne' => $lineNum, 'motifs' => $errs];
                continue;
            }

            $operations[] = [
                'item'  => $existant,
                'attrs' => array_filter([
                    'code'        => $code,
                    'libelle'     => $val('libelle'),
                    'description' => $val('description'),
                    'ordre'       => $ordre !== null ? (int) $ordre : null,
                    'statut'      => $statut,
                ], fn($v) => $v !== null),
            ];
        }

        if (!empty($erreurs) && !$ignore) {
            return back()
                ->with('error', count($erreurs).' ligne(s) en erreur — corrigez le fichier ou cochez « Ignorer les lignes en erreur ».')
                ->with('import_erreurs', array_slice($erreurs, 0, 30));
        }

        if (empty($operations)) {
            return back()->with('error', 'Aucune ligne valide à importer.')->with('import_erreurs', array_slice($erreurs, 0, 30));
        }

        $compteurs = ['crees' => 0, 'majs' => 0, 'restaures' => 0];
        DB::transaction(function () use ($operations, $meta, &$compteurs) {
            foreach ($operations as $op) {
                if ($item = $op['item']) {
                    if ($item->trashed()) {
                        $item->restore();
                        $compteurs['restaures']++;
                    }
                    $item->update($op['attrs']);
                    $compteurs['majs']++;
                } else {
                    $meta['model']::create($op['attrs'] + array_filter([
                        'ordre'  => 0,
                        'statut' => 1,
                        'type'   => $meta['type_filter'] ?? null,
                    ], fn($v) => $v !== null));
                    $compteurs['crees']++;
                }
            }
        });

        $msg = "Import terminé : {$compteurs['crees']} créé(s), {$compteurs['majs']} mis à jour";
        if ($compteurs['restaures']) $msg .= " (dont {$compteurs['restaures']} restauré(s) depuis la corbeille)";
        $msg .= '.';
        if ($erreurs) $msg .= ' '.count($erreurs).' ligne(s) ignorée(s).';

        return redirect()->route('admin.referentiel-rh.index', $type)
            ->with('success', $msg)
            ->with('import_erreurs', array_slice($erreurs, 0, 30));
    }

    /**
     * Endpoint JSON pour autocomplete Tom Select (RH employee form).
     * URL : /api/referentiel/{type}/options?q=...
     */
    public function options(Request $request, string $type)
    {
        $meta = $this->meta($type);
        $model = $meta['model'];
        $items = $this->applyTypeFilter($model::actif(), $meta)
            ->when($request->q, fn($q, $s) => $q->where('libelle', 'ilike', "%$s%"))
            ->orderBy('libelle')
            ->limit(50)
            ->get(['id', 'code', 'libelle']);

        return response()->json($items);
    }
}
