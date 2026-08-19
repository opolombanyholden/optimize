<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\Rh\PointageMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Endpoints publics pour le pointage par QR code.
 * Authentification par token unique de l'employé (URL contient le token).
 * Pas de login Laravel requis (l'employé scanne avec son téléphone perso).
 */
class PointageQrController extends Controller
{
    public function __construct(private PointageMethodService $service) {}

    /**
     * Écran de scan : affichage de confirmation avant pointage (anti-double-scan accidentel).
     */
    public function scan(string $token)
    {
        $employee = Employee::where('pointage_token', $token)->where('statut', 1)->first();
        abort_if(!$employee, 404, 'QR code invalide ou employé inactif.');

        return view('rh.pointages.qr-scan', compact('employee', 'token'));
    }

    /**
     * Confirme le pointage (POST depuis le bouton de l'écran scan).
     */
    public function confirmer(Request $request, string $token)
    {
        $employee = Employee::where('pointage_token', $token)->where('statut', 1)->first();
        abort_if(!$employee, 404, 'QR code invalide.');

        // Rate-limit : max 5 scans / 5min par token (anti-abuse)
        $key = 'pointage-qr:' . sha1($token);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return view('rh.pointages.qr-result', [
                'employee' => $employee,
                'success'  => false,
                'message'  => "Trop de tentatives, réessayez dans $seconds secondes.",
            ]);
        }
        RateLimiter::hit($key, 300);

        if (!$this->service->horaireAutorise()) {
            return view('rh.pointages.qr-result', [
                'employee' => $employee,
                'success'  => false,
                'message'  => "Pointage hors plage horaire autorisée.",
            ]);
        }

        $ip = $request->ip();
        // QR scan : toujours considéré comme « qr_code » (l'employé est physiquement présent au bureau,
        // ou bien on accepte le QR comme preuve de présence — au choix de l'organisation).
        // Si on veut être strict, on peut combiner QR + check IP intranet.
        $mode = 'qr_code';

        $pointage = $this->service->pointer(
            employee: $employee,
            ip: $ip,
            userAgent: $request->userAgent(),
            mode: $mode,
        );

        Log::info('Pointage par QR code', [
            'employee_id'  => $employee->id,
            'matricule'    => $employee->matricule,
            'ip'           => $ip,
            'pointage_id'  => $pointage->id,
            'heure_entree' => $pointage->heure_entree,
            'heure_sortie' => $pointage->heure_sortie,
        ]);

        return view('rh.pointages.qr-result', [
            'employee' => $employee,
            'success'  => true,
            'pointage' => $pointage,
            'message'  => $pointage->heure_sortie
                ? 'Sortie enregistrée. Bonne fin de journée !'
                : 'Entrée enregistrée. Bonne journée !',
        ]);
    }

    /**
     * Affichage du QR code (PNG) pour impression. Réservé à l'admin / RH.
     */
    public function image(Employee $employee)
    {
        // Permission contrôlée au niveau route (permission:update:employee)
        $png = $this->service->genererQrPng($employee);
        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'inline; filename="qr-' . $employee->matricule . '.png"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    /**
     * Page imprimable contenant le QR + identité de l'employé.
     */
    public function imprimer(Employee $employee)
    {
        $url = $this->service->urlQr($employee);
        return view('rh.pointages.qr-imprimable', compact('employee', 'url'));
    }

    /**
     * Régénère un nouveau token (l'ancien QR devient invalide).
     */
    public function regenererToken(Employee $employee)
    {
        $this->service->genererToken($employee, regenerer: true);
        return back()->with('success', 'QR code régénéré. L\'ancien n\'est plus valide.');
    }
}
