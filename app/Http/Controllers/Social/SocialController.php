<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use App\Models\Employee;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\PieceJointe;
use App\Models\Intranet\Tache;
use App\Models\Social\SocialGroup;
use App\Models\Social\SocialGroupMember;
use App\Models\Intranet\Service;
use App\Models\Organisation;
use App\Models\Social\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    public function dashboard(Request $request)
    {
        $filter = $request->query('filter', 'feed'); // feed | mine | liked
        $user = $request->user();

        $query = Post::with(['auteur', 'commentaires.auteur', 'likes', 'piecesJointes', 'publication.cibles'])
            ->withCount(['commentaires', 'likes'])
            ->latest();

        if ($filter === 'mine' && $user) {
            $query->where('created_by', $user->id);
        } elseif ($filter === 'liked' && $user) {
            $query->whereHas('likes', fn($q) => $q->where('user_id', $user->id));
        } else {
            // Feed principal : publications visibles = public OU auteur = user
            // OU publication privée ciblant le user (direct / groupe / entité)
            $groupeIds = $user?->groupesIntranet?->pluck('id') ?? collect();
            $orgId = $user?->organisation_id;
            $query->where(function ($q) use ($user, $groupeIds, $orgId) {
                $q->whereHas('publication', fn($p) => $p->where('visibilite', 'public'));
                if ($user) {
                    $q->orWhere('created_by', $user->id);
                    $q->orWhereHas('publication', function ($p) use ($user, $groupeIds, $orgId) {
                        $p->where('visibilite', 'prive')->whereHas('cibles', function ($c) use ($user, $groupeIds, $orgId) {
                            $c->where(function ($cw) use ($user, $groupeIds, $orgId) {
                                $cw->where(fn($x) => $x->where('cible_type', 'user')->where('cible_id', $user->id));
                                if ($groupeIds->isNotEmpty()) {
                                    $cw->orWhere(fn($x) => $x->where('cible_type', 'groupe')->whereIn('cible_id', $groupeIds));
                                }
                                if ($orgId) {
                                    $cw->orWhere(fn($x) => $x->where('cible_type', 'entite')->where('cible_id', $orgId));
                                }
                            });
                        });
                    });
                }
            });
        }

        $posts = $query->paginate(15);

        // Agenda : 3 prochains événements
        $prochainsEvenements = Evenement::with('type')
            ->aVenir()
            ->orderBy('date_debut')
            ->take(3)
            ->get();

        // ── Anniversaires (7 prochains jours) ────────────────────────
        $today = Carbon::today();
        $in7 = Carbon::today()->addDays(7)->endOfDay();
        $anniversaires = Employee::with('user')
            ->where('statut', 1)
            ->whereNotNull('date_naissance')
            ->get()
            ->filter(function ($e) use ($today, $in7) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->between($today, $in7);
            })
            ->sortBy(function ($e) use ($today) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->timestamp;
            })
            ->take(8)
            ->values();

        $wishesData = [];
        $wishesDejaEnvoyes = collect();
        if ($anniversaires->isNotEmpty()) {
            $dates = $anniversaires->map(function ($e) use ($today) {
                $b = $e->date_naissance->copy()->year($today->year);
                if ($b->lt($today)) $b->addYear();
                return $b->toDateString();
            })->unique();
            $wishesData = BirthdayWish::whereIn('employee_id', $anniversaires->pluck('id'))
                ->whereIn('date_anniversaire', $dates)
                ->selectRaw('employee_id, count(*) as total')
                ->groupBy('employee_id')
                ->pluck('total', 'employee_id')
                ->toArray();
            if ($user) {
                $wishesDejaEnvoyes = BirthdayWish::where('expediteur_id', $user->id)
                    ->whereIn('employee_id', $anniversaires->pluck('id'))
                    ->whereIn('date_anniversaire', $dates)
                    ->pluck('employee_id');
            }
        }


        // ── Activité des groupes de discussion (mes groupes récents) ─
        $groupesActifs = collect();
        if ($user) {
            $memberships = SocialGroupMember::where('user_id', $user->id)->get()->keyBy('group_id');
            $groupesActifs = SocialGroup::whereIn('id', $memberships->keys())
                ->with(['messages' => fn($q) => $q->latest()->limit(1)->with('sender')])
                ->withCount('messages')
                ->latest('updated_at')
                ->take(5)
                ->get()
                ->map(function ($g) use ($memberships) {
                    $lastMsg = $g->messages->first();
                    $lastReadAt = $memberships[$g->id]->last_read_at ?? null;
                    $g->last_message = $lastMsg;
                    $g->unread_count = ($lastMsg && (!$lastReadAt || $lastMsg->created_at->gt($lastReadAt)))
                        ? $g->messages()->where('created_at', '>', $lastReadAt ?? '1970-01-01')->count()
                        : 0;
                    return $g;
                });
        }

        // ── Mes 5 prochaines tâches à faire ─────────────────────────
        $mesTaches = collect();
        if ($user) {
            $mesTaches = Tache::with(['projet', 'priorite', 'statut'])
                ->assigneesA($user->id)
                ->whereHas('statut', fn($q) => $q->whereNotIn('libelle', ['Terminé', 'Terminée', 'Annulé', 'Annulée']))
                ->orderByRaw('date_fin ASC NULLS LAST')
                ->orderByDesc('id')
                ->take(5)
                ->get();
        }

        // Options pour le sélecteur de cibles
        $usersDispo    = User::where('id', '!=', $user?->id)->orderBy('name')->get(['id', 'prenoms', 'name']);
        $groupesDispo  = Groupe::orderBy('nom')->get(['id', 'nom']);
        $entitesDispo  = Organisation::orderBy('label')->get(['id', 'label']);

        return view('social.dashboard', compact(
            'posts', 'filter',
            'prochainsEvenements',
            'anniversaires', 'wishesData', 'wishesDejaEnvoyes',
            'mesTaches', 'groupesActifs',
            'usersDispo', 'groupesDispo', 'entitesDispo',
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validatePost($request);
        $contenuSanitize = $this->sanitizeHtml($data['contenu']);
        if (trim(strip_tags($contenuSanitize)) === '') {
            return back()->withErrors(['contenu' => 'Le contenu ne peut pas être vide.'])->withInput();
        }

        $post = Post::create([
            'created_by' => $request->user()->id,
            'contenu'    => $contenuSanitize,
        ]);

        // Publier avec visibilité + cibles éventuelles
        $visibilite = $data['visibilite'] === 'prive' ? 'prive' : 'public';
        $cibles = $visibilite === 'prive' ? $this->parseCibles($request->input('cibles', [])) : [];
        $post->publier($visibilite, $cibles);

        // Multi-médias via HasPiecesJointes (pas de limite hard-coded, respect max 20 Mo par fichier)
        if ($request->hasFile('medias')) {
            $post->attacherFichiers($request->file('medias'), 'social/posts');
        }

        return redirect()->route('social.dashboard')->with('success', 'Publication ajoutée.');
    }

    public function update(Request $request, Post $post)
    {
        // Seul l'auteur peut modifier, dans les 8h suivant la création (super-admin bypass)
        $user = $request->user();
        $isAdmin = $user->hasRole('super-admin');
        if ($post->created_by !== $user->id && !$isAdmin) abort(403);
        if (!$isAdmin && $post->created_at->lt(now()->subHours(8))) {
            return back()->with('error', 'Cette publication ne peut plus être modifiée (délai de 8h dépassé).');
        }

        $data = $this->validatePost($request);
        $contenuSanitize = $this->sanitizeHtml($data['contenu']);
        if (trim(strip_tags($contenuSanitize)) === '') {
            return back()->withErrors(['contenu' => 'Le contenu ne peut pas être vide.'])->withInput();
        }

        $post->update(['contenu' => $contenuSanitize]);

        $visibilite = $data['visibilite'] === 'prive' ? 'prive' : 'public';
        $cibles = $visibilite === 'prive' ? $this->parseCibles($request->input('cibles', [])) : [];
        $post->publier($visibilite, $cibles);

        if ($request->hasFile('medias')) {
            $post->attacherFichiers($request->file('medias'), 'social/posts');
        }

        return redirect()->route('social.dashboard')->with('success', 'Publication modifiée.');
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'contenu'    => 'required|string|max:20000',
            'visibilite' => 'nullable|in:public,prive',
            'cibles'     => 'nullable|array',
            'cibles.*'   => ['string', 'regex:/^(user|groupe|entite):\d+$/'],
            'medias'     => 'nullable|array|max:10',
            'medias.*'   => ['file', 'mimes:jpeg,jpg,png,webp,gif,mp4,mov,webm', 'max:20480'],
        ]) + ['visibilite' => $request->input('visibilite', 'public')];
    }

    private function parseCibles(array $raw): array
    {
        $out = [];
        foreach ($raw as $item) {
            if (!is_string($item) || !preg_match('/^(user|groupe|entite):(\d+)$/', $item, $m)) continue;
            $out[] = ['type' => $m[1], 'id' => (int) $m[2]];
        }
        return $out;
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->created_by !== $request->user()->id && !$request->user()->hasRole('super-admin')) {
            abort(403);
        }
        $post->delete();
        return back()->with('success', 'Publication supprimée.');
    }

    /**
     * Compteurs pour les FABs (notifications + messages groupes non lus).
     */
    public function badges(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['social' => 0, 'groups' => 0]);

        // Social : notifications Laravel non lues (toutes sources)
        $socialCount = \DB::table('notifications')
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Groupes : total messages non lus dans les groupes dont je suis membre
        // (exclut mes propres messages)
        $groupsCount = \DB::table('social_group_messages')
            ->join('social_group_members', function ($j) use ($user) {
                $j->on('social_group_messages.group_id', '=', 'social_group_members.group_id')
                  ->where('social_group_members.user_id', $user->id);
            })
            ->whereNull('social_group_messages.deleted_at')
            ->where('social_group_messages.sender_id', '!=', $user->id)
            ->whereRaw("social_group_messages.created_at > COALESCE(social_group_members.last_read_at, '1970-01-01')")
            ->count();

        return response()->json(['social' => $socialCount, 'groups' => $groupsCount]);
    }

    /**
     * Affichage d'une publication seule (ouverte depuis un partage chat).
     * Renvoie 403 si le user ne peut pas la voir.
     */
    public function showPost(Request $request, Post $post)
    {
        abort_unless($this->userCanView($post, $request->user()), 403);
        $post->load(['auteur', 'commentaires.auteur', 'likes', 'piecesJointes', 'publication']);
        $post->loadCount(['commentaires', 'likes']);
        return view('social.post', compact('post'));
    }

    public function toggleLike(Request $request, Post $post)
    {
        abort_unless($this->userCanView($post, $request->user()), 403);
        $liked = $post->toggleLike($request->user());
        return back()->with('success', $liked ? 'Aimé.' : 'Like retiré.');
    }

    public function comment(Request $request, Post $post)
    {
        abort_unless($this->userCanView($post, $request->user()), 403);
        $data = $request->validate([
            'contenu'   => 'required|string|max:1000',
            // Le parent doit exister ET appartenir à CE post (bloque le rattachement croisé)
            'parent_id' => [
                'nullable', 'integer',
                \Illuminate\Validation\Rule::exists('intranet_commentaires', 'id')
                    ->where('commentable_type', Post::class)
                    ->where('commentable_id', $post->id),
            ],
        ]);
        $post->commenter($data['contenu'], $data['parent_id'] ?? null, $request->user());
        return back()->with('success', 'Commentaire ajouté.');
    }

    /**
     * Like / unlike d'un média (photo/vidéo) attaché à un post.
     * Endpoint JSON pour la lightbox — pas de redirect.
     */
    public function toggleMediaLike(Request $request, PieceJointe $media)
    {
        abort_unless($this->canInteractWithMedia($media, $request->user()), 403);
        $liked = $media->toggleLike($request->user());
        return response()->json([
            'liked' => $liked,
            'total' => $media->totalLikes(),
        ]);
    }

    /**
     * Commentaire sur un média. Retourne le commentaire créé pour affichage instantané.
     */
    public function commentMedia(Request $request, PieceJointe $media)
    {
        abort_unless($this->canInteractWithMedia($media, $request->user()), 403);
        $data = $request->validate(['contenu' => 'required|string|max:1000']);
        $com = $media->commenter($data['contenu'], null, $request->user());
        $u = $request->user();
        return response()->json([
            'ok' => true,
            'total' => $media->totalCommentaires(),
            'commentaire' => [
                'auteur'  => trim(($u->prenoms ?? '').' '.$u->name),
                'contenu' => e($com->contenu), // pré-échappé pour injection sûre côté client
                'date'    => $com->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Vérifie qu'un média est attaché à un Post ET que l'utilisateur peut voir ce post.
     * Bloque IDOR : un user ne peut pas liker un média d'une image non-post ni d'un post caché.
     */
    private function canInteractWithMedia(PieceJointe $media, $user): bool
    {
        if ($media->attachable_type !== Post::class) return false;
        $post = Post::find($media->attachable_id);
        return $post && $this->userCanView($post, $user);
    }

    /**
     * Sanitise le HTML produit par Quill : allow-list stricte de tags/attributs.
     * Interdit toute source de XSS (scripts, event handlers, URLs javascript:/data:,
     * style inline).
     */
    private function sanitizeHtml(string $html): string
    {
        // ── Passe 0 : décodage des entités HTML pour éviter les bypass ────
        // (ex: `&#106;avascript:` → `javascript:`). On travaille sur du texte
        // décodé pour que les checks regex opèrent sur ce que le navigateur
        // verra réellement, puis on rééchappe les valeurs conservées.
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // ── Passe 1 : coupe-circuit — rejet total de patterns notoirement dangereux ──
        // Même après strip_tags, on garantit qu'aucun de ces motifs ne peut
        // survivre dans le résultat final (défense en profondeur).
        $blocklist = [
            '/<script\b/i', '/<\/script>/i',
            '/<iframe\b/i', '/<object\b/i', '/<embed\b/i',
            '/<svg\b/i', '/<math\b/i',
            '/<meta\b/i', '/<link\b/i', '/<base\b/i',
            '/<form\b/i', '/<input\b/i', '/<button\b/i', '/<textarea\b/i',
            '/on[a-z]+\s*=/i',                    // onclick, onerror, onmouseover, …
            '/(?:javascript|vbscript|data)\s*:/i', // schémas URL malveillants (même dans du texte)
            '/expression\s*\(/i',                 // CSS expression() (IE legacy)
        ];
        foreach ($blocklist as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }

        // ── Passe 2 : allow-list stricte de tags (correspond à la toolbar Quill) ──
        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><a><span>';
        $html = strip_tags($html, $allowed);

        // ── Passe 3 : purge des attributs — seul href sur <a> peut subsister ──
        $html = preg_replace_callback('/<([a-z0-9]+)([^>]*)>/i', function ($m) {
            $tag  = strtolower($m[1]);
            $attrs = $m[2];
            $keep = '';
            if ($tag === 'a' && preg_match('/\shref\s*=\s*(["\'])(.*?)\1/i', $attrs, $h)) {
                $url = trim($h[2]);
                // Whitelist stricte de schémas (les schémas dangereux ont déjà été purgés
                // en Passe 1, ce check est un filet de sécurité supplémentaire)
                if (preg_match('#^(https?://[^\s"\'<>]+|mailto:[^\s"\'<>]+|tel:[^\s"\'<>]+|#[^\s"\'<>]*)$#i', $url)) {
                    $keep = ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" rel="noopener noreferrer nofollow" target="_blank"';
                }
            }
            return "<{$tag}{$keep}>";
        }, $html);

        return $html;
    }

    /**
     * Un utilisateur peut interagir avec un post s'il en est l'auteur OU si la publication
     * associée est en visibilité "public". Miroir de la logique du feed principal.
     * Super-admin bypass.
     */
    private function userCanView(Post $post, $user): bool
    {
        if (!$user) return false;
        if ($user->hasRole('super-admin')) return true;
        if ((int) $post->created_by === (int) $user->id) return true;
        $pub = $post->publication;
        if (!$pub) return false;
        if ($pub->visibilite === 'brouillon') return false;
        if ($pub->visibilite === 'public') return true;
        // Privé : délègue au trait qui vérifie user/groupe/entité
        return $pub->estVisible($user);
    }
}
