<?php

namespace App\Http\Controllers\Finance\V2;

use App\Http\Controllers\Controller;
use App\Models\Finance\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index()
    {
        $sources = Source::withCount('budgetSources')->orderBy('code')->paginate(20);
        return view('finance.v2.sources.index', compact('sources'));
    }

    public function create()  { return view('finance.v2.sources.create'); }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'        => 'required|string|max:50|unique:sources,code',
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        Source::create($data);
        return redirect()->route('finance.v2.sources.index')->with('success', 'Source créée.');
    }

    public function edit(Source $source) { return view('finance.v2.sources.edit', compact('source')); }

    public function update(Request $r, Source $source)
    {
        $data = $r->validate([
            'code'        => 'required|string|max:50|unique:sources,code,' . $source->id,
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $source->update($data);
        return redirect()->route('finance.v2.sources.index')->with('success', 'Source mise à jour.');
    }

    public function destroy(Source $source)
    {
        if ($source->budgetSources()->exists()) {
            return back()->with('error', 'Cette source est utilisée dans des budgets.');
        }
        $source->delete();
        return redirect()->route('finance.v2.sources.index')->with('success', 'Source supprimée.');
    }
}
