<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
class PublicationCible extends Model {
    public $timestamps = false;
    protected $table = 'intranet_publication_cibles';
    protected $fillable = ['publication_id','cible_type','cible_id'];
    public function publication() { return $this->belongsTo(Publication::class,'publication_id'); }
}
