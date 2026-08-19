<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Embauche;
use App\Models\Employee;
use App\Models\Postulant;
use App\Models\Profil;
use App\Models\Recrutement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PostulantController extends Controller
{
    public const STATUTS = [
        0 => 'Nouveau',
        1 => 'Entretien',
        2 => 'Retenu',
        3 => 'Rejeté',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'success',
        3 => 'danger',
    ];

    public function index(Request $request)
    {
        $postulants = Postulant::query()
            ->with(['recrutement', 'profil', 'embauche'])
            ->when($request->q, fn($q, $s) => $q->where(fn($w) =>
                $w->where('noms', 'ilike', "%$s%")
                  ->orWhere('prenoms', 'ilike', "%$s%")
                  ->orWhere('email', 'ilike', "%$s%")
            ))
            ->when($request->recrutement_id, fn($q, $id) => $q->where('recrutement_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $recrutements = Recrutement::ouvert()->orderBy('label')->get();

        return view('rh.postulants.index', [
            'postulants'    => $postulants,
            'recrutements'  => $recrutements,
            'statuts'       => self::STATUTS,
            'statutCouleurs' => self::STATUT_COULEURS,
        ]);
    }

    public function create(Request $request)
    {
        $recrutements = Recrutement::ouvert()->orderBy('label')->get();
        $recrutementId = $request->integer('recrutement') ?: null;
        $profils = $recrutementId
            ? Profil::where('recrutement_id', $recrutementId)->orderBy('label')->get()
            : collect();
        return view('rh.postulants.create', compact('recrutements', 'profils', 'recrutementId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'noms'           => 'required|string|max:255',
            'prenoms'        => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'contact'        => 'nullable|string|max:50',
            'age'            => 'nullable|string|max:10',
            'date_naissance' => 'nullable|string|max:30',
            'profil_id'      => 'required|exists:profils,id',
            'recrutement_id' => 'nullable|exists:recrutements,id',
            'statut'         => 'nullable|integer|in:0,1,2,3',
            'fichiersjoin'   => 'nullable|string',
        ]);

        // Récupère automatiquement le recrutement depuis le profil si non fourni
        if (empty($data['recrutement_id']) && !empty($data['profil_id'])) {
            $data['recrutement_id'] = Profil::find($data['profil_id'])?->recrutement_id;
        }
        $data['statut'] = $data['statut'] ?? 0;
        $data['age'] = $data['age'] ?? '';
        $data['date_naissance'] = $data['date_naissance'] ?? '';

        $postulant = Postulant::create($data);

        return redirect()->route('rh.postulants.show', $postulant)
            ->with('success', 'Candidature enregistrée.');
    }

    public function show(Postulant $postulant)
    {
        $postulant->load(['recrutement', 'profil', 'embauche.employee']);
        return view('rh.postulants.show', [
            'postulant'      => $postulant,
            'statuts'        => self::STATUTS,
            'statutCouleurs' => self::STATUT_COULEURS,
        ]);
    }

    public function edit(Postulant $postulant)
    {
        $recrutements = Recrutement::orderBy('label')->get();
        $profils = $postulant->recrutement_id
            ? Profil::where('recrutement_id', $postulant->recrutement_id)->orderBy('label')->get()
            : Profil::orderBy('label')->get();
        return view('rh.postulants.edit', compact('postulant', 'recrutements', 'profils'));
    }

    public function update(Request $request, Postulant $postulant)
    {
        $data = $request->validate([
            'noms'           => 'required|string|max:255',
            'prenoms'        => 'required|string|max:255',
            'email'          => 'required|email|max:255',
            'contact'        => 'nullable|string|max:50',
            'age'            => 'nullable|string|max:10',
            'date_naissance' => 'nullable|string|max:30',
            'profil_id'      => 'required|exists:profils,id',
            'recrutement_id' => 'nullable|exists:recrutements,id',
            'statut'         => 'nullable|integer|in:0,1,2,3',
            'fichiersjoin'   => 'nullable|string',
        ]);
        $data['age'] = $data['age'] ?? '';
        $data['date_naissance'] = $data['date_naissance'] ?? '';
        $postulant->update($data);

        return redirect()->route('rh.postulants.show', $postulant)
            ->with('success', 'Candidature mise à jour.');
    }

    public function destroy(Postulant $postulant)
    {
        if ($postulant->embauche) {
            return back()->with('error', 'Impossible de supprimer : ce candidat a déjà été embauché.');
        }
        $postulant->delete();
        return redirect()->route('rh.postulants.index')->with('success', 'Candidature supprimée.');
    }

    /**
     * Change le statut (workflow : nouveau → entretien → retenu / rejeté).
     */
    public function changerStatut(Request $request, Postulant $postulant)
    {
        $data = $request->validate([
            'statut' => 'required|integer|in:0,1,2,3',
        ]);
        $postulant->update(['statut' => $data['statut']]);
        return back()->with('success', 'Statut mis à jour : ' . (self::STATUTS[$data['statut']] ?? '—'));
    }

    /**
     * Convertit un postulant retenu en employé (création Employee + User + Embauche).
     */
    public function embaucher(Request $request, Postulant $postulant)
    {
        if ($postulant->statut !== 2) {
            return back()->with('error', 'Le candidat doit être au statut « Retenu » pour être embauché.');
        }
        if ($postulant->embauche) {
            return back()->with('error', 'Ce candidat a déjà été embauché (embauche #' . $postulant->embauche->id . ').');
        }

        $data = $request->validate([
            'matricule'      => 'required|string|max:50|unique:employees,matricule',
            'date_embauche'  => 'required|date',
            'type_contrat'   => 'required|string|max:50',
            'poste'          => 'nullable|string|max:255',
            'departement'    => 'nullable|string|max:255',
            'salaire_base'   => 'required|numeric|min:0',
            'commentaire'    => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($postulant, $data) {
            // Compte utilisateur — mot de passe aléatoire, must_change_password
            $user = User::create([
                'name'                 => $postulant->noms,
                'prenoms'              => $postulant->prenoms,
                'email'                => $postulant->email,
                'contact'              => $postulant->contact ?? '',
                'matricule'            => $data['matricule'],
                'poste'                => $data['poste'] ?? null,
                'password'             => Hash::make(Str::random(32)),
                'must_change_password' => true,
                'statut'               => 1,
            ]);
            $user->assignRole('user');

            // Fiche employé
            $employee = Employee::create([
                'noms'          => $postulant->noms,
                'prenoms'       => $postulant->prenoms,
                'email'         => $postulant->email,
                'contact'       => $postulant->contact ?? '',
                'matricule'     => $data['matricule'],
                'date_embauche' => $data['date_embauche'],
                'type_contrat'  => $data['type_contrat'],
                'poste'         => $data['poste'] ?? null,
                'departement'   => $data['departement'] ?? null,
                'salaire_base'  => $data['salaire_base'],
                'user_id'       => $user->id,
                'statut'        => 1,
            ]);

            // Embauche (trace du lien postulant → employee)
            Embauche::create([
                'label'         => 'Embauche de ' . $postulant->noms . ' ' . $postulant->prenoms,
                'description'   => $data['commentaire'] ?? null,
                'valider'       => true,
                'postulant_id'  => $postulant->id,
                'employee_id'   => $employee->id,
                'statut'        => 1,
            ]);

            // Audit
            activity('embauche')
                ->causedBy(auth()->user())
                ->performedOn($employee)
                ->withProperties([
                    'postulant_id' => $postulant->id,
                    'matricule'    => $employee->matricule,
                ])
                ->log("Embauche depuis candidature #{$postulant->id}");
        });

        return redirect()->route('rh.postulants.show', $postulant)
            ->with('success', 'Candidat embauché : fiche employé et compte utilisateur créés. '
                . 'L\'employé devra définir son mot de passe à la première connexion.');
    }

    /**
     * Endpoint AJAX : retourne les profils d'un recrutement donné.
     */
    public function profilsParRecrutement(Recrutement $recrutement)
    {
        return response()->json(
            Profil::where('recrutement_id', $recrutement->id)
                ->where('statut', 1)
                ->orderBy('label')
                ->get(['id', 'label'])
        );
    }
}
