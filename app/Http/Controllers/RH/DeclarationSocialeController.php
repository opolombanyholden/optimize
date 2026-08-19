<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\DeclarationSociale;
use App\Services\Rh\DeclarationSocialeBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeclarationSocialeController extends Controller
{
    public function index(Request $request)
    {
        $declarations = DeclarationSociale::query()
            ->with(['createur', 'validateur'])
            ->when($request->type_organisme, fn($q, $t) => $q->where('type_organisme', $t))
            ->when($request->annee, fn($q, $a) => $q->where('annee', $a))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->orderByDesc('annee')
            ->orderByDesc('trimestre')
            ->orderByDesc('mois')
            ->paginate(20)
            ->withQueryString();

        return view('rh.declarations-sociales.index', compact('declarations'));
    }

    public function create()
    {
        return view('rh.declarations-sociales.create');
    }

    public function apercu(Request $request, DeclarationSocialeBuilder $builder)
    {
        $data = $request->validate([
            'type_organisme' => 'required|in:cnss,cnamgs,fnh,cfp',
            'annee'          => 'required|integer|min:2020|max:2100',
            'mois'           => 'nullable|integer|min:1|max:12',
            'trimestre'      => 'nullable|integer|min:1|max:4',
        ]);
        $resultat = $builder->calculer(
            $data['type_organisme'], $data['annee'],
            $data['mois'] ?? null, $data['trimestre'] ?? null
        );
        return response()->json($resultat);
    }

    public function store(Request $request, DeclarationSocialeBuilder $builder)
    {
        $data = $request->validate([
            'type_organisme' => 'required|in:cnss,cnamgs,fnh,cfp',
            'annee'          => 'required|integer|min:2020|max:2100',
            'mois'           => 'nullable|integer|min:1|max:12',
            'trimestre'      => 'nullable|integer|min:1|max:4',
        ]);
        // Doublon (même code) ? On bloque.
        $resultat = $builder->calculer(
            $data['type_organisme'], $data['annee'],
            $data['mois'] ?? null, $data['trimestre'] ?? null
        );
        $declaration = $builder->persister($resultat, auth()->id());
        return redirect()->route('rh.declarations-sociales.show', $declaration)
            ->with('success', "Déclaration {$declaration->code} créée ({$declaration->nombre_employes} employé(s)).");
    }

    public function show(DeclarationSociale $declarations_sociale)
    {
        $declarations_sociale->load(['lignes.employee', 'createur', 'validateur', 'deposeur']);
        return view('rh.declarations-sociales.show', ['declaration' => $declarations_sociale]);
    }

    public function valider(DeclarationSociale $declarations_sociale)
    {
        if ($declarations_sociale->statut !== 0) {
            return back()->with('error', 'Cette déclaration n\'est plus en brouillon.');
        }
        $declarations_sociale->update([
            'statut'      => 1,
            'validee_par' => auth()->id(),
            'validee_at'  => now(),
        ]);
        return back()->with('success', 'Déclaration validée.');
    }

    public function deposer(Request $request, DeclarationSociale $declarations_sociale)
    {
        if ($declarations_sociale->statut !== 1) {
            return back()->with('error', 'La déclaration doit être validée avant dépôt.');
        }
        $data = $request->validate([
            'reference_depot' => 'required|string|max:100',
        ]);
        $declarations_sociale->update([
            'statut'           => 2,
            'reference_depot'  => $data['reference_depot'],
            'deposee_par'      => auth()->id(),
            'deposee_at'       => now(),
        ]);
        return back()->with('success', 'Déclaration marquée comme déposée.');
    }

    public function destroy(DeclarationSociale $declarations_sociale)
    {
        if ($declarations_sociale->statut > 0) {
            return back()->with('error', 'Une déclaration validée ne peut être supprimée.');
        }
        $declarations_sociale->delete();
        return redirect()->route('rh.declarations-sociales.index')->with('success', 'Déclaration supprimée.');
    }

    public function pdf(DeclarationSociale $declarations_sociale)
    {
        $declarations_sociale->load(['lignes.employee']);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $declarations_sociale->code) . '.pdf';
        $pdf = Pdf::loadView('rh.declarations-sociales.pdf', ['declaration' => $declarations_sociale])
            ->setPaper('A4', 'landscape')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);
        return $pdf->stream($filename);
    }

    public function csv(DeclarationSociale $declarations_sociale): StreamedResponse
    {
        $declarations_sociale->load(['lignes']);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $declarations_sociale->code) . '.csv';

        return response()->streamDownload(function () use ($declarations_sociale) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Matricule employeur', 'Matricule organisme', 'NIP',
                'Noms', 'Prénoms', 'Sexe', 'Date naissance',
                'Jours travaillés', 'Brut', 'Brut plafonné',
                'Cotisation salariale', 'Cotisation patronale',
            ], ';');
            foreach ($declarations_sociale->lignes as $l) {
                fputcsv($out, [
                    $l->matricule_employeur,
                    $l->matricule_organisme,
                    $l->nip,
                    $l->noms,
                    $l->prenoms,
                    $l->sexe,
                    $l->date_naissance?->format('d/m/Y'),
                    $l->nb_jours_travailles,
                    number_format($l->brut, 2, ',', ''),
                    number_format($l->brut_plafonne, 2, ',', ''),
                    number_format($l->cot_salariale, 2, ',', ''),
                    number_format($l->cot_patronale, 2, ',', ''),
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
