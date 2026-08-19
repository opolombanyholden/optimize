<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TypeEvenement extends Model {
    use SoftDeletes;
    protected $table = 'intranet_type_evenements';
    protected $fillable = ['nom','code','couleur','description'];
}
