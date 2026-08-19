<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\OrganisationDocument;
use App\Models\Intranet\TypeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganisationDocumentController extends Controller
{
    private const FOLDER = 'intranet/organisations/documents';

    public function store(Request $request, ContactOrganisation $organisation)
    {
        $data = $request->validate([
            'type_document_id' => ['required', 'exists:intranet_types_documents,id'],
            'fichier'          => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'date_expiration'  => ['nullable', 'date'],
            'notes'            => ['nullable', 'string'],
        ]);

        $type = TypeDocument::findOrFail($data['type_document_id']);
        $file = $request->file('fichier');

        $doc = $organisation->documents()->create([
            'type_document_id' => $type->id,
            'fichier'          => $file->store(self::FOLDER . '/' . $organisation->id, 'public'),
            'nom_original'     => $file->getClientOriginalName(),
            'taille'           => $file->getSize(),
            'mime'             => $file->getMimeType(),
            'date_expiration'  => $type->avec_expiration ? ($data['date_expiration'] ?? null) : null,
            'notes'            => $data['notes'] ?? null,
            'uploaded_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Document « ' . $type->libelle . ' » ajouté.');
    }

    public function destroy(ContactOrganisation $organisation, OrganisationDocument $document)
    {
        abort_unless($document->organisation_id === $organisation->id, 404);
        if ($document->fichier) Storage::disk('public')->delete($document->fichier);
        $document->delete();
        return back()->with('success', 'Document supprimé.');
    }
}
