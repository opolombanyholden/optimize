<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'id_user', 'type_action', 'action',
        'adresse_ip', 'message', 'old_value', 'new_value',
    ];
}
