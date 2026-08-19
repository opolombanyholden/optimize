<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;

class TypeDocumentExigence extends Model
{
    protected $table = 'intranet_types_documents_exigences';

    protected $fillable = [
        'type_document_id', 'type_organisation', 'obligatoire',
    ];

    protected $casts = [
        'obligatoire' => 'boolean',
    ];

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'type_document_id');
    }
}
