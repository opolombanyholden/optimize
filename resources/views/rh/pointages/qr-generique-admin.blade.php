@extends('layouts.app')

@section('title', 'QR Code générique de pointage')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.pointages.index') }}">Pointages</a></li>
        <li class="breadcrumb-item active">QR générique</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-qrcode me-2 text-primary"></i>QR Code générique de pointage</h1>
        <p class="text-muted mb-0">Un seul QR partagé, à afficher à l'entrée du bureau.</p>
    </div>
    <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">

        @if(!$configured)
            <div class="alert alert-warning">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Aucun token configuré</h6>
                <p class="mb-2">Le QR générique nécessite un token sécurisé. Cliquez sur « Régénérer » pour en créer un — il sera écrit dans votre fichier <code>.env</code> (clé <code>POINTAGE_QR_GENERIQUE_TOKEN</code>).</p>
                <form action="{{ route('rh.pointages.qr-generique.regenerer') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-primary"><i class="fas fa-key me-1"></i> Générer le token</button>
                </form>
            </div>
        @else
            <div class="row g-3">
                {{-- Aperçu QR --}}
                <div class="col-md-5">
                    <div class="card data-card text-center">
                        <div class="card-body p-4">
                            <img src="{{ route('rh.pointages.qr-generique.image') }}" alt="QR générique" class="img-fluid border rounded" style="max-width: 280px;">
                            <p class="text-muted small mt-3 mb-0">
                                Token : <code class="user-select-all">{{ $token }}</code>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="col-md-7">
                    <div class="card data-card mb-3">
                        <div class="card-header"><strong><i class="fas fa-tools me-1"></i> Actions</strong></div>
                        <div class="card-body">
                            <a href="{{ route('rh.pointages.qr-generique.imprimer') }}" target="_blank" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-print me-1"></i> Page imprimable A4 (avec mode d'emploi)
                            </a>
                            <a href="{{ route('rh.pointages.qr-generique.image') }}" download="qr-generique-{{ now()->format('Ymd') }}.png" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-download me-1"></i> Télécharger l'image PNG
                            </a>
                            <hr>
                            <form action="{{ route('rh.pointages.qr-generique.regenerer') }}" method="POST"
                                  onsubmit="return confirm('Régénérer le token rendra l\'ancien QR invalide. Toutes les affiches doivent être réimprimées. Confirmer ?');">
                                @csrf
                                <button class="btn btn-outline-warning w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Régénérer le token
                                </button>
                            </form>
                            <small class="form-text text-muted d-block mt-2">
                                À régénérer si le QR a fuité (capture/photo). Après régénération, exécutez
                                <code>php artisan config:clear</code> en production.
                            </small>
                        </div>
                    </div>

                    <div class="card data-card border-info">
                        <div class="card-header bg-info text-white"><strong><i class="fas fa-info-circle me-1"></i> Comment ça marche</strong></div>
                        <div class="card-body small">
                            <ol class="ps-3 mb-2">
                                <li>Imprimez la page A4 (bouton ci-dessus) et affichez-la à l'entrée du bureau.</li>
                                <li>Chaque employé scanne le QR avec son téléphone.</li>
                                <li>Il saisit son <strong>matricule</strong> et son <strong>PIN à 4-6 chiffres</strong>.</li>
                                <li>Le pointage est validé immédiatement.</li>
                            </ol>
                            <p class="mb-0 text-muted">
                                Pour définir/réinitialiser un PIN : fiche employé → bouton « Définir le PIN de pointage ».
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
