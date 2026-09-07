<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemeConfig;
use Illuminate\Http\Request;

/**
 * Paramétrage global du système — key/value en 5 catégories.
 * Marque, Localisation, Sécurité, Notifications, Général.
 */
class ConfigController extends Controller
{
    public function index()
    {
        $configs = SystemeConfig::orderBy('categorie')->orderBy('ordre')->orderBy('libelle')->get()->groupBy('categorie');

        $categoriesLabels = [
            'marque'        => ['label' => 'Marque & identité', 'icon' => 'fa-palette',  'color' => '#0A66C2'],
            'localisation'  => ['label' => 'Localisation',      'icon' => 'fa-globe',    'color' => '#059669'],
            'securite'      => ['label' => 'Sécurité',          'icon' => 'fa-shield-halved', 'color' => '#DC2626'],
            'notifications' => ['label' => 'Notifications',     'icon' => 'fa-bell',     'color' => '#D97706'],
            'general'       => ['label' => 'Général',           'icon' => 'fa-sliders',  'color' => '#64748B'],
        ];

        return view('admin.config.index', compact('configs', 'categoriesLabels'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'valeurs' => 'required|array',
        ]);

        $count = 0;
        foreach ($data['valeurs'] as $cle => $valeur) {
            $config = SystemeConfig::where('cle', $cle)->where('editable', true)->first();
            if (!$config) continue;

            // Normalisation par type
            $v = match ($config->type) {
                'bool' => in_array($valeur, ['1', 'on', true, 'true'], true) ? '1' : '0',
                'int'  => is_numeric($valeur) ? (string) (int) $valeur : null,
                'json' => is_array($valeur) ? json_encode($valeur) : (is_string($valeur) ? $valeur : null),
                default => is_scalar($valeur) ? (string) $valeur : null,
            };

            if ($config->valeur !== $v) {
                $config->update(['valeur' => $v]);
                $count++;
            }
        }

        // Bool désactivés : les checkboxes non-cochées ne sont pas envoyées
        SystemeConfig::where('type', 'bool')
            ->where('editable', true)
            ->whereNotIn('cle', array_keys($data['valeurs']))
            ->get()
            ->each(function ($c) use (&$count) {
                if ($c->valeur !== '0') { $c->update(['valeur' => '0']); $count++; }
            });

        return back()->with('success', "$count paramètre(s) mis à jour.");
    }
}
