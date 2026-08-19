<?php

namespace App\Http\Controllers\Intranet\Annuaire;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['chef', 'parent', 'sousServices'])
            ->withCount('membres')
            ->orderBy('nom')
            ->get();

        $servicesRacine = $services->whereNull('parent_id');

        $users = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);

        return view('intranet.annuaire.services.index', compact('services', 'servicesRacine', 'users'));
    }

    public function show(Service $service)
    {
        $service->load(['chef', 'parent', 'sousServices.chef', 'membres' => fn($q) => $q->orderBy('name')]);

        return view('intranet.annuaire.services.show', compact('service'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'                 => 'required|string|max:255',
            'code'                => 'nullable|string|max:20|unique:intranet_services,code',
            'description'         => 'nullable|string',
            'chef_du_service_id'  => 'nullable|exists:users,id',
            'parent_id'           => 'nullable|exists:intranet_services,id',
            'couleur'             => 'nullable|string|max:20',
            'icone'               => 'nullable|string|max:50',
            'email'               => 'nullable|email',
            'telephone'           => 'nullable|string|max:50',
            'localisation'        => 'nullable|string',
            'type_entite'         => 'nullable|in:direction,service,departement,unite,equipe,autre',
        ]);

        Service::create(array_merge($validated, ['est_actif' => true]));

        return redirect()->route('intranet.annuaire.services.index')->with('success', 'Service créé.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'nom'                 => 'required|string|max:255',
            'code'                => 'nullable|string|max:20|unique:intranet_services,code,' . $service->id,
            'description'         => 'nullable|string',
            'chef_du_service_id'  => 'nullable|exists:users,id',
            'parent_id'           => 'nullable|exists:intranet_services,id',
            'couleur'             => 'nullable|string|max:20',
            'icone'               => 'nullable|string|max:50',
            'email'               => 'nullable|email',
            'telephone'           => 'nullable|string|max:50',
            'localisation'        => 'nullable|string',
            'est_actif'           => 'nullable|boolean',
        ]);

        $service->update($validated + ['est_actif' => $request->boolean('est_actif', true)]);

        return back()->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        if ($service->membres()->exists() || $service->sousServices()->exists()) {
            return back()->withErrors(['_error' => 'Service utilisé. Désactivez-le ou retirez membres/sous-services d\'abord.']);
        }
        $service->delete();
        return back()->with('success', 'Service supprimé.');
    }

    public function attacherMembre(Request $request, Service $service)
    {
        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'poste'          => 'nullable|string|max:150',
            'est_principal'  => 'nullable|boolean',
            'date_arrivee'   => 'nullable|date',
        ]);

        // Si est_principal, retirer le flag des autres services du user
        if ($request->boolean('est_principal')) {
            \DB::table('intranet_service_user')
                ->where('user_id', $validated['user_id'])
                ->update(['est_principal' => false]);
        }

        $service->membres()->syncWithoutDetaching([
            $validated['user_id'] => [
                'poste'         => $validated['poste'] ?? null,
                'est_principal' => $request->boolean('est_principal'),
                'date_arrivee'  => $validated['date_arrivee'] ?? now()->toDateString(),
            ],
        ]);

        return back()->with('success', 'Membre ajouté au service.');
    }

    public function detacherMembre(Service $service, User $user)
    {
        $service->membres()->detach($user->id);
        return back()->with('success', 'Membre retiré du service.');
    }
}
