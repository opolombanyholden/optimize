<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class KpiValeur extends Model {
    protected $table = 'intranet_kpi_valeurs';
    protected $fillable = ['kpi_id','valeur','date_mesure','commentaire','created_by'];
    protected $casts = ['date_mesure'=>'date','valeur'=>'decimal:4'];
    public function kpi() { return $this->belongsTo(Kpi::class,'kpi_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
