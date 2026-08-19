<?php

namespace App\Http\Controllers\Admin;

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
use Illuminate\Support\Str;

/**
 * Contrôleur générique pour les 3 référentiels RH :
 * type_contrats, postes, departements.
 *
 * Une seule classe gère les 3 entités via le segment de route "type".
 */
class ReferentielRhController extends Controller
{
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
        'regroupements-village'=> ['model' => LocaliteAdmin::class, 'libelle' => 'Regroupements de villages','singulier' => 'Regroupement de village', 'icon' => 'fa-people-roof', 'type_filter' => 'regroupement-village', 'group' => 'Localisation administrative'],
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

    public function index(Request $request, string $type)
    {
        $meta = $this->meta($type);
        $model = $meta['model'];
        $query = $this->applyTypeFilter($model::query(), $meta);
        $items = $query
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('code', 'ilike', "%$s%")))
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->paginate(30)
            ->withQueryString();

        return view('admin.referentiel-rh.index', compact('items', 'meta'));
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
