<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    protected $redirectTo = '/dashboard';

    /**
     * Hook exécuté après une authentification réussie.
     * Flash le drapeau qui déclenche l'affichage de la modale de choix d'espace
     * sur la première page qui suit le login (le dashboard par défaut).
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        $request->session()->flash('show_workspace_picker', true);
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
            new Middleware('auth', only: ['logout']),
        ];
    }
}
