<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Paie;
use App\Models\Payement;
use Illuminate\Http\Request;

class PayementController extends Controller
{
    public function index(Request $request)
    {
        $payements = Payement::query()
            ->with(['employee', 'paie', 'executeur'])
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->mode_payement, fn($q, $m) => $q->where('mode_payement', $m))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->when($request->du, fn($q, $d) => $q->whereDate('date_payement', '>=', $d))
            ->when($request->au, fn($q, $d) => $q->whereDate('date_payement', '<=', $d))
            ->orderByDesc('date_payement')
            ->paginate(20)
            ->withQueryString();

        return view('rh.payements.index', compact('payements'));
    }

    public function create(Request $request)
    {
        $paie = $request->paie_id ? Paie::with('employee')->find($request->paie_id) : null;
        $paies = Paie::where('statut', 1)->with('employee')->orderByDesc('debut')->limit(100)->get();
        return view('rh.payements.create', compact('paie', 'paies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'paie_id'            => ['required', 'exists:gpaies,id'],
            'employee_id'        => ['required', 'exists:employees,id'],
            'date_payement'      => ['required', 'date'],
            'montant'            => ['required', 'numeric', 'min:0.01'],
            'mode_payement'      => ['required', 'in:virement,cheque,especes,mobile_money'],
            'reference_payement' => ['nullable', 'string', 'max:100'],
            'banque'             => ['nullable', 'string', 'max:100'],
            'iban'               => ['nullable', 'string', 'max:50'],
        ]);

        $paie = Paie::with('payements')->findOrFail($data['paie_id']);
        $dejaPaye = $paie->payements()->where('statut', 1)->sum('montant');
        $reste = (float) $paie->net_a_payer - (float) $dejaPaye;

        if ((float) $data['montant'] > $reste + 0.01) {
            return back()->withErrors([
                'montant' => "Le montant ({$data['montant']}) depasse le reste a payer ({$reste})."
            ])->withInput();
        }

        $data['execute_par'] = auth()->id();
        $data['statut'] = 1;
        Payement::create($data);

        // Si totalement payé, marquer la paie comme payée
        if (((float) $dejaPaye + (float) $data['montant']) >= ((float) $paie->net_a_payer - 0.01)) {
            $paie->update(['statut' => 2]);
        }

        return redirect()->route('rh.payements.index')->with('success', 'Payement enregistre.');
    }

    public function show(Payement $payement)
    {
        $payement->load(['employee', 'paie', 'executeur']);
        return view('rh.payements.show', compact('payement'));
    }

    public function destroy(Payement $payement)
    {
        $paie = $payement->paie;
        $payement->delete();
        // Repasser la paie en validée si plus aucun paiement actif
        if ($paie && $paie->payements()->where('statut', 1)->doesntExist()) {
            $paie->update(['statut' => 1]);
        }
        return redirect()->route('rh.payements.index')->with('success', 'Payement supprime.');
    }
}
