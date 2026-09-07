<?php

namespace App\Models\Social;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes, HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes;

    protected $table = 'social_posts';

    protected $fillable = [
        'created_by', 'contenu',
        'media_principal', 'media_principal_type',
        'vues_count',
    ];

    protected $casts = [
        'vues_count' => 'integer',
    ];

    protected $appends = ['media_url'];

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (!$this->media_principal) return null;
        // Support des URLs externes directes (démo) + stockage local
        if (preg_match('#^https?://#', $this->media_principal)) return $this->media_principal;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }
}
