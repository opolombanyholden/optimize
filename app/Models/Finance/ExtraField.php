<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Champ personnalisé dynamique attaché à un modèle (par identifier).
 * Permet d'étendre un modèle sans migration.
 * Conforme au schéma initial : table `extra_fields`.
 */
class ExtraField extends Model
{
    use SoftDeletes;

    protected $table = 'extra_fields';

    protected $fillable = ['identifier', 'name', 'type', 'model'];

    public const TYPES = ['text', 'number', 'date', 'select', 'boolean'];
}
