<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->with(['user'])
            ->when($request->search, fn($q, $s) => $q->where(fn($w) => $w->where('noms', 'ilike', "%{$s}%")->orWhere('prenoms', 'ilike', "%{$s}%")->orWhere('matricule', 'ilike', "%{$s}%")->orWhere('nip', 'ilike', "%{$s}%")))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->when($request->departement, fn($q, $d) => $q->where('departement', $d))
            ->orderBy('noms')
            ->paginate(15);

        return view('rh.employees.index', compact('employees'));
    }

    public function create()
    {
        $organisations = Organisation::all();
        $roles = Role::orderBy('name')->get();

        return view('rh.employees.create', compact('organisations', 'roles'));
    }

    public function store(Request $request)
    {
        if ($request->filled('nip')) {
            $request->merge(['nip' => strtoupper(trim($request->input('nip')))]);
        }

        $validated = $request->validate([
            'noms' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:255|unique:employees,matricule',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:255',
            'sexe' => 'nullable|string|max:1',
            'situation_matrimoniale' => 'nullable|string|max:255',
            'nombre_enfants' => 'nullable|integer|min:0',
            'email' => 'required|email|max:255|unique:users,email',
            'contact' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            // Localisation administrative
            'pays'                  => 'nullable|string|max:100',
            'province'              => 'nullable|string|max:100',
            'departement_geo'       => 'nullable|string|max:100',
            'prefecture'            => 'nullable|string|max:100',
            'sous_prefecture'       => 'nullable|string|max:100',
            'zone_type'             => 'nullable|in:urbaine,rurale',
            'commune'               => 'nullable|string|max:100',
            'arrondissement'        => 'nullable|string|max:100',
            'quartier_loc'          => 'nullable|string|max:100',
            'canton'                => 'nullable|string|max:100',
            'regroupement_village'  => 'nullable|string|max:100',
            'village'               => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:255',
            'date_fin_contrat' => 'nullable|date',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            // Supérieur hiérarchique : lien par POSTE (recommandé) — la personne legacy reste tolérée
            'superieur_poste_id' => 'nullable|exists:postes,id',
            'superieur_hierarchique' => 'nullable|exists:employees,id',
            'salaire_base' => 'nullable|numeric|min:0',
            'iban' => 'nullable|string|max:255',
            'numero_secu' => 'nullable|string|max:255',
            'nip' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]{2}-[A-Z0-9]{4}-\d{8}$/', 'unique:employees,nip'],
            // Compte utilisateur
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['nullable', Rule::in(Role::pluck('name')->all())],
            'photo'    => 'nullable|image|max:5120', // 5 Mo
        ], [
            'email.required'    => 'L\'email est obligatoire (sera utilisé comme identifiant de connexion).',
            'email.unique'      => 'Cet email est déjà utilisé par un autre compte utilisateur.',
            'password.required' => 'Le mot de passe initial est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'=> 'La confirmation du mot de passe ne correspond pas.',
            'nip.regex'         => 'Le NIP doit respecter le format XX-XXXX-AAAAMMJJ (ex : A1-2345-19901225).',
            'nip.unique'        => 'Ce NIP est déjà attribué à un autre employé.',
            'photo.image'       => 'Le fichier doit être une image (jpg, png, gif, svg).',
            'photo.max'         => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // 1. Photo (optionnelle)
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('avatars', 'public');
            }

            // 2. Compte utilisateur
            $user = User::create([
                'name'                => $validated['noms'],
                'prenoms'             => $validated['prenoms'],
                'email'               => $validated['email'],
                'password'            => Hash::make($validated['password']),
                'contact'             => $validated['contact'] ?? '',
                'matricule'           => $validated['matricule'] ?? null,
                'poste'               => $validated['poste'] ?? null,
                'date_naissance'      => $validated['date_naissance'] ?? null,
                'date_embauche'       => $validated['date_embauche'] ?? null,
                'profile_photo_path'  => $photoPath,
                'statut'              => 1,
            ]);
            $user->assignRole($request->input('role') ?: 'user');

            // 3. Employé lié au user
            Employee::create(array_merge(
                array_diff_key($validated, array_flip(['password', 'password_confirmation', 'role', 'photo'])),
                ['user_id' => $user->id]
            ));
        });

        return redirect()->route('rh.employees.index')->with('success', 'Employe et compte utilisateur crees avec succes.');
    }

    public function show(string $id)
    {
        $employee = Employee::with([
            'user', 'superieur', 'affilies', 'absences',
            'competences', 'qualifications', 'formations',
            'evenementsCarriere',
        ])->findOrFail($id);

        return view('rh.employees.show', compact('employee'));
    }

    public function edit(string $id)
    {
        $employee = Employee::with('user')->findOrFail($id);
        $organisations = Organisation::all();
        $roles = Role::orderBy('name')->get();

        return view('rh.employees.edit', compact('employee', 'organisations', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        if ($request->filled('nip')) {
            $request->merge(['nip' => strtoupper(trim($request->input('nip')))]);
        }

        $userId = $employee->user_id;

        $validated = $request->validate([
            'noms' => 'sometimes|required|string|max:255',
            'prenoms' => 'sometimes|required|string|max:255',
            'matricule' => 'nullable|string|max:255|unique:employees,matricule,' . $id,
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:255',
            'sexe' => 'nullable|string|max:1',
            'situation_matrimoniale' => 'nullable|string|max:255',
            'nombre_enfants' => 'nullable|integer|min:0',
            'email' => ['required', 'email', 'max:255', $userId ? "unique:users,email,{$userId}" : 'unique:users,email'],
            'contact' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            // Localisation administrative
            'pays'                  => 'nullable|string|max:100',
            'province'              => 'nullable|string|max:100',
            'departement_geo'       => 'nullable|string|max:100',
            'prefecture'            => 'nullable|string|max:100',
            'sous_prefecture'       => 'nullable|string|max:100',
            'zone_type'             => 'nullable|in:urbaine,rurale',
            'commune'               => 'nullable|string|max:100',
            'arrondissement'        => 'nullable|string|max:100',
            'quartier_loc'          => 'nullable|string|max:100',
            'canton'                => 'nullable|string|max:100',
            'regroupement_village'  => 'nullable|string|max:100',
            'village'               => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:255',
            'poste' => 'nullable|string|max:255',
            'departement' => 'nullable|string|max:255',
            'superieur_poste_id' => 'nullable|exists:postes,id',
            'superieur_hierarchique' => 'nullable|exists:employees,id',
            'salaire_base' => 'nullable|numeric|min:0',
            'iban' => 'nullable|string|max:255',
            'numero_secu' => 'nullable|string|max:255',
            'nip' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]{2}-[A-Z0-9]{4}-\d{8}$/', 'unique:employees,nip,' . $id],
            'statut' => 'nullable|integer|in:1,2,3',
            // Compte utilisateur — le mot de passe n'est PAS modifiable ici
            // (procédure dédiée /rh/employees/{id}/reset-password)
            'role'     => ['nullable', Rule::in(Role::pluck('name')->all())],
            'photo'    => 'nullable|image|max:5120',
        ], [
            'email.required'    => 'L\'email est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé par un autre compte utilisateur.',
            'nip.regex'         => 'Le NIP doit respecter le format XX-XXXX-AAAAMMJJ.',
            'nip.unique'        => 'Ce NIP est déjà attribué à un autre employé.',
            'photo.image'       => 'Le fichier doit être une image.',
            'photo.max'         => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        // Garde-fou : si jamais un password est posté (formulaire forgé), on l'ignore explicitement
        $request->request->remove('password');
        $request->request->remove('password_confirmation');

        DB::transaction(function () use ($request, $validated, $employee) {
            // Sync user (création si manquant — legacy data)
            $userData = [
                'name'    => $validated['noms'] ?? $employee->noms,
                'prenoms' => $validated['prenoms'] ?? $employee->prenoms,
                'email'   => $validated['email'],
                'contact' => $validated['contact'] ?? $employee->contact ?? '',
                'matricule' => $validated['matricule'] ?? $employee->matricule,
                'poste'   => $validated['poste'] ?? $employee->poste,
            ];
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne si présente
                if ($employee->user?->profile_photo_path) {
                    Storage::disk('public')->delete($employee->user->profile_photo_path);
                }
                $userData['profile_photo_path'] = $request->file('photo')->store('avatars', 'public');
            }

            if ($employee->user) {
                $employee->user->update($userData);
                if ($request->filled('role')) {
                    $employee->user->syncRoles([$request->input('role')]);
                }
            } else {
                // Création d'un user manquant : on génère un mot de passe aléatoire jamais affiché.
                // L'employé devra utiliser la procédure de réinitialisation (lien email ou reset admin).
                $user = User::create(array_merge($userData, [
                    'password'             => Hash::make(\Illuminate\Support\Str::random(32)),
                    'statut'               => 1,
                    'must_change_password' => true,
                ]));
                $user->assignRole($request->input('role') ?: 'user');
                $employee->user_id = $user->id;
            }

            // Update employee
            $employee->fill(array_diff_key($validated, array_flip(['role', 'photo'])));
            $employee->save();
        });

        return redirect()->route('rh.employees.index')->with('success', 'Employe et compte utilisateur mis a jour.');
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('rh.employees.index')->with('success', 'Employe supprime avec succes.');
    }
}
