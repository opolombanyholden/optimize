@extends('layouts.app')

@section('title', 'Importer au grand-livre')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.grand-livre.index') }}">Grand-livre</a></li>
        <li class="breadcrumb-item active">Importer</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-upload me-2" style="color:#4F46E5;"></i>Importer des écritures</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">
            Import en masse depuis un fichier CSV (bulk data loading).
            Les écritures sont créées <strong>en brouillon</strong> — à valider ensuite.
        </p>
    </div>
    <a href="{{ route('finance.grand-livre.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card data-card">
            <div class="card-header" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.9rem; font-weight:700;">
                    <i class="fas fa-file-arrow-up me-2" style="color:#4F46E5;"></i>Fichier CSV
                </h6>
            </div>
            <form action="{{ route('finance.grand-livre.import.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf

                @if(session('error'))
                    <div class="alert alert-danger" style="font-size:.85rem;">
                        <strong><i class="fas fa-triangle-exclamation me-1"></i>{{ session('error') }}</strong>
                        @if(session('import_erreurs'))
                        <ul class="mb-0 mt-2" style="font-size:.82rem;">
                            @foreach(session('import_erreurs') as $err)
                                <li>Ligne <strong>{{ $err['ligne'] }}</strong> : {{ implode(' · ', $err['motifs']) }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="fichier" class="form-control" required accept=".csv,.xlsx,text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <div class="form-text">
                        Formats acceptés :
                        <strong>CSV</strong> (séparateur <code>;</code>, encodage UTF-8) ou
                        <strong>Excel XLSX</strong>.
                        Max 5 Mo. La première feuille est lue pour les XLSX.
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ignore_erreurs" value="1" id="ignoreErreurs">
                        <label class="form-check-label" for="ignoreErreurs">
                            <strong>Ignorer les lignes en erreur</strong>
                            <div class="text-muted" style="font-size:.78rem;">Importe uniquement les lignes valides ; les lignes fautives sont rapportées mais non insérées.</div>
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn" style="background:#4F46E5; color:#fff;">
                        <i class="fas fa-upload me-1"></i>Lancer l'import
                    </button>
                    <a href="{{ route('finance.grand-livre.import.template') }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-arrow-down me-1"></i>Télécharger le modèle CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card data-card">
            <div class="card-header" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.9rem; font-weight:700;">
                    <i class="fas fa-circle-info me-2" style="color:#4F46E5;"></i>Colonnes attendues
                </h6>
            </div>
            <div class="card-body" style="font-size:.85rem;">
                <p class="text-muted">
                    Le fichier doit être au format CSV avec point-virgule comme séparateur. Les colonnes marquées <span class="text-danger">*</span> sont obligatoires.
                </p>
                <ul style="list-style:none; padding:0;">
                    <li><code>date_ecriture</code> <span class="text-danger">*</span> — format YYYY-MM-DD</li>
                    <li><code>libelle</code> <span class="text-danger">*</span></li>
                    <li><code>sens</code> <span class="text-danger">*</span> — <code>debit</code> ou <code>credit</code></li>
                    <li><code>montant_tc</code> <span class="text-danger">*</span> — nombre &gt; 0</li>
                    <li><code>compte_code</code> <span class="text-danger">*</span> — ou <code>compte_id</code></li>
                    <li><code>id_exercicebudgetaire</code> — id de l'exercice</li>
                    <li><code>description</code></li>
                    <li><code>num_piece</code></li>
                    <li><code>journal</code></li>
                    <li><code>devise</code> — défaut XAF</li>
                    <li><code>beneficiaire</code></li>
                </ul>

                @if($exercices->isNotEmpty())
                <hr>
                <h6 style="font-size:.78rem; font-weight:700; text-transform:uppercase; color:#64748B; letter-spacing:.05em;">Exercices disponibles</h6>
                <ul style="font-size:.8rem; margin:0; padding-left:1rem;">
                    @foreach($exercices as $ex)
                        <li><code>{{ $ex->id }}</code> — {{ $ex->libelle ?? $ex->exercice }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <div class="alert alert-warning mt-3" style="font-size:.82rem;">
            <strong><i class="fas fa-triangle-exclamation me-1"></i>À savoir</strong>
            <ul class="mb-0 mt-2">
                <li>Les écritures importées sont créées <strong>en brouillon</strong> (isvalide = 0).</li>
                <li>Utilisez cette fonction pour <strong>migrer</strong> depuis un ancien logiciel ou intégrer des <strong>relevés bancaires</strong> exportés.</li>
                <li>Pour la saisie quotidienne, utilisez les <a href="{{ route('finance.ordres.index') }}">ordres de dépense/recette</a>.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
