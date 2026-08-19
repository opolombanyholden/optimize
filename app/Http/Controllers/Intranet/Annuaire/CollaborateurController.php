<?php

namespace App\Http\Controllers\Intranet\Annuaire;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Http\Request;

class CollaborateurController extends Controller
{
    public function index(Request $request)
    {
        $q          = $request->input('q');
        $serviceId  = $request->input('service');
        $typeEntite = $request->input('type_entite');
        $equipeId   = $request->input('equipe');
        $mode       = $request->input('mode');  // 'mes_entites' | 'mes_equipes' | null
        $vue        = $request->input('vue', 'cards');

        $monId = auth()->id();

        // IDs de mes services et mes équipes (pour filtres rapides)
        $mesServicesIds = collect();
        $mesEquipesIds  = collect();
        if ($monId) {
            $monUser = User::find($monId);
            $mesServicesIds = $monUser?->services()->pluck('intranet_services.id') ?? collect();
            $mesEquipesIds  = $monUser?->groupesIntranet()->pluck('intranet_groupes.id') ?? collect();
        }

        $users = User::actif()
            ->with(['services', 'roles', 'groupesIntranet'])
            ->when($q, fn($qb) => $qb->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('prenoms', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('matricule', 'like', "%{$q}%")
                  ->orWhere('poste', 'like', "%{$q}%");
            }))
            ->when($serviceId, fn($qb) => $qb->whereHas('services', fn($s) => $s->where('intranet_services.id', $serviceId)))
            ->when($typeEntite, fn($qb) => $qb->whereHas('services', fn($s) => $s->where('type_entite', $typeEntite)))
            ->when($equipeId, fn($qb) => $qb->whereHas('groupesIntranet', fn($g) => $g->where('intranet_groupes.id', $equipeId)))
            ->when($mode === 'mes_entites' && $mesServicesIds->isNotEmpty(),
                fn($qb) => $qb->whereHas('services', fn($s) => $s->whereIn('intranet_services.id', $mesServicesIds)))
            ->when($mode === 'mes_equipes' && $mesEquipesIds->isNotEmpty(),
                fn($qb) => $qb->whereHas('groupesIntranet', fn($g) => $g->whereIn('intranet_groupes.id', $mesEquipesIds)))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        $services = Service::actif()->orderBy('nom')->get();
        $equipes  = \App\Models\Intranet\Groupe::orderBy('nom')->get();

        $stats = [
            'total'           => User::actif()->count(),
            'avec_service'    => User::actif()->whereHas('services')->count(),
            'sans_service'    => User::actif()->whereDoesntHave('services')->count(),
            'services_actifs' => $services->count(),
            'mes_entites'     => $mesServicesIds->count(),
            'mes_equipes'     => $mesEquipesIds->count(),
        ];

        return view('intranet.annuaire.collaborateurs.index', compact(
            'users', 'services', 'equipes', 'stats', 'vue', 'mode', 'typeEntite'
        ));
    }

    public function show(User $collaborateur)
    {
        $collaborateur->load(['services', 'roles', 'employee', 'groupesIntranet']);

        return view('intranet.annuaire.collaborateurs.show', ['user' => $collaborateur]);
    }
}
