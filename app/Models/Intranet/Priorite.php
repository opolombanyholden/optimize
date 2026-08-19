<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
class Priorite extends Model {
    protected $table = 'intranet_priorites';
    protected $fillable = ['libelle','couleur'];
}
