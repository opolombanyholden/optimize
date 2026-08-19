<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
class Statut extends Model {
    protected $table = 'intranet_statuts';
    protected $fillable = ['libelle','couleur'];
}
