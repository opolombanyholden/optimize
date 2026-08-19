<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
class ModuleParametre extends Model {
    protected $table = 'intranet_module_parametres';
    protected $fillable = ['module','likes_actifs','commentaires_actifs','partage_actif','ciblage_users','ciblage_groupes','ciblage_entites','peut_etre_public','notif_publication','notif_commentaire','notif_like','moderation_commentaires','actif'];
    protected $casts = [
        'likes_actifs'=>'boolean','commentaires_actifs'=>'boolean','partage_actif'=>'boolean',
        'ciblage_users'=>'boolean','ciblage_groupes'=>'boolean','ciblage_entites'=>'boolean',
        'peut_etre_public'=>'boolean','notif_publication'=>'boolean','notif_commentaire'=>'boolean',
        'notif_like'=>'boolean','moderation_commentaires'=>'boolean','actif'=>'boolean',
    ];
    public static function pour(string $module): self {
        return Cache::remember("intranet_module_{$module}", 300, fn() =>
            static::firstOrCreate(['module' => $module], [
                'likes_actifs' => true, 'commentaires_actifs' => true,
                'partage_actif' => false, 'peut_etre_public' => true, 'actif' => true,
            ])
        );
    }
    public static function clearCache(string $module): void {
        Cache::forget("intranet_module_{$module}");
    }
}
