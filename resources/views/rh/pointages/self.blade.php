@extends('layouts.app')

@section('title', 'Mon pointage')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Mon pointage</li>
    </ol>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="page-header mb-3">
            <h1 class="h3 mb-1"><i class="fas fa-clock me-2 text-muted"></i>Mon pointage</h1>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l d F Y') }} · {{ now()->format('H:i') }}</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Statut du jour --}}
        <div class="card data-card mb-3">
            <div class="card-body">
                @if($pointageDuJour)
                    <h6 class="text-muted mb-3">Pointage du jour</h6>
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="text-muted small">Entrée</div>
                            <strong class="fs-4 text-success">
                                {{ $pointageDuJour->heure_entree ? substr($pointageDuJour->heure_entree, 0, 5) : '—' }}
                            </strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Sortie</div>
                            <strong class="fs-4 {{ $pointageDuJour->heure_sortie ? 'text-info' : 'text-muted' }}">
                                {{ $pointageDuJour->heure_sortie ? substr($pointageDuJour->heure_sortie, 0, 5) : 'en cours' }}
                            </strong>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center small">
                        <div>
                            <i class="fas fa-{{ $pointageDuJour->mode_pointage === 'qr_code' ? 'qrcode' : ($pointageDuJour->mode_pointage === 'intranet' ? 'network-wired' : 'house-laptop') }} me-1 text-muted"></i>
                            Mode : <strong>{{ $pointageDuJour->mode_libelle }}</strong>
                        </div>
                        <div>
                            <span class="badge bg-{{ $pointageDuJour->statut_couleur }}">{{ $pointageDuJour->statut_libelle }}</span>
                            @if($pointageDuJour->requires_validation_n1)
                                <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>Validation N+1 attendue</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fs-1 text-muted opacity-50 d-block mb-2"></i>
                        <p class="text-muted mb-0">Aucun pointage enregistré aujourd'hui.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Boutton de pointage --}}
        <form action="{{ route('pointage.self.pointer') }}" method="POST">
            @csrf
            <div class="card data-card border-{{ $modePrevu === 'intranet' ? 'success' : 'warning' }}">
                <div class="card-body text-center">
                    @php
                        $estIntranet = $modePrevu === 'intranet';
                        $bg   = $estIntranet ? 'bg-success' : 'bg-warning';
                        $icon = $estIntranet ? 'fa-network-wired' : 'fa-house-laptop';
                        $libelle = $estIntranet ? 'Réseau de l\'entreprise détecté' : 'Connexion hors réseau (télétravail)';
                    @endphp
                    <div class="mb-3">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $bg }} text-white"
                              style="width:64px;height:64px;font-size:1.6rem;">
                            <i class="fas {{ $icon }}"></i>
                        </span>
                    </div>
                    <p class="mb-1"><strong>{{ $libelle }}</strong></p>
                    <p class="text-muted small mb-3">IP détectée : <code>{{ $ip }}</code></p>
                    @if(!$estIntranet)
                        <div class="alert alert-warning small text-start">
                            <i class="fas fa-info-circle me-1"></i>
                            En mode télétravail, votre pointage sera <strong>transmis à votre N+1</strong> pour validation avant prise en compte en paie.
                        </div>
                    @endif

                    @if($pointageDuJour && $pointageDuJour->heure_sortie)
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-check me-1"></i> Journée déjà clôturée
                        </button>
                    @elseif($pointageDuJour && !$pointageDuJour->heure_sortie)
                        <button class="btn btn-info btn-lg w-100" type="submit">
                            <i class="fas fa-sign-out-alt me-1"></i> Pointer ma sortie
                        </button>
                    @else
                        <button class="btn btn-{{ $estIntranet ? 'success' : 'warning' }} btn-lg w-100" type="submit">
                            <i class="fas fa-sign-in-alt me-1"></i> Pointer mon entrée
                        </button>
                    @endif
                </div>
            </div>
        </form>

        {{-- Autres modes --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                Vous préférez un autre mode ? Demandez votre QR code personnel à la DRH ou consultez votre fiche employé.
            </small>
        </div>
    </div>
</div>
@endsection
