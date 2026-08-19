<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\Rh\PointageMethodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * QR code générique partagé (affiché à l'entrée du bureau).
 * Tous les employés peuvent scanner ce QR, puis saisissent matricule + PIN.
 *
 * Sécurité :
 *   • Token générique dans config('pointage.qr_generique_token') — sinon refus
 *   • PIN bcrypt-hashé sur l'employee
 *   • Rate limit anti-bruteforce sur (matricule, ip)
 */
class PointageGeneriqueController extends Controller
{
    public function __construct(private PointageMethodService $service) {}

    /**
     * Affiche le formulaire matricule + PIN après scan du QR générique.
     */
    public function show(string $token)
    {
        $expected = $this->service->tokenQrGenerique();
        abort_if(!$expected || !hash_equals($expected, $token), 404, 'QR générique invalide ou expiré.');

        return view('rh.pointages.qr-generique', ['token' => $token]);
    }

    /**
     * Traite la saisie matricule + PIN.
     */
    public function check(Request $request, string $token)
    {
        $expected = $this->service->tokenQrGenerique();
        abort_if(!$expected || !hash_equals($expected, $token), 404, 'QR générique invalide.');

        $data = $request->validate([
            'matricule' => 'required|string|max:50',
            'pin'       => 'required|string|min:4|max:8',
        ]);

        if (!$this->service->horaireAutorise()) {
            return back()->withErrors(['pin' => 'Pointage hors plage horaire autorisée.']);
        }

        try {
            $employee = $this->service->authenticatePin($data['matricule'], $data['pin'], $request->ip());
        } catch (ValidationException $e) {
            // Log discret (sans le PIN évidemment) pour audit sécurité
            Log::warning('Échec PIN pointage générique', [
                'matricule_tente' => $data['matricule'],
                'ip'              => $request->ip(),
                'user_agent'      => $request->userAgent(),
            ]);
            throw $e;
        }

        $pointage = $this->service->pointer(
            employee: $employee,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            mode: 'qr_generique',
        );

        Log::info('Pointage QR générique', [
            'employee_id'  => $employee->id,
            'matricule'    => $employee->matricule,
            'ip'           => $request->ip(),
            'pointage_id'  => $pointage->id,
        ]);

        return view('rh.pointages.qr-result', [
            'employee' => $employee,
            'success'  => true,
            'pointage' => $pointage,
            'message'  => $pointage->heure_sortie
                ? 'Sortie enregistrée à ' . substr($pointage->heure_sortie, 0, 5) . '. Bonne fin de journée !'
                : 'Entrée enregistrée à ' . substr($pointage->heure_entree, 0, 5) . '. Bonne journée !',
        ]);
    }

    /**
     * Page admin de gestion du QR générique : aperçu + lien d'impression + régénération du token.
     */
    public function admin()
    {
        $token = $this->service->tokenQrGenerique();
        $configured = !empty($token);
        return view('rh.pointages.qr-generique-admin', compact('token', 'configured'));
    }

    /**
     * Régénère le token générique. Met à jour le .env, écrase la valeur cache config.
     * Le ré-impression du QR est obligatoire après cette action.
     */
    public function regenererToken()
    {
        $nouveau = bin2hex(random_bytes(16)); // 32 chars hex

        // Écriture dans .env (préserve les autres clés)
        $envPath = base_path('.env');
        if (!is_writable($envPath)) {
            return back()->with('error', "Le fichier .env n'est pas inscriptible. Mettez à la main POINTAGE_QR_GENERIQUE_TOKEN=$nouveau et videz le cache.");
        }

        $envContent = file_get_contents($envPath);
        if (preg_match('/^POINTAGE_QR_GENERIQUE_TOKEN=.*/m', $envContent)) {
            $envContent = preg_replace(
                '/^POINTAGE_QR_GENERIQUE_TOKEN=.*/m',
                "POINTAGE_QR_GENERIQUE_TOKEN=$nouveau",
                $envContent
            );
        } else {
            $envContent .= "\nPOINTAGE_QR_GENERIQUE_TOKEN=$nouveau\n";
        }
        file_put_contents($envPath, $envContent);

        // Recharge la config en mémoire pour cette requête
        config(['pointage.qr_generique_token' => $nouveau]);

        // Note : en production avec config cache, il faudra `php artisan config:clear` après
        return redirect()->route('rh.pointages.qr-generique.admin')
            ->with('success', 'Token régénéré. Réimprimez le QR générique — l\'ancien n\'est plus valide.');
    }

    /**
     * Renvoie l'image PNG du QR générique.
     */
    public function image()
    {
        abort_if(!$this->service->tokenQrGenerique(), 404, 'QR générique non configuré.');
        $png = $this->service->genererQrGeneriquePng();
        return response($png, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Page imprimable du QR générique (A4, à afficher à l'entrée).
     */
    public function imprimer()
    {
        abort_if(!$this->service->tokenQrGenerique(), 404, 'QR générique non configuré.');
        $url = $this->service->urlQrGenerique();
        return view('rh.pointages.qr-generique-imprimable', compact('url'));
    }

    /**
     * Gestion du PIN d'un employé (admin) — définit ou réinitialise.
     */
    public function definirPinEmployee(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'pin' => 'required|string|regex:/^\d{4,6}$/',
        ], [
            'pin.regex' => 'Le PIN doit être 4 à 6 chiffres.',
        ]);

        $employee->forceFill([
            'pointage_pin'             => $data['pin'], // cast 'hashed' bcrypt-isera
            'pointage_pin_changed_at'  => now(),
        ])->save();

        activity('pointage_pin')
            ->causedBy(auth()->user())
            ->performedOn($employee)
            ->log('PIN de pointage défini/réinitialisé par admin');

        return back()->with('success', "PIN défini pour {$employee->noms} {$employee->prenoms}. Communiquez-le par canal sécurisé.");
    }
}
