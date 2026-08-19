<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Notifications\PasswordResetByAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Réinitialisation administrative du mot de passe d'un employé.
 *
 * Procédure conforme aux bonnes pratiques de sécurité :
 *   - Le mot de passe actuel n'est jamais affiché ni lisible.
 *   - L'administrateur doit confirmer son identité (mot de passe).
 *   - Deux modes :
 *       (a) Génération automatique d'un mot de passe temporaire fort
 *       (b) Saisie manuelle par l'administrateur (avec règles de complexité)
 *   - Le mot de passe est envoyé à l'utilisateur par email.
 *   - L'utilisateur cible est marqué « must_change_password » → forcé au prochain login.
 *   - Audit log Spatie : qui a réinitialisé, quand, pour qui, comment (canal).
 */
class EmployeePasswordController extends Controller
{
    public function show(Employee $employee)
    {
        abort_if(!$employee->user, 404, 'Cet employé n\'a pas de compte utilisateur lié.');
        return view('rh.employees.reset-password', compact('employee'));
    }

    public function reset(Request $request, Employee $employee)
    {
        abort_if(!$employee->user, 404, 'Cet employé n\'a pas de compte utilisateur lié.');

        $data = $request->validate([
            'mode'                   => ['required', 'in:auto,manuel'],
            'password'               => ['required_if:mode,manuel', 'nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'admin_password'         => ['required', 'string'],
            'confirmation'           => ['required', 'accepted'],
            'motif'                  => ['nullable', 'string', 'max:255'],
        ], [
            'mode.required'              => 'Choisissez un mode (généré ou manuel).',
            'password.required_if'       => 'Le mot de passe est requis en mode manuel.',
            'password.confirmed'         => 'La confirmation du mot de passe ne correspond pas.',
            'admin_password.required'    => 'Votre mot de passe est requis pour confirmer cette action.',
            'confirmation.accepted'      => 'Vous devez cocher la case de confirmation.',
        ]);

        // Re-authentification de l'admin
        if (!Hash::check($data['admin_password'], $request->user()->password)) {
            Log::warning('Tentative de reset password avec mauvais mot de passe admin', [
                'admin_id'    => $request->user()->id,
                'admin_email' => $request->user()->email,
                'cible_id'    => $employee->user->id,
                'cible_email' => $employee->user->email,
                'ip'          => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'admin_password' => 'Mot de passe administrateur incorrect.',
            ]);
        }

        $cible = $employee->user;

        // Génération ou récupération du mot de passe
        $nouveau = $data['mode'] === 'manuel'
            ? $data['password']
            : $this->genererMotDePasseTemporaire();

        // 1) Persistance du nouveau hash + invalidation des sessions
        $cible->forceFill([
            'password'             => Hash::make($nouveau),
            'must_change_password' => true,
            'password_reset_at'    => now(),
            'password_reset_by'    => $request->user()->id,
            'remember_token'       => Str::random(60),
        ])->save();

        // 2) Envoi par email — tracé même en cas d'échec
        $emailEnvoye = false;
        $emailErreur = null;
        try {
            $cible->notify(new PasswordResetByAdminNotification(
                temporaryPassword: $nouveau,
                adminName: $request->user()->name . ' ' . ($request->user()->prenoms ?? ''),
                motif: $data['motif'] ?? null
            ));
            $emailEnvoye = true;
        } catch (\Throwable $e) {
            $emailErreur = $e->getMessage();
            Log::error('Échec envoi email reset password', [
                'cible_id' => $cible->id,
                'error'    => $emailErreur,
            ]);
        }

        // 3) Audit log
        activity('password_reset')
            ->causedBy($request->user())
            ->performedOn($cible)
            ->withProperties([
                'mode'         => $data['mode'],
                'motif'        => $data['motif'] ?? null,
                'email_sent'   => $emailEnvoye,
                'email_to'     => $cible->email,
                'ip'           => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 200),
            ])
            ->log('Mot de passe réinitialisé par un administrateur (' . ($emailEnvoye ? 'email envoyé' : 'email en échec') . ')');

        // 4) Redirection avec message approprié
        $redirect = redirect()->route('rh.employees.show', $employee);

        if ($emailEnvoye) {
            return $redirect->with('success',
                "Mot de passe réinitialisé. Un email a été envoyé à {$cible->email}. "
                . "L'utilisateur devra changer son mot de passe à sa prochaine connexion.");
        }

        // Fallback : si l'email a échoué, on permet l'affichage one-time côté admin
        // (sinon le mot de passe est inaccessible)
        return $redirect
            ->with('warning', "Le mot de passe a été réinitialisé mais l'email n'a pas pu être envoyé : "
                . substr($emailErreur ?? 'erreur SMTP', 0, 120))
            ->with('reset_password_otp', [
                'employee_id'   => $employee->id,
                'employee_name' => trim(($employee->noms ?? '') . ' ' . ($employee->prenoms ?? '')),
                'email'         => $cible->email,
                'temp_password' => $nouveau,
                'generated_at'  => now()->toIso8601String(),
                'fallback'      => true,
            ]);
    }

    /**
     * Génère un mot de passe temporaire fort : 16 caractères mixtes
     * (minuscules + majuscules + chiffres + symboles), sans caractères ambigus.
     */
    private function genererMotDePasseTemporaire(int $longueur = 16): string
    {
        $minuscules = 'abcdefghjkmnpqrstuvwxyz';
        $majuscules = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $chiffres   = '23456789';
        $symboles   = '!@#$%&*+=?';
        $tous       = $minuscules . $majuscules . $chiffres . $symboles;

        $mdp = [
            $minuscules[random_int(0, strlen($minuscules) - 1)],
            $majuscules[random_int(0, strlen($majuscules) - 1)],
            $chiffres[random_int(0, strlen($chiffres) - 1)],
            $symboles[random_int(0, strlen($symboles) - 1)],
        ];
        for ($i = count($mdp); $i < $longueur; $i++) {
            $mdp[] = $tous[random_int(0, strlen($tous) - 1)];
        }
        for ($i = count($mdp) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$mdp[$i], $mdp[$j]] = [$mdp[$j], $mdp[$i]];
        }
        return implode('', $mdp);
    }
}
