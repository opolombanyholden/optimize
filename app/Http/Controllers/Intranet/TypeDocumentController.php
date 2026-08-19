<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\TypeDocument;
use App\Models\Intranet\TypeDocumentExigence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypeDocumentController extends Controller
{
    /**
     * Vue référentiel : liste des types + matrice d'exigence type × type_organisation.
     */
    public function index()
    {
        $types = TypeDocument::with('exigences')->orderBy('ordre')->orderBy('libelle')->get();
        return view('intranet.organisations.types-documents.index', [
            'types'         => $types,
            'typesOrganisation' => ContactOrganisation::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => ['required', 'string', 'max:60', 'unique:intranet_types_documents,code'],
            'libelle'         => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'icone'           => ['nullable', 'string', 'max:60'],
            'ordre'           => ['nullable', 'integer'],
            'avec_expiration' => ['nullable', 'boolean'],
        ]);
        $data['avec_expiration'] = $request->boolean('avec_expiration');
        $data['actif']  = true;
        $data['icone']  = $data['icone'] ?: 'fa-file-lines';
        $data['ordre']  = $data['ordre'] ?? 0;
        TypeDocument::create($data);
        return back()->with('success', 'Type de document créé.');
    }

    public function update(Request $request, TypeDocument $type)
    {
        $data = $request->validate([
            'libelle'         => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'icone'           => ['nullable', 'string', 'max:60'],
            'ordre'           => ['nullable', 'integer'],
            'avec_expiration' => ['nullable', 'boolean'],
            'actif'           => ['nullable', 'boolean'],
        ]);
        $data['avec_expiration'] = $request->boolean('avec_expiration');
        $data['actif']           = $request->boolean('actif');
        $data['icone']           = $data['icone'] ?: 'fa-file-lines';
        $type->update($data);
        return back()->with('success', 'Type de document mis à jour.');
    }

    public function destroy(TypeDocument $type)
    {
        $type->delete();
        return back()->with('success', 'Type de document supprimé.');
    }

    /**
     * Sauvegarde de toute la matrice type × type_organisation.
     * Payload : matrice[type_document_id][type_organisation] = 'obligatoire' | 'facultatif' | ''.
     */
    public function saveMatrice(Request $request)
    {
        $matrice = $request->input('matrice', []);
        DB::transaction(function () use ($matrice) {
            foreach ($matrice as $typeDocId => $exigences) {
                foreach ($exigences as $typeOrg => $niveau) {
                    if (!in_array($niveau, ['obligatoire', 'facultatif'])) {
                        TypeDocumentExigence::where('type_document_id', $typeDocId)
                            ->where('type_organisation', $typeOrg)
                            ->delete();
                        continue;
                    }
                    TypeDocumentExigence::updateOrCreate(
                        ['type_document_id' => $typeDocId, 'type_organisation' => $typeOrg],
                        ['obligatoire' => $niveau === 'obligatoire']
                    );
                }
            }
        });
        return back()->with('success', 'Matrice des exigences mise à jour.');
    }
}
