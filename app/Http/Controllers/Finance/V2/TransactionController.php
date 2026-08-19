<?php

namespace App\Http\Controllers\Finance\V2;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Entite;
use App\Models\Exercice;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDetail;
use App\Models\Ligne;
use App\Models\ModeReglement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $r)
    {
        $transactions = Transaction::with(['ligne.titre', 'compte', 'modeReglement', 'entite'])
            ->when($r->type !== null && $r->type !== '', fn($q) => $q->where('type', (int) $r->type))
            ->when($r->status !== null && $r->status !== '', fn($q) => $q->where('status', (int) $r->status))
            ->when($r->exercice_id, fn($q, $id) => $q->where('exercice_id', $id))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $exercices = Exercice::orderByDesc('id')->get();
        return view('finance.v2.transactions.index', compact('transactions', 'exercices'));
    }

    public function create(Request $r)
    {
        $type = (int) ($r->type ?? 0);
        return view('finance.v2.transactions.create', [
            'type'         => $type,
            'exercices'    => Exercice::orderByDesc('id')->get(),
            'lignes'       => Ligne::orderBy('code')->get(),
            'comptes'      => Compte::orderBy('code')->get(),
            'entites'      => Entite::orderBy('code')->get(),
            'modes'        => ModeReglement::orderBy('code')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'type'              => 'required|integer|in:0,1',
            'date'              => 'required|date',
            'code'              => 'nullable|string|max:100',
            'label'             => 'required|string|max:255',
            'description'       => 'nullable|string',
            'montant'           => 'required|numeric|min:0.01',
            'beneficiaire'      => 'nullable|string|max:255',
            'beneficiaire_externe' => 'nullable|boolean',
            'devise'            => 'nullable|string|max:10',
            'entite_id'         => 'nullable|exists:entites,id',
            'compte_id'         => 'nullable|exists:comptes,id',
            'ligne_id'          => 'nullable|exists:lignes,id',
            'exercice_id'       => 'nullable|exists:exercices,id',
            'mode_reglement_id' => 'nullable|exists:mode_reglements,id',
            'details'           => 'nullable|array',
            'details.*.label'   => 'nullable|string|max:255',
            'details.*.quantity' => 'nullable|integer|min:1',
            'details.*.montant'  => 'nullable|numeric|min:0',
            'details.*.ligne_id' => 'nullable|exists:lignes,id',
        ]);

        $data['montant_restant'] = $data['montant'];
        $data['status'] = 0;
        $data['id_user'] = auth()->id();
        $data['devise'] ??= 'XAF';

        $tx = DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            $totalDetails = 0;
            foreach (($data['details'] ?? []) as $d) {
                if (empty($d['label'])) continue;
                $mt = (float) ($d['montant'] ?? 0);
                if ($mt <= 0) continue;
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'label'          => $d['label'],
                    'quantity'       => $d['quantity'] ?? 1,
                    'montant'        => $mt,
                    'ligne_id'       => $d['ligne_id'] ?? null,
                ]);
                $totalDetails += $mt;
            }
            if ($totalDetails > 0) {
                $transaction->update(['montant' => $totalDetails, 'montant_restant' => $totalDetails]);
            }
            return $transaction;
        });

        return redirect()->route('finance.v2.transactions.show', $tx)
            ->with('success', "Transaction {$tx->code} créée en brouillon.");
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['ligne.titre', 'compte', 'entite', 'modeReglement', 'user', 'details.ligne']);
        return view('finance.v2.transactions.show', compact('transaction'));
    }

    public function soumettre(Transaction $transaction)
    {
        if (!$transaction->est_soumissible) return back()->with('error', 'Non soumissible.');
        $transaction->update(['status' => 1]);
        return back()->with('success', 'Transaction soumise.');
    }

    public function valider(Transaction $transaction)
    {
        if (!$transaction->est_validable) return back()->with('error', 'Non validable.');
        $transaction->update(['status' => 2, 'isvalide' => true]);
        return back()->with('success', 'Transaction validée.');
    }

    public function payer(Transaction $transaction)
    {
        if (!$transaction->est_payable) return back()->with('error', 'Non payable.');
        $transaction->update(['status' => 3, 'montant_restant' => 0]);
        return back()->with('success', 'Transaction ' . ($transaction->type == 0 ? 'payée' : 'encaissée') . '.');
    }

    public function annuler(Transaction $transaction)
    {
        $transaction->update(['status' => 4]);
        return back()->with('success', 'Transaction annulée.');
    }
}
