@extends('layouts.app')

@section('title', 'Pointage QR — ' . $employee->noms . ' ' . $employee->prenoms)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="page-header text-center mb-4 mt-4">
            <h1 class="h3 mb-1"><i class="fas fa-qrcode me-2"></i>Pointage par QR</h1>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l d F Y · H:i') }}</p>
        </div>

        <div class="card data-card text-center">
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white"
                          style="width:72px;height:72px;font-size:1.8rem;">
                        <i class="fas fa-user-check"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ $employee->noms }} {{ $employee->prenoms }}</h4>
                <p class="text-muted mb-4"><code>{{ $employee->matricule ?? '—' }}</code></p>

                <form action="{{ route('pointage.qr.confirmer', ['token' => $token]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check-circle me-1"></i> Confirmer mon pointage
                    </button>
                </form>

                <p class="text-muted small mt-3 mb-0">
                    Cliquez sur le bouton pour enregistrer votre entrée ou sortie.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
