<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si l'utilisateur connecté a must_change_password=true (compte initial ou reset admin),
 * on le force à passer par la vue de changement de mot de passe avant toute autre action.
 * Routes whitelist : la page de changement elle-même, le logout et les assets.
 */
class EnsurePasswordIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !($user->must_change_password ?? false)) {
            return $next($request);
        }

        $allowed = [
            'password.force-change.show',
            'password.force-change.update',
            'logout',
        ];

        $current = $request->route()?->getName();
        if (in_array($current, $allowed, true)) {
            return $next($request);
        }

        // Pour les requêtes AJAX/JSON : 423 Locked
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vous devez changer votre mot de passe avant de continuer.',
            ], 423);
        }

        return redirect()
            ->route('password.force-change.show')
            ->with('warning', 'Pour des raisons de sécurité, vous devez définir un nouveau mot de passe avant de continuer.');
    }
}
