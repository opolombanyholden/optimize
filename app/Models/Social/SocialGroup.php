<?php

namespace App\Models\Social;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialGroup extends Model
{
    use SoftDeletes;

    protected $table = 'social_groups';

    protected $fillable = ['nom', 'description', 'avatar', 'is_public', 'created_by'];

    protected $casts = ['is_public' => 'boolean'];

    protected $appends = ['avatar_url'];

    public function auteur()   { return $this->belongsTo(User::class, 'created_by'); }
    public function members()  { return $this->hasMany(SocialGroupMember::class, 'group_id'); }
    public function messages() { return $this->hasMany(SocialGroupMessage::class, 'group_id'); }

    public function users()
    {
        return $this->belongsToMany(User::class, 'social_group_members', 'group_id', 'user_id')
            ->withPivot(['role', 'joined_at', 'last_read_at'])->withTimestamps();
    }

    public function estMembre(?User $user): bool
    {
        if (!$user) return false;
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function estAdmin(?User $user): bool
    {
        if (!$user) return false;
        return $this->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) return null;
        if (preg_match('#^https?://#', $this->avatar)) return $this->avatar;
        return asset('storage/' . ltrim($this->avatar, '/'));
    }
}
