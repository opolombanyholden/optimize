<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'configs';

    protected $fillable = [
        'config_object', 'description',
    ];

    protected function casts(): array
    {
        return [
            'config_object' => 'array',
        ];
    }
}
