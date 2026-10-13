<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

/** Lien entre un mail classé et une entité de la solution (polymorphe). */
class MailSuiviLien extends Model
{
    protected $table = 'mail_suivi_liens';

    protected $fillable = ['suivi_id', 'liable_type', 'liable_id', 'created_by'];

    public function suivi()  { return $this->belongsTo(MailSuivi::class, 'suivi_id'); }
    public function liable() { return $this->morphTo(); }
}
