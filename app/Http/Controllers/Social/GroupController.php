<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Social\SocialGroup;
use App\Models\Social\SocialGroupMember;
use App\Models\Social\SocialGroupMessage;
use App\Models\User;
use App\Services\Social\ShareableResolver;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mesGroupes = SocialGroup::whereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->with(['auteur'])
            ->withCount(['members', 'messages'])
            ->latest('updated_at')
            ->get();
        $groupesPublics = SocialGroup::where('is_public', true)
            ->whereDoesntHave('members', fn($q) => $q->where('user_id', $user->id))
            ->withCount('members')
            ->latest()
            ->take(10)
            ->get();
        return view('social.groups.index', compact('mesGroupes', 'groupesPublics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public'   => 'nullable|boolean',
            'avatar'      => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
            'members'     => 'nullable|array',
            'members.*'   => 'integer|exists:users,id',
        ]);

        $user = $request->user();
        $groupData = [
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'is_public'   => (bool) ($data['is_public'] ?? false),
            'created_by'  => $user->id,
        ];
        if ($request->hasFile('avatar')) {
            $groupData['avatar'] = $request->file('avatar')->store('social/groups', 'public');
        }

        $group = SocialGroup::create($groupData);

        // Auteur = admin
        SocialGroupMember::create([
            'group_id' => $group->id, 'user_id' => $user->id, 'role' => 'admin',
        ]);
        // Membres invités
        foreach ($data['members'] ?? [] as $uid) {
            if ((int) $uid === (int) $user->id) continue;
            SocialGroupMember::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => (int) $uid],
                ['role' => 'member'],
            );
        }

        return redirect()->route('social.groups.show', $group)->with('success', 'Groupe créé.');
    }

    public function show(Request $request, SocialGroup $group)
    {
        $user = $request->user();
        abort_unless($group->estMembre($user) || $group->is_public, 403);
        // Auto-join si groupe public
        if (!$group->estMembre($user) && $group->is_public) {
            SocialGroupMember::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                ['role' => 'member'],
            );
        }

        $messages = $group->messages()->with('sender')->orderBy('created_at')->take(200)->get();
        $membres  = $group->users()->orderBy('name')->get();

        // Marque le dernier read pour cet utilisateur
        SocialGroupMember::where(['group_id' => $group->id, 'user_id' => $user->id])
            ->update(['last_read_at' => now()]);

        $usersDispo = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $membres->pluck('id'))
            ->orderBy('name')->get(['id', 'prenoms', 'name']);

        return view('social.groups.show', compact('group', 'messages', 'membres', 'usersDispo'));
    }

    public function postMessage(Request $request, SocialGroup $group)
    {
        $user = $request->user();
        abort_unless($group->estMembre($user), 403);

        $data = $request->validate([
            'contenu'      => 'nullable|string|max:2000',
            'shared_type'  => 'nullable|string|in:'.implode(',', array_keys(ShareableResolver::TYPES)),
            'shared_id'    => 'nullable|integer',
            'pieces_jointes'   => 'nullable|array|max:6',
            // Allow-list stricte des extensions/MIME — bloque html, svg, js, exe, etc.
            // Doit correspondre à l'attribut `accept` du champ file côté client.
            'pieces_jointes.*' => [
                'file',
                'max:20480',
                // + audio pour les notes vocales (mp3/m4a/ogg/wav/webm)
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mov,webm,mp3,m4a,ogg,wav',
            ],
        ]);

        // Au moins un des trois : texte, partage, pièces jointes
        $hasFiles = $request->hasFile('pieces_jointes');
        if (empty(trim((string) ($data['contenu'] ?? ''))) && empty($data['shared_type']) && !$hasFiles) {
            return back()->with('error', 'Message vide.');
        }

        // Résolution + garde-fou du partage
        $sharedType = null; $sharedId = null;
        $contenu = $data['contenu'] ?? null;

        // 1) Partage explicite via la modale (shared_type / shared_id)
        if (!empty($data['shared_type']) && !empty($data['shared_id'])) {
            $class = ShareableResolver::TYPES[$data['shared_type']];
            $model = $class::find($data['shared_id']);
            if ($model && ShareableResolver::userCanShare($user, $model)) {
                $sharedType = $class;
                $sharedId   = $model->id;
            }
        }

        // 2) Fallback : syntaxe inline `@_module&@_section&@_titre` dans le texte
        if (!$sharedType && $contenu) {
            $inline = ShareableResolver::parseInlineShare($contenu, $user);
            if ($inline) {
                $sharedType = ShareableResolver::TYPES[$inline['type']];
                $sharedId   = $inline['id'];
                // Retire la mention du contenu (garde le texte restant s'il y en a)
                $contenu = $inline['clean'];
            }
        }

        $msg = $group->messages()->create([
            'sender_id'   => $user->id,
            'contenu'     => $contenu,
            'shared_type' => $sharedType,
            'shared_id'   => $sharedId,
        ]);

        // Pièces jointes via HasPiecesJointes
        if ($hasFiles) {
            $msg->attacherFichiers($request->file('pieces_jointes'), 'social/groups/messages');
        }

        $group->touch();

        if ($request->wantsJson() || $request->ajax()) {
            $msg->load(['sender', 'piecesJointes']);
            return response()->json(['ok' => true, 'message' => $this->serializeMessage($msg)]);
        }
        return back();
    }

    /**
     * Sérialise un message pour l'API JSON (post ou fetch).
     */
    private function serializeMessage(SocialGroupMessage $msg): array
    {
        $pjs = $msg->piecesJointes->map(fn($p) => [
            'id'        => $p->id,
            'url'       => $p->url,
            'nom'       => $p->nom_original,
            'categorie' => $p->categorie,
            'mime'      => $p->type_mime,
        ]);
        $shared = null;
        if ($msg->shared_type && $msg->shared_id) {
            $obj = $msg->shared;
            if ($obj) $shared = ShareableResolver::normalize($obj);
        }
        return [
            'id'        => $msg->id,
            'contenu'   => $msg->contenu, // brut : le client utilise textContent
            'sender'    => trim(($msg->sender?->prenoms ?? '').' '.($msg->sender?->name ?? '')),
            'sender_id' => $msg->sender_id,
            'date'      => $msg->created_at->format('H:i'),
            'pieces'    => $pjs,
            'shared'    => $shared,
        ];
    }

    public function invite(Request $request, SocialGroup $group)
    {
        abort_unless($group->estAdmin($request->user()), 403);
        $data = $request->validate([
            'members'   => 'required|array',
            'members.*' => 'integer|exists:users,id',
        ]);
        foreach ($data['members'] as $uid) {
            SocialGroupMember::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => (int) $uid],
                ['role' => 'member'],
            );
        }
        return back()->with('success', 'Membres ajoutés.');
    }

    public function leave(Request $request, SocialGroup $group)
    {
        $user = $request->user();
        // Un admin ne peut pas quitter s'il est le seul admin
        if ($group->estAdmin($user)) {
            $otherAdmins = $group->members()->where('role', 'admin')->where('user_id', '!=', $user->id)->count();
            if ($otherAdmins === 0 && $group->members()->count() > 1) {
                return back()->with('error', 'Vous êtes seul admin — désignez un autre admin avant de quitter.');
            }
        }
        SocialGroupMember::where(['group_id' => $group->id, 'user_id' => $user->id])->delete();
        return redirect()->route('social.groups.index')->with('success', 'Vous avez quitté le groupe.');
    }

    public function destroy(Request $request, SocialGroup $group)
    {
        abort_unless($group->estAdmin($request->user()), 403);
        $group->delete();
        return redirect()->route('social.groups.index')->with('success', 'Groupe supprimé.');
    }

    /**
     * Endpoint JSON pour poller les nouveaux messages depuis un ID connu.
     */
    public function fetchMessages(Request $request, SocialGroup $group)
    {
        abort_unless($group->estMembre($request->user()), 403);
        $after = (int) $request->query('after', 0);
        $msgs = $group->messages()->with(['sender', 'piecesJointes', 'shared'])
            ->when($after > 0, fn($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->take(50)
            ->get()
            ->map(fn($m) => $this->serializeMessage($m));
        return response()->json(['messages' => $msgs]);
    }

    /**
     * Recherche AJAX de contenus ERP partageables (news, courriers, projets, tâches, médias…).
     */
    public function searchShareable(Request $request)
    {
        $q = (string) $request->query('q', '');
        $module = (string) $request->query('module', 'all');
        $results = ShareableResolver::search($request->user(), $q, 5, $module);
        return response()->json(['results' => $results]);
    }

    /**
     * Autocomplete mention `@_module&@_section&@_titre` — 3 endpoints unifiés.
     * Retourne : modules (step=1), sections (step=2), contents (step=3).
     */
    public function mentionAutocomplete(Request $request)
    {
        $step = (string) $request->query('step', 'modules');
        if ($step === 'modules') {
            // Retourne la liste des modules avec leur label FR
            $mods = [];
            foreach (ShareableResolver::MODULES as $key => $m) {
                if ($key === 'all') continue;
                $mods[] = ['key' => $key, 'label' => $m['label']];
            }
            return response()->json(['items' => $mods]);
        }

        if ($step === 'sections') {
            $module = (string) $request->query('module', '');
            $sections = ShareableResolver::SECTIONS[$module] ?? [];
            // dedup les alias (même type → 1 seule entrée avec la 1ère clé)
            $seen = [];
            $items = [];
            foreach ($sections as $sectionKey => $type) {
                if (isset($seen[$type])) continue;
                $seen[$type] = true;
                $items[] = ['key' => $sectionKey, 'label' => ShareableResolver::META[ShareableResolver::TYPES[$type]]['label'] ?? $sectionKey];
            }
            return response()->json(['items' => $items]);
        }

        if ($step === 'contents') {
            $module  = (string) $request->query('module', '');
            $section = (string) $request->query('section', '');
            $q       = (string) $request->query('q', '');
            $type = ShareableResolver::SECTIONS[$module][$section] ?? null;
            if (!$type) return response()->json(['items' => []]);
            $results = ShareableResolver::search($request->user(), $q, 10, 'all', $type, allowEmpty: true);
            $items = array_map(fn ($r) => ['title' => $r['title'], 'excerpt' => $r['excerpt']], $results);
            return response()->json(['items' => $items]);
        }

        return response()->json(['items' => []]);
    }
}
