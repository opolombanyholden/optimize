<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        $user = request()->user();
        if (!$user || !($user->must_change_password ?? false)) {
            return redirect()->route('dashboard');
        }
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 403);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.required' => 'Le mot de passe actuel (temporaire) est requis.',
            'password.required'         => 'Le nouveau mot de passe est requis.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        if (Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
        }

        $user->forceFill([
            'password'             => Hash::make($data['password']),
            'must_change_password' => false,
            'password_changed_at'  => now(),
        ])->save();

        activity('password_changed')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('Mot de passe changé par l\'utilisateur (suite à un reset)');

        // Régénère la session pour éviter session fixation
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }
}
