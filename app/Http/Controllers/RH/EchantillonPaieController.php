<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\EchantillonPaie;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EchantillonPaieController extends Controller
{
    public function index(Request $request)
    {
        $echantillons = EchantillonPaie::query()
            ->with('createur')
            ->withCount('employes')
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('code', 'ilike', "%$s%")))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (bool) $request->statut))
            ->orderBy('libelle')
            ->paginate(20)
            ->withQueryString();

        return view('rh.echantillons-paie.index', compact('echantillons'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.echantillons-paie.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle'        => 'required|string|max:255',
            'code'           => 'nullable|string|max:60|unique:echantillons_paie,code',
            'description'    => 'nullable|string',
            'couleur'        => 'nullable|string|max:9',
            'icone'          => 'nullable|string|max:40',
            'statut'         => 'nullable|boolean',
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
        ], [
            'employee_ids.required' => 'Sélectionnez au moins un employé.',
            'employee_ids.min'      => 'Un échantillon doit contenir au moins un employé.',
        ]);

        $code = $data['code'] ?? $this->genererCode($data['libelle']);

        DB::transaction(function () use ($data, $code) {
            $ech = EchantillonPaie::create([
                'code'        => $code,
                'libelle'     => $data['libelle'],
                'description' => $data['description'] ?? null,
                'couleur'     => $data['couleur'] ?? null,
                'icone'       => $data['icone'] ?? null,
                'statut'      => $data['statut'] ?? true,
                'created_by'  => auth()->id(),
            ]);
            $ech->employes()->attach($data['employee_ids']);
        });

        return redirect()->route('rh.echantillons-paie.index')
            ->with('success', "Échantillon « {$data['libelle'] } » créé avec " . count($data['employee_ids']) . ' employé(s).');
    }

    public function show(EchantillonPaie $echantillons_paie)
    {
        $echantillons_paie->load(['employes' => fn($q) => $q->orderBy('noms'), 'createur']);
        return view('rh.echantillons-paie.show', ['echantillon' => $echantillons_paie]);
    }

    public function edit(EchantillonPaie $echantillons_paie)
    {
        $echantillons_paie->load('employes');
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $selectedIds = $echantillons_paie->employes->pluck('id')->all();
        return view('rh.echantillons-paie.edit', [
            'echantillon' => $echantillons_paie,
            'employees'   => $employees,
            'selectedIds' => $selectedIds,
        ]);
    }

    public function update(Request $request, EchantillonPaie $echantillons_paie)
    {
        $data = $request->validate([
            'libelle'        => 'required|string|max:255',
            'code'           => 'nullable|string|max:60|unique:echantillons_paie,code,' . $echantillons_paie->id,
            'description'    => 'nullable|string',
            'couleur'        => 'nullable|string|max:9',
            'icone'          => 'nullable|string|max:40',
            'statut'         => 'nullable|boolean',
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
        ]);

        DB::transaction(function () use ($data, $echantillons_paie) {
            $echantillons_paie->update([
                'libelle'     => $data['libelle'],
                'code'        => $data['code'] ?? $echantillons_paie->code,
                'description' => $data['description'] ?? null,
                'couleur'     => $data['couleur'] ?? null,
                'icone'       => $data['icone'] ?? null,
                'statut'      => $data['statut'] ?? $echantillons_paie->statut,
            ]);
            $echantillons_paie->employes()->sync($data['employee_ids']);
        });

        return redirect()->route('rh.echantillons-paie.show', $echantillons_paie)
            ->with('success', 'Échantillon mis à jour.');
    }

    public function destroy(EchantillonPaie $echantillons_paie)
    {
        $echantillons_paie->delete();
        return redirect()->route('rh.echantillons-paie.index')->with('success', 'Échantillon supprimé.');
    }

    private function genererCode(string $libelle): string
    {
        $base = 'ECH-' . strtoupper(Str::slug(Str::limit($libelle, 30, ''), '-'));
        $code = $base;
        $i = 2;
        while (EchantillonPaie::where('code', $code)->exists()) {
            $code = $base . '-' . $i++;
            if ($i > 999) break;
        }
        return $code;
    }
}
