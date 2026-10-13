<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Messagerie instantanée 1-à-1 entre collaborateurs.
 * Page plein écran (conversations + fil) et endpoints JSON utilisés par la page
 * et par le widget flottant (polling, pas de websocket).
 */
class MessagerieController extends Controller
{
    public function index(Request $request)
    {
        return $this->page($request, null);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->autoriser($request, $conversation);
        return $this->page($request, $conversation);
    }

    private function page(Request $request, ?Conversation $active)
    {
        $user = $request->user();

        if ($active) {
            $active->load('userUn', 'userDeux');
            $active->nonLusPour($user)->update(['lu_at' => now()]);
        }

        $conversations = $this->listeConversations($user);
        $messages = $active
            ? $active->messages()->latest('id')->take(100)->get()->reverse()->values()
            : collect();

        $collaborateurs = User::where('id', '!=', $user->id)->where('statut', 1)
            ->orderBy('name')->get(['id', 'name', 'prenoms', 'poste']);

        return view('intranet.messagerie.index', compact('conversations', 'active', 'messages', 'collaborateurs'));
    }

    /** Démarre (ou rouvre) la conversation avec un collaborateur. */
    public function demarrer(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'Impossible de discuter avec soi-même.');
        abort_unless($user->statut, 404);

        $conversation = Conversation::entre($request->user(), $user);

        if ($request->wantsJson()) {
            return response()->json(['conversation_id' => $conversation->id]);
        }
        return redirect()->route('intranet.messagerie.show', $conversation);
    }

    /** Messages après l'id `apres` (polling) ; marque les messages reçus comme lus. */
    public function messages(Request $request, Conversation $conversation)
    {
        $user = $this->autoriser($request, $conversation);
        $apres = (int) $request->query('apres', 0);

        $messages = $conversation->messages()
            ->when($apres > 0, fn($q) => $q->where('id', '>', $apres))
            ->latest('id')->take(100)->get()->reverse()->values();

        $conversation->nonLusPour($user)->update(['lu_at' => now()]);
        $autre = $conversation->autre($user);

        return response()->json([
            'messages' => $messages->map(fn(Message $m) => $this->serialiser($m, $user)),
            // Accusé de lecture : dernier de mes messages lu par l'autre
            'lu_jusqua' => (int) $conversation->messages()->where('sender_id', $user->id)->whereNotNull('lu_at')->max('id'),
            'en_ligne'  => (bool) $autre?->estEnLigne(),
        ]);
    }

    public function envoyer(Request $request, Conversation $conversation)
    {
        $user = $this->autoriser($request, $conversation);
        $data = $request->validate(['contenu' => 'required|string|max:4000']);

        $contenu = trim($data['contenu']);
        abort_if($contenu === '', 422, 'Message vide.');

        $message = DB::transaction(function () use ($conversation, $user, $contenu) {
            $m = $conversation->messages()->create(['sender_id' => $user->id, 'contenu' => $contenu]);
            $conversation->update(['last_message_at' => $m->created_at]);
            return $m;
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->serialiser($message, $user)]);
        }
        return redirect()->route('intranet.messagerie.show', $conversation);
    }

    /**
     * Collaborateurs actifs + présence + non-lus par interlocuteur (widget flottant)
     * et conversations récentes (page).
     */
    public function utilisateurs(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::pour($user)->get(['id', 'user_un_id', 'user_deux_id', 'last_message_at']);
        $parInterlocuteur = $conversations->keyBy(fn($c) => $c->user_un_id === $user->id ? $c->user_deux_id : $c->user_un_id);
        $nonLus = $this->nonLusParConversation($user);

        $users = User::where('id', '!=', $user->id)->where('statut', 1)
            ->with('roles:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'prenoms', 'poste', 'last_seen_at']);

        return response()->json($users->map(function (User $u) use ($parInterlocuteur, $nonLus) {
            $conv = $parInterlocuteur[$u->id] ?? null;
            return [
                'id'              => $u->id,
                'name'            => trim("{$u->prenoms} {$u->name}"),
                'role'            => $u->poste ?: ($u->roles->first()?->name ?? 'Collaborateur'),
                'online'          => $u->estEnLigne(),
                'conversation_id' => $conv?->id,
                'last_message_at' => $conv?->last_message_at?->toIso8601String(),
                'unread'          => $conv ? (int) ($nonLus[$conv->id] ?? 0) : 0,
            ];
        })->values());
    }

    /** Total des messages non lus (badges). */
    public function nonLus(Request $request)
    {
        return response()->json(['total' => (int) array_sum($this->nonLusParConversation($request->user()))]);
    }

    // ── Helpers ─────────────────────────────────────────

    private function autoriser(Request $request, Conversation $conversation): User
    {
        $user = $request->user();
        abort_unless($conversation->participe($user), 403);
        return $user;
    }

    /** [conversation_id => nb de messages reçus non lus] */
    private function nonLusParConversation(User $user): array
    {
        return Message::query()
            ->join('chat_conversations as c', 'c.id', '=', 'chat_messages.conversation_id')
            ->where(fn($w) => $w->where('c.user_un_id', $user->id)->orWhere('c.user_deux_id', $user->id))
            ->where('chat_messages.sender_id', '!=', $user->id)
            ->whereNull('chat_messages.lu_at')
            ->groupBy('chat_messages.conversation_id')
            ->selectRaw('chat_messages.conversation_id, count(*) as n')
            ->pluck('n', 'conversation_id')
            ->all();
    }

    private function listeConversations(User $user)
    {
        $nonLus = $this->nonLusParConversation($user);

        return Conversation::pour($user)
            ->whereNotNull('last_message_at')
            ->with(['userUn:id,name,prenoms,poste,last_seen_at', 'userDeux:id,name,prenoms,poste,last_seen_at', 'dernierMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->each(function (Conversation $c) use ($user, $nonLus) {
                $c->interlocuteur = $c->autre($user);
                $c->non_lus = (int) ($nonLus[$c->id] ?? 0);
            });
    }

    private function serialiser(Message $m, User $user): array
    {
        return [
            'id'      => $m->id,
            'contenu' => $m->contenu, // brut : le client utilise textContent
            'mine'    => $m->sender_id === $user->id,
            'heure'   => $m->created_at->format('H:i'),
            'date'    => $m->created_at->toDateString(),
        ];
    }
}
