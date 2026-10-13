@extends('layouts.app')
@section('title', $modele->exists ? 'Modifier un modèle de signature' : 'Nouveau modèle de signature')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item"><a href="{{ route('admin.signatures-modeles.index') }}">Modèles de signature</a></li><li class="breadcrumb-item active">{{ $modele->exists ? $modele->nom : 'Nouveau' }}</li></ol>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" integrity="sha384-VvSC4PGxeMkOaAmyuDGZECjY2dkqdO/IdBYBUK+BCYNc3WIvRxHLUzQ5OSgUaMA7" crossorigin="anonymous">
@endpush

@section('content')
<form action="{{ $modele->exists ? route('admin.signatures-modeles.update', $modele) : route('admin.signatures-modeles.store') }}" method="POST" class="card data-card" style="max-width:1100px;">
    @csrf
    @if($modele->exists) @method('PUT') @endif
    <div class="card-header d-flex align-items-center gap-2">
        <h5 class="mb-0 me-auto"><i class="fas fa-signature me-2 text-muted"></i>{{ $modele->exists ? 'Modifier « '.$modele->nom.' »' : 'Nouveau modèle de signature' }}</h5>
        <a href="{{ route('admin.signatures-modeles.index') }}" class="btn btn-sm btn-outline-secondary">Annuler</a>
        <button class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Enregistrer</button>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger py-2" style="font-size:.85rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <div class="row g-3 mb-3">
            <div class="col-md-5">
                <label class="form-label">Nom du modèle <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" maxlength="100" required value="{{ old('nom', $modele->nom) }}" placeholder="Ex. Signature officielle">
            </div>
            <div class="col-md-7 d-flex flex-wrap align-items-end gap-4">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="actif" value="0">
                    <input class="form-check-input" type="checkbox" name="actif" value="1" id="mActif" @checked(old('actif', $modele->actif))>
                    <label class="form-check-label" for="mActif">Actif (proposé aux utilisateurs)</label>
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="verrouille" value="0">
                    <input class="form-check-input" type="checkbox" name="verrouille" value="1" id="mVerrou" @checked(old('verrouille', $modele->verrouille))>
                    <label class="form-check-label" for="mVerrou">Imposé (les utilisateurs ne peuvent pas le retirer)</label>
                </div>
            </div>
        </div>

        @include('mon-compte._signature-editeur', [
            'sourceHtml' => old('source_html', $modele->source_html),
            'sourceCss'  => old('source_css', $modele->source_css),
            'variables'  => true,
        ])
        <div class="form-text mt-2">
            Variables remplacées pour chaque utilisateur :
            @foreach(\App\Services\Mail\Signature::VARIABLES as $cle => $lib)<code>{{ '{'.'{'.$cle.'}'.'}' }}</code> {{ mb_strtolower($lib) }}@if(!$loop->last), @endif @endforeach.
            Le CSS est converti en styles en ligne ; le logo est joint aux mails comme image intégrée.
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js" integrity="sha384-hcxmSutM10NL6iGBAA0LStIhy+kWJxfrhqWVMRuABZH5Vqztexq2nBz/Xnfllly9" crossorigin="anonymous"></script>
<script>document.addEventListener('DOMContentLoaded', () => window.initEditeurSignature(document.querySelector('[data-sig-editeur]')));</script>
@endpush
