<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Exercice;
use App\Models\Paie;
use App\Models\Rubrique;
use App\Services\Rh\PaieCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaieController extends Controller
{
    /**
     * Aperçu d'un bulletin calculé (sans persistance) — utilisé par l'UI pour prévisualiser.
     */
    public function apercu(Request $request, PaieCalculator $calc)
    {
        $data = $request->validate([
            'employee_id'         => ['required', 'exists:employees,id'],
            'debut'               => ['required', 'date'],
            'fin'                 => ['required', 'date', 'after_or_equal:debut'],
            'salaire_base'        => ['nullable', 'numeric', 'min:0'],
            'primes'              => ['nullable', 'numeric', 'min:0'],
            'indemnites'          => ['nullable', 'numeric', 'min:0'],
            'heures_sup'          => ['nullable', 'numeric', 'min:0'],
            'avances'             => ['nullable', 'numeric', 'min:0'],
            'retenues_manuelles'  => ['nullable', 'numeric', 'min:0'],
            'jours_travailles'    => ['nullable', 'numeric', 'min:0', 'max:31'],
        ]);
        return response()->json($calc->calculer($data));
    }

    /**
     * Génère un bulletin via le moteur à rubriques paramétrables.
     */
    public function genererBulletin(Request $request, PaieCalculator $calc)
    {
        $data = $request->validate([
            'employee_id'         => ['required', 'exists:employees,id'],
            'debut'               => ['required', 'date'],
            'fin'                 => ['required', 'date', 'after_or_equal:debut'],
            'label'               => ['nullable', 'string', 'max:255'],
            'salaire_base'        => ['nullable', 'numeric', 'min:0'],
            'primes'              => ['nullable', 'numeric', 'min:0'],
            'indemnites'          => ['nullable', 'numeric', 'min:0'],
            'heures_sup'          => ['nullable', 'numeric', 'min:0'],
            'avances'             => ['nullable', 'numeric', 'min:0'],
            'retenues_manuelles'  => ['nullable', 'numeric', 'min:0'],
            'jours_travailles'    => ['nullable', 'numeric', 'min:0', 'max:31'],
        ]);
        $resultat = $calc->calculer($data);
        $paie = $calc->persister($resultat);
        return redirect()->route('rh.paie.show', $paie)->with('success', 'Bulletin genere via le moteur de rubriques.');
    }

    /**
     * Validation d'un bulletin (brouillon -> valide).
     */
    public function valider(Paie $paie)
    {
        if ($paie->statut != 0) {
            return back()->with('error', 'Ce bulletin n\'est pas en brouillon.');
        }
        $paie->update(['statut' => 1]);
        return back()->with('success', 'Bulletin valide.');
    }

    public function index(Request $request)
    {
        $paies = Paie::query()
            ->with(['employee'])
            ->when($request->exercice_id, fn($q, $id) => $q->where('exercice_id', $id))
            ->when($request->mois, fn($q, $m) => $q->whereMonth('debut', $m))
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->statut !== null, fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('rh.paie.index', compact('paies'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->get();
        $exercices = Exercice::where('statut', 1)->get();

        return view('rh.paie.create', compact('employees', 'exercices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'employee_id' => 'required|exists:employees,id',
            'debut' => 'required|date',
            'fin' => 'required|date|after_or_equal:debut',
            'salaire_base' => 'required|numeric|min:0',
            'primes' => 'nullable|numeric|min:0',
            'indemnites' => 'nullable|numeric|min:0',
            'heures_sup' => 'nullable|numeric|min:0',
            'cotisations_salariales' => 'nullable|numeric|min:0',
            'cotisations_patronales' => 'nullable|numeric|min:0',
            'irpp' => 'nullable|numeric|min:0',
            'avances' => 'nullable|numeric|min:0',
            'retenues' => 'nullable|numeric|min:0',
        ]);

        $validated['brut'] = ($validated['salaire_base'] ?? 0)
            + ($validated['primes'] ?? 0)
            + ($validated['indemnites'] ?? 0)
            + ($validated['heures_sup'] ?? 0);

        $validated['net_imposable'] = $validated['brut'] - ($validated['cotisations_salariales'] ?? 0);
        $validated['net_a_payer'] = $validated['net_imposable']
            - ($validated['irpp'] ?? 0)
            - ($validated['avances'] ?? 0)
            - ($validated['retenues'] ?? 0);

        Paie::create($validated);

        return redirect()->route('rh.paie.index')->with('success', 'Bulletin de paie cree avec succes.');
    }

    public function show(string $id)
    {
        $paie = Paie::with(['employee'])->findOrFail($id);

        return view('rh.paie.show', compact('paie'));
    }

    public function edit(string $id)
    {
        $paie = Paie::findOrFail($id);
        $employees = Employee::where('statut', 1)->get();
        $exercices = Exercice::where('statut', 1)->get();

        return view('rh.paie.edit', compact('paie', 'employees', 'exercices'));
    }

    public function update(Request $request, string $id)
    {
        $paie = Paie::findOrFail($id);

        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'salaire_base' => 'sometimes|numeric|min:0',
            'primes' => 'nullable|numeric|min:0',
            'indemnites' => 'nullable|numeric|min:0',
            'heures_sup' => 'nullable|numeric|min:0',
            'cotisations_salariales' => 'nullable|numeric|min:0',
            'cotisations_patronales' => 'nullable|numeric|min:0',
            'irpp' => 'nullable|numeric|min:0',
            'avances' => 'nullable|numeric|min:0',
            'retenues' => 'nullable|numeric|min:0',
            'statut' => 'nullable|integer|in:0,1,2',
        ]);

        $paie->update($validated);

        return redirect()->route('rh.paie.index')->with('success', 'Bulletin de paie mis a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $paie = Paie::findOrFail($id);
        $paie->delete();

        return redirect()->route('rh.paie.index')->with('success', 'Bulletin de paie supprime avec succes.');
    }

    public function pdf(Paie $paie)
    {
        $paie->load('employee');
        $filename = sprintf('bulletin_%s_%s.pdf',
            $paie->numero_bulletin ?: ('B' . $paie->id),
            $paie->debut?->format('Y-m') ?? now()->format('Y-m')
        );
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

        $pdf = Pdf::loadView('rh.paie.pdf', compact('paie'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);

        return $pdf->stream($filename);
    }
}
