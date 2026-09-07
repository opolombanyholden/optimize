<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayWish extends Model
{
    protected $table = 'birthday_wishes';

    protected $fillable = ['employee_id', 'expediteur_id', 'date_anniversaire', 'message'];

    protected $casts = ['date_anniversaire' => 'date'];

    public function employee()    { return $this->belongsTo(Employee::class, 'employee_id'); }
    public function expediteur()  { return $this->belongsTo(User::class, 'expediteur_id'); }
}
