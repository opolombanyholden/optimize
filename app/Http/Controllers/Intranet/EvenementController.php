<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intranet\EvenementRequest;
use App\Models\Intranet\Contact;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\EvenementInviteExterne;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\TypeEvenement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EvenementController extends Controller
{
    private const FOLDER = 'intranet/evenements';

    // ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $evenements = Evenement::with(['auteur', 'type', 'publication'])
            ->withCount(['participants', 'likes', 'commentaires'])
            ->recherche($request->input('q'))
            ->when($request->filled('type'), fn($q) => $q->where('type_evenement_id', $request->type))
            ->when($request->filled('statut'), function ($q) use ($request) {
                if ($request->statut === 'avenir') $q->aVenir();
                if ($request->statut === 'passes') $q->passes();
            })
            ->orderByDesc('date_debut')
            ->paginate(12)
            ->withQueryString();

        $types = TypeEvenement::orderBy('nom')->get();

        return view('intranet.evenements.index', compact('evenements', 'types'));
    }

    // ──────────────────────────────────────────────────────
    public function calendrier()
    {
        return view('intranet.evenements.calendrier', [
            'types' => TypeEvenement::orderBy('nom')->get(),
        ]);
    }

    /**
     * API JSON pour FullCalendar.
     */
    public function feed(Request $request)
    {
        $start = $request->input('start');
        $end   = $request->input('end');

        $events = Evenement::with('type')
            ->when($start && $end, fn($q) => $q->entreDates($start, $end))
            ->get()
            ->map(function (Evenement $e) {
                return [
                    'id'              => $e->id,
                    'title'           => $e->titre,
                    'start'           => $e->date_debut->toIso8601String(),
                    'end'             => $e->date_fin->toIso8601String(),
                    'allDay'          => (bool) $e->journee_entiere,
                    'backgroundColor' => $e->couleur_affichee,
                    'borderColor'     => $e->couleur_affichee,
                    'url'             => route('intranet.evenements.show', $e),
                    'extendedProps'   => [
                        'lieu'      => $e->lieu,
                        'est_visio' => $e->est_visio,
                        'type'      => $e->type?->nom,
                        'extrait'   => $e->extrait,
                    ],
                ];
            });

        return response()->json($events);
    }

    // ──────────────────────────────────────────────────────
    public function create(\Illuminate\Http\Request $request)
    {
        // Pré-remplissage depuis le calendrier (date_debut, heure_debut, date_fin)
        $dateDebut = null;
        $dateFin   = null;
        if ($request->filled('date_debut')) {
            $heure = $request->input('heure_debut', '09:00');
            try { $dateDebut = \Carbon\Carbon::parse($request->input('date_debut') . ' ' . $heure); } catch (\Throwable $e) {}
        }
        if ($request->filled('date_fin')) {
            try { $dateFin = \Carbon\Carbon::parse($request->input('date_fin') . ' 18:00'); } catch (\Throwable $e) {}
        } elseif ($dateDebut) {
            $dateFin = $dateDebut->copy()->addHour();
        }

        return view('intranet.evenements.create', [
            'evenement' => new Evenement([
                'couleur'    => null,
                'statut'     => 'prevu',
                'date_debut' => $dateDebut,
                'date_fin'   => $dateFin,
            ]),
            'types'     => TypeEvenement::orderBy('nom')->get(),
            'users'     => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'   => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'participantsIds'      => [],
            'invitesExternesEmails'=> [],
        ]);
    }

    // ──────────────────────────────────────────────────────
    public function store(EvenementRequest $request)
    {
        $evenement = DB::transaction(function () use ($request) {
            $data = $request->safe()->only([
                'titre', 'extrait', 'description',
                'date_debut', 'date_fin', 'journee_entiere',
                'lieu', 'lieu_url', 'est_visio', 'lien_visio',
                'capacite_max', 'inscription_requise', 'rappel_minutes',
                'couleur', 'statut', 'type_evenement_id',
                'recurrence', 'recurrence_jusqu_au',
            ]);
            $data['created_by'] = auth()->id();
            $data['is_public']  = $request->input('visibilite') === 'public';

            if ($request->hasFile('media_principal')) {
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $evenement = Evenement::create($data);

            if ($request->hasFile('pieces_jointes')) {
                $evenement->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $evenement->id);
            }

            // Participants invités
            if ($request->filled('participants')) {
                $sync = collect($request->input('participants'))
                    ->mapWithKeys(fn($id) => [(int) $id => ['statut' => 'invite']])
                    ->all();
                $evenement->participants()->sync($sync);
            }

            // Invités externes (emails ou contacts CRM)
            $this->synchroniserInvitesExternes($evenement, $request);

            // Publication / cibles
            $this->synchroniserPublication($evenement, $request);

            // Génération des occurrences récurrentes
            if ($evenement->recurrence !== 'aucune' && $evenement->recurrence_jusqu_au) {
                $evenement->genererOccurrences();
            }

            return $evenement;
        });

        return redirect()
            ->route('intranet.evenements.show', $evenement)
            ->with('success', 'Événement créé avec succès.');
    }

    // ──────────────────────────────────────────────────────
    public function show(Evenement $evenement)
    {
        $evenement->load([
            'auteur', 'type', 'publication.cibles', 'piecesJointes',
            'participants', 'invitesExternes.contact', 'commentaires.user',
        ]);

        if (auth()->check()) {
            $evenement->enregistrerVue();
            $evenement->increment('vues_count');
        }

        // Statut RSVP de l'utilisateur courant
        $monStatut = $evenement->participants->firstWhere('id', auth()->id())?->pivot->statut;

        return view('intranet.evenements.show', compact('evenement', 'monStatut'));
    }

    // ──────────────────────────────────────────────────────
    public function edit(Evenement $evenement)
    {
        $evenement->load(['publication.cibles', 'piecesJointes', 'participants', 'invitesExternes']);

        $ciblesUsers   = $evenement->publication?->cibles->where('cible_type', 'user')->pluck('cible_id')->all() ?? [];
        $ciblesGroupes = $evenement->publication?->cibles->where('cible_type', 'groupe')->pluck('cible_id')->all() ?? [];

        return view('intranet.evenements.edit', [
            'evenement'      => $evenement,
            'types'          => TypeEvenement::orderBy('nom')->get(),
            'users'          => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email', 'email_interne']),
            'groupes'        => Groupe::orderBy('nom')->get(['id', 'nom', 'couleur']),
            'ciblesUsers'    => $ciblesUsers,
            'ciblesGroupes'  => $ciblesGroupes,
            'participantsIds'      => $evenement->participants->pluck('id')->all(),
            'invitesExternesEmails'=> $evenement->invitesExternes->pluck('email')->all(),
        ]);
    }

    // ──────────────────────────────────────────────────────
    public function update(EvenementRequest $request, Evenement $evenement)
    {
        DB::transaction(function () use ($request, $evenement) {
            $data = $request->safe()->only([
                'titre', 'extrait', 'description',
                'date_debut', 'date_fin', 'journee_entiere',
                'lieu', 'lieu_url', 'est_visio', 'lien_visio',
                'capacite_max', 'inscription_requise', 'rappel_minutes',
                'couleur', 'statut', 'type_evenement_id',
            ]);
            $data['is_public'] = $request->input('visibilite') === 'public';

            if ($request->hasFile('media_principal')) {
                if ($evenement->media_principal) {
                    Storage::disk('public')->delete($evenement->media_principal);
                }
                $file = $request->file('media_principal');
                $data['media_principal']      = $file->store(self::FOLDER, 'public');
                $data['media_principal_type'] = $this->categorieMedia($file->getMimeType());
            }

            $evenement->update($data);

            if ($request->hasFile('pieces_jointes')) {
                $evenement->attacherFichiers($request->file('pieces_jointes'), self::FOLDER . '/' . $evenement->id);
            }

            if ($request->has('participants')) {
                // Préserver les statuts existants si possible
                $existing = $evenement->participants->keyBy('id');
                $sync = collect($request->input('participants', []))
                    ->mapWithKeys(function ($id) use ($existing) {
                        return [(int) $id => [
                            'statut' => $existing->get($id)?->pivot->statut ?? 'invite',
                        ]];
                    })->all();
                $evenement->participants()->sync($sync);
            }

            $this->synchroniserInvitesExternes($evenement, $request);
            $this->synchroniserPublication($evenement, $request);
        });

        return redirect()
            ->route('intranet.evenements.show', $evenement)
            ->with('success', 'Événement mis à jour.');
    }

    // ──────────────────────────────────────────────────────
    public function destroy(Evenement $evenement)
    {
        $evenement->delete();
        return redirect()
            ->route('intranet.evenements.index')
            ->with('success', 'Événement supprimé.');
    }

    public function destroyPieceJointe(Evenement $evenement, PieceJointe $piece)
    {
        abort_unless($piece->attachable_id === $evenement->id && $piece->attachable_type === Evenement::class, 404);
        $evenement->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    /**
     * API recherche de contacts CRM (autocomplete pour invités externes).
     */
    public function searchContacts(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $contacts = Contact::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('email', 'like', "%{$q}%")
                      ->orWhere('nom', 'like', "%{$q}%")
                      ->orWhere('prenoms', 'like', "%{$q}%");
                });
            })
            ->orderBy('nom')
            ->limit(30)
            ->get(['id', 'nom', 'prenoms', 'email']);

        return response()->json($contacts->map(fn($c) => [
            'value' => $c->email,
            'text'  => trim(($c->prenoms ?? '') . ' ' . $c->nom),
            'email' => $c->email,
        ]));
    }

    /**
     * RSVP : utilisateur courant met à jour son statut.
     */
    public function rsvp(Request $request, Evenement $evenement)
    {
        $request->validate(['statut' => ['required', 'in:invite,confirme,decline,peut_etre']]);

        $evenement->participants()->syncWithoutDetaching([
            auth()->id() => [
                'statut'     => $request->input('statut'),
                'repondu_le' => now(),
            ],
        ]);

        return back()->with('success', 'Votre réponse a été enregistrée.');
    }

    // ══════════════════════════════════════════════════════
    private function categorieMedia(?string $mime): string
    {
        if (! $mime) return 'document';
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        return 'document';
    }

    private function synchroniserInvitesExternes(Evenement $evenement, EvenementRequest $request): void
    {
        if (! $request->has('invites_externes')) return;

        $emails = collect((array) $request->input('invites_externes'))
            ->map(fn($e) => trim(strtolower($e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        // Supprimer les invités externes qui ne sont plus dans la liste
        $evenement->invitesExternes()
            ->whereNotIn('email', $emails->all())
            ->delete();

        // Créer / mettre à jour
        foreach ($emails as $email) {
            $contact = Contact::where('email', $email)->first();

            EvenementInviteExterne::firstOrCreate(
                ['evenement_id' => $evenement->id, 'email' => $email],
                [
                    'contact_id' => $contact?->id,
                    'nom'        => $contact ? trim(($contact->prenoms ?? '') . ' ' . $contact->nom) : null,
                    'statut'     => 'invite',
                ]
            );
        }
    }

    private function synchroniserPublication(Evenement $evenement, EvenementRequest $request): void
    {
        $cibles = [];
        foreach ((array) $request->input('cibles_users', []) as $userId) {
            $cibles[] = ['type' => 'user', 'id' => (int) $userId];
        }
        foreach ((array) $request->input('cibles_groupes', []) as $groupeId) {
            $cibles[] = ['type' => 'groupe', 'id' => (int) $groupeId];
        }

        $evenement->publier(
            $request->input('visibilite', 'public'),
            $cibles,
            [
                'likes_actifs'        => $request->boolean('likes_actifs'),
                'commentaires_actifs' => $request->boolean('commentaires_actifs'),
                'publie_le'           => $request->input('publie_le') ?: now(),
                'expire_le'           => $request->input('expire_le'),
            ]
        );
    }
}
