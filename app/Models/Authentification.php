<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Authentification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ip', 'email', 'operations', 'lieu',
        'user_agent', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
        ];
    }
}
