<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Pointage;
use App\Services\Rh\PointageMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Pointage en self-service depuis l'intranet.
 * L'employé connecté visite une URL et déclenche son pointage.
 * Le mode est détecté automatiquement (intranet vs telework) selon l'IP.
 */
class PointageSelfController extends Controller
{
    public function __construct(private PointageMethodService $service) {}

    /**
     * Page self-service : affiche l'état du jour + bouton de pointage.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;
        abort_if(!$employee, 403, 'Votre compte utilisateur n\'est pas lié à un employé.');

        $ip = $request->ip();
        $modePrevu = $this->service->detecterMode($ip);

        $pointageDuJour = Pointage::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();

        return view('rh.pointages.self', [
            'employee'        => $employee,
            'pointageDuJour'  => $pointageDuJour,
            'modePrevu'       => $modePrevu,
            'ip'              => $ip,
        ]);
    }

    /**
     * Déclenche le pointage. Mode déterminé par l'IP.
     */
    public function pointer(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;
        abort_if(!$employee, 403, 'Aucun employé lié à ce compte.');

        if (!$this->service->horaireAutorise()) {
            return back()->with('error', 'Pointage hors plage horaire autorisée.');
        }

        $ip = $request->ip();
        $mode = $this->service->detecterMode($ip);

        $pointage = $this->service->pointer(
            employee: $employee,
            ip: $ip,
            userAgent: $request->userAgent(),
            mode: $mode,
            causedBy: $user,
        );

        Log::info('Pointage self-service', [
            'employee_id' => $employee->id,
            'user_id'     => $user->id,
            'ip'          => $ip,
            'mode'        => $mode,
            'pointage_id' => $pointage->id,
        ]);

        if ($mode === 'teletravail') {
            $message = 'Pointage enregistré en mode télétravail. Il sera transmis à votre N+1 pour validation.';
        } else {
            $message = $pointage->heure_sortie
                ? 'Sortie enregistrée à ' . substr($pointage->heure_sortie, 0, 5) . '. Bonne fin de journée !'
                : 'Entrée enregistrée à ' . substr($pointage->heure_entree, 0, 5) . '. Bonne journée !';
        }

        return redirect()->route('pointage.self')->with('success', $message);
    }

    /**
     * Page de validation pour les managers (N+1) — liste des pointages télétravail en attente.
     */
    public function validationN1Index(Request $request)
    {
        $user = $request->user();
        $employee = $user?->employee;

        // Les pointages à valider sont ceux des subordonnés (employees ayant superieur_hierarchique = $employee->id)
        $subordonnesIds = $employee
            ? \App\Models\Employee::where('superieur_hierarchique', $employee->id)->pluck('id')->all()
            : [];

        // Un super-admin voit tout
        $estSuperAdmin = $user->hasRole('super-admin');

        $pointages = Pointage::query()
            ->with(['employee', 'saisiPar'])
            ->enAttenteValidationN1()
            ->when(!$estSuperAdmin, fn($q) => $q->whereIn('employee_id', $subordonnesIds))
            ->orderBy('date')
            ->paginate(20)
            ->withQueryString();

        return view('rh.pointages.validation-n1', compact('pointages', 'estSuperAdmin'));
    }

    public function validationN1Action(Request $request, Pointage $pointage)
    {
        $user = $request->user();
        $employee = $user?->employee;

        // Vérification d'autorité hiérarchique (sauf super-admin)
        if (!$user->hasRole('super-admin')) {
            $estSuperieur = $employee
                && $pointage->employee
                && $pointage->employee->superieur_hierarchique === $employee->id;
            abort_if(!$estSuperieur, 403, 'Vous n\'êtes pas le supérieur hiérarchique de cet employé.');
        }

        $data = $request->validate([
            'decision'    => 'required|in:approuve,rejete',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $pointage->update([
            'validation_n1_decision'     => $data['decision'],
            'validation_n1_commentaire'  => $data['commentaire'] ?? null,
            'validation_n1_par'          => $user->id,
            'validation_n1_at'           => now(),
            // Si approuvé → statut = 1 (validé pour la paie) ; si rejeté → statut reste 0 (brouillon)
            'statut'                     => $data['decision'] === 'approuve' ? 1 : 0,
            'requires_validation_n1'     => false,
        ]);

        activity('pointage_validation_n1')
            ->causedBy($user)
            ->performedOn($pointage)
            ->withProperties(['decision' => $data['decision'], 'commentaire' => $data['commentaire'] ?? null])
            ->log("Pointage {$data['decision']} par N+1");

        return back()->with('success', $data['decision'] === 'approuve'
            ? 'Pointage approuvé et reporté en paie.'
            : 'Pointage rejeté. L\'employé devra corriger.');
    }
}
