<?php

namespace App\Models\Social;

use App\Models\User;
use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialGroupMessage extends Model
{
    use SoftDeletes, HasPiecesJointes;

    protected $table = 'social_group_messages';

    protected $fillable = ['group_id', 'sender_id', 'contenu', 'shared_type', 'shared_id'];

    public function group()  { return $this->belongsTo(SocialGroup::class, 'group_id'); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function shared() { return $this->morphTo(); }
}
