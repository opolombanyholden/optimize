<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Source;
use Illuminate\Http\Request;

/**
 * Sources de financement — référentiel Finance.
 * Exemples : FP (Fonds Propres), RB (Reports Budgétaires), ETAT (Dotation de l'État).
 */
class SourceController extends Controller
{
    public function index(Request $request)
    {
        $sources = Source::withCount('budgetSources')
            ->when($request->q, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('code', 'ilike', "%{$s}%")
                  ->orWhere('label', 'ilike', "%{$s}%")
                  ->orWhere('description', 'ilike', "%{$s}%");
            }))
            ->orderBy('code')
            ->get();

        return view('finance.referentiels.sources.index', compact('sources'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Source::create($data);
        return redirect()->route('finance.referentiels.sources.index')->with('success', 'Source créée.');
    }

    public function update(Request $request, Source $source)
    {
        $data = $this->validateData($request, $source);
        $source->update($data);
        return redirect()->route('finance.referentiels.sources.index')->with('success', 'Source mise à jour.');
    }

    public function destroy(Source $source)
    {
        if ($source->budgetSources()->exists()) {
            return back()->with('error', 'Cette source est utilisée dans un ou plusieurs budgets — impossible de la supprimer.');
        }
        $source->delete();
        return redirect()->route('finance.referentiels.sources.index')->with('success', 'Source supprimée.');
    }

    private function validateData(Request $request, ?Source $source = null): array
    {
        $uniqueRule = $source ? 'unique:sources,code,'.$source->id : 'unique:sources,code';
        return $request->validate([
            'code'        => "required|string|max:50|{$uniqueRule}",
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);
    }
}
