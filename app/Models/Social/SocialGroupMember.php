<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SocialGroupMember extends Model
{
    protected $table = 'social_group_members';

    protected $fillable = ['group_id', 'user_id', 'role', 'joined_at', 'last_read_at'];

    protected $casts = [
        'joined_at'    => 'datetime',
        'last_read_at' => 'datetime',
    ];

    public function group() { return $this->belongsTo(SocialGroup::class, 'group_id'); }
    public function user()  { return $this->belongsTo(User::class, 'user_id'); }
}
