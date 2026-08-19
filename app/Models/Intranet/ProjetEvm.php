<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class ProjetEvm extends Model {
    protected $table = 'intranet_projet_evm';
    protected $fillable = ['projet_id','date_mesure','bac','pv','ev','ac','sv','cv','spi','cpi','etc','eac','commentaire','created_by'];
    protected $casts = ['date_mesure'=>'date'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    // Santé projet
    public function getSanteAttribute(): string {
        $cpi = $this->cpi ?? 1;
        $spi = $this->spi ?? 1;
        if ($cpi >= 0.95 && $spi >= 0.95) return 'vert';
        if ($cpi >= 0.8  || $spi >= 0.8)  return 'orange';
        return 'rouge';
    }
}
