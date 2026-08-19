<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vitrine\Atout;
use App\Models\Vitrine\Capture;
use App\Models\Vitrine\Module;
use App\Models\Vitrine\Setting;
use App\Models\Vitrine\SlideHero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VitrineController extends Controller
{
    public function index()
    {
        return view('admin.vitrine.index', [
            'slides'   => SlideHero::orderBy('ordre')->get(),
            'modules'  => Module::orderBy('ordre')->get(),
            'captures' => Capture::orderBy('ordre')->get(),
            'atouts'   => Atout::orderBy('ordre')->get(),
            'settings' => Setting::orderBy('groupe')->orderBy('ordre')->get()->groupBy('groupe'),
        ]);
    }

    /* ─── SLIDES HERO ──────────────────────────────────── */

    public function storeSlide(Request $request)
    {
        $data = $this->validateSlide($request);
        $data['stats'] = $this->parseStats($request->input('stats_raw'));
        $data['image_path'] = $this->storeImage($request, 'image', 'vitrine/hero');
        SlideHero::create($data + ['ordre' => SlideHero::max('ordre') + 1]);
        return back()->with('success', 'Slide créé.');
    }

    public function updateSlide(Request $request, SlideHero $slide)
    {
        $data = $this->validateSlide($request);
        $data['stats'] = $this->parseStats($request->input('stats_raw'));
        if ($img = $this->storeImage($request, 'image', 'vitrine/hero')) {
            if ($slide->image_path) Storage::disk('public')->delete($slide->image_path);
            $data['image_path'] = $img;
        }
        $slide->update($data);
        return back()->with('success', 'Slide mis à jour.');
    }

    public function destroySlide(SlideHero $slide)
    {
        if ($slide->image_path) Storage::disk('public')->delete($slide->image_path);
        $slide->delete();
        return back()->with('success', 'Slide supprimé.');
    }

    private function validateSlide(Request $r): array
    {
        return $r->validate([
            'eyebrow'         => 'nullable|string|max:100',
            'eyebrow_icone'   => 'nullable|string|max:50',
            'eyebrow_couleur' => 'nullable|string|max:20',
            'titre'           => 'required|string|max:255',
            'sous_titre'      => 'nullable|string',
            'cta_texte'       => 'nullable|string|max:100',
            'cta_url'         => 'nullable|string|max:255',
            'mockup_type'     => 'nullable|in:dashboard,wbs,okr,validation',
            'image'           => 'nullable|image|max:5120',
            'est_actif'       => 'nullable|boolean',
        ]) + ['est_actif' => $r->boolean('est_actif', true)];
    }

    /* ─── MODULES ──────────────────────────────────────── */

    public function storeModule(Request $request)
    {
        $data = $this->validateModule($request);
        $data['features'] = $this->parseFeatures($request->input('features_raw'));
        Module::create($data + ['ordre' => Module::max('ordre') + 1]);
        return back()->with('success', 'Module créé.');
    }

    public function updateModule(Request $request, Module $module)
    {
        $data = $this->validateModule($request);
        $data['features'] = $this->parseFeatures($request->input('features_raw'));
        $module->update($data);
        return back()->with('success', 'Module mis à jour.');
    }

    public function destroyModule(Module $module)
    {
        $module->delete();
        return back()->with('success', 'Module supprimé.');
    }

    private function validateModule(Request $r): array
    {
        return $r->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'icone'       => 'nullable|string|max:50',
            'couleur'     => 'nullable|string|max:20',
            'est_actif'   => 'nullable|boolean',
        ]) + ['est_actif' => $r->boolean('est_actif', true)];
    }

    /* ─── CAPTURES ─────────────────────────────────────── */

    public function storeCapture(Request $request)
    {
        $data = $this->validateCapture($request);
        $data['image_path'] = $this->storeImage($request, 'image', 'vitrine/captures');
        Capture::create($data + ['ordre' => Capture::max('ordre') + 1]);
        return back()->with('success', 'Capture créée.');
    }

    public function updateCapture(Request $request, Capture $capture)
    {
        $data = $this->validateCapture($request);
        if ($img = $this->storeImage($request, 'image', 'vitrine/captures')) {
            if ($capture->image_path) Storage::disk('public')->delete($capture->image_path);
            $data['image_path'] = $img;
        }
        $capture->update($data);
        return back()->with('success', 'Capture mise à jour.');
    }

    public function destroyCapture(Capture $capture)
    {
        if ($capture->image_path) Storage::disk('public')->delete($capture->image_path);
        $capture->delete();
        return back()->with('success', 'Capture supprimée.');
    }

    private function validateCapture(Request $r): array
    {
        return $r->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'tag'          => 'nullable|string|max:50',
            'tag_couleur'  => 'nullable|string|max:20',
            'url_affichee' => 'nullable|string|max:255',
            'mockup_type'  => 'nullable|in:dashboard,wbs,kanban,annuaire,okr,validation',
            'image'        => 'nullable|image|max:5120',
            'est_actif'    => 'nullable|boolean',
        ]) + ['est_actif' => $r->boolean('est_actif', true)];
    }

    /* ─── ATOUTS ───────────────────────────────────────── */

    public function storeAtout(Request $request)
    {
        $data = $this->validateAtout($request);
        Atout::create($data + ['ordre' => Atout::max('ordre') + 1]);
        return back()->with('success', 'Atout créé.');
    }

    public function updateAtout(Request $request, Atout $atout)
    {
        $atout->update($this->validateAtout($request));
        return back()->with('success', 'Atout mis à jour.');
    }

    public function destroyAtout(Atout $atout)
    {
        $atout->delete();
        return back()->with('success', 'Atout supprimé.');
    }

    private function validateAtout(Request $r): array
    {
        return $r->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'icone'         => 'nullable|string|max:50',
            'gradient_from' => 'nullable|string|max:20',
            'gradient_to'   => 'nullable|string|max:20',
            'est_actif'     => 'nullable|boolean',
        ]) + ['est_actif' => $r->boolean('est_actif', true)];
    }

    /* ─── SETTINGS (key/value) ────────────────────────── */

    public function updateSettings(Request $request)
    {
        foreach ($request->input('settings', []) as $cle => $valeur) {
            Setting::where('cle', $cle)->update(['valeur' => $valeur]);
        }
        return back()->with('success', 'Paramètres enregistrés.');
    }

    /* ─── REORDER (générique) ─────────────────────────── */

    public function reorder(Request $request, string $type)
    {
        $request->validate(['order' => 'required|array']);
        $model = match ($type) {
            'slides'   => SlideHero::class,
            'modules'  => Module::class,
            'captures' => Capture::class,
            'atouts'   => Atout::class,
            default    => abort(404),
        };
        foreach ($request->input('order') as $index => $id) {
            $model::where('id', $id)->update(['ordre' => $index]);
        }
        return response()->json(['ok' => true]);
    }

    /* ─── Helpers privés ──────────────────────────────── */

    private function storeImage(Request $r, string $field, string $folder): ?string
    {
        if ($r->hasFile($field)) {
            return $r->file($field)->store($folder, 'public');
        }
        return null;
    }

    private function parseStats(?string $raw): array
    {
        if (! $raw) return [];
        $stats = [];
        foreach (preg_split('/[\r\n]+/', trim($raw)) as $line) {
            $parts = explode('|', $line, 2);
            if (count($parts) === 2) {
                $stats[] = ['num' => trim($parts[0]), 'label' => trim($parts[1])];
            }
        }
        return $stats;
    }

    private function parseFeatures(?string $raw): array
    {
        if (! $raw) return [];
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', trim($raw)))));
    }
}
