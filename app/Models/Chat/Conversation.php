<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Conversation privée entre deux collaborateurs (messagerie instantanée).
 * La paire est stockée ordonnée : user_un_id < user_deux_id.
 */
class Conversation extends Model
{
    protected $table = 'chat_conversations';

    protected $fillable = ['user_un_id', 'user_deux_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function userUn()   { return $this->belongsTo(User::class, 'user_un_id'); }
    public function userDeux() { return $this->belongsTo(User::class, 'user_deux_id'); }
    public function messages() { return $this->hasMany(Message::class, 'conversation_id'); }
    public function dernierMessage() { return $this->hasOne(Message::class, 'conversation_id')->latestOfMany(); }

    /** Conversation entre deux utilisateurs, créée si besoin. */
    public static function entre(User $a, User $b): self
    {
        [$un, $deux] = $a->id < $b->id ? [$a->id, $b->id] : [$b->id, $a->id];
        return static::firstOrCreate(['user_un_id' => $un, 'user_deux_id' => $deux]);
    }

    public function scopePour(Builder $q, User $user): Builder
    {
        return $q->where(fn($w) => $w->where('user_un_id', $user->id)->orWhere('user_deux_id', $user->id));
    }

    public function participe(User $user): bool
    {
        return in_array($user->id, [$this->user_un_id, $this->user_deux_id], true);
    }

    /** L'interlocuteur de $user dans cette conversation. */
    public function autre(User $user): ?User
    {
        return $this->user_un_id === $user->id ? $this->userDeux : $this->userUn;
    }

    /** Messages reçus par $user et non encore lus. */
    public function nonLusPour(User $user)
    {
        return $this->messages()->where('sender_id', '!=', $user->id)->whereNull('lu_at');
    }
}
