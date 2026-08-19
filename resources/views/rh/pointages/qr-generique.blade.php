@extends('layouts.app')

@section('title', 'Pointage')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="text-center mb-3 mt-4">
            <h1 class="h3 mb-1"><i class="fas fa-clock me-2 text-primary"></i>Pointage rapide</h1>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l d F Y · H:i') }}</p>
        </div>

        <div class="card data-card">
            <div class="card-body p-4">
                <form action="{{ route('pointage.qr-generique.check', ['token' => $token]) }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="mb-3">
                        <label for="matricule" class="form-label">Votre matricule <span class="text-danger">*</span></label>
                        <input type="text" name="matricule" id="matricule"
                               class="form-control form-control-lg @error('matricule') is-invalid @enderror"
                               required maxlength="50" autofocus
                               autocapitalize="characters"
                               placeholder="Ex : EMP-042"
                               value="{{ old('matricule') }}">
                        @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="pin" class="form-label">Votre PIN <span class="text-danger">*</span> <small class="text-muted">(4 à 6 chiffres)</small></label>
                        <input type="password" name="pin" id="pin"
                               class="form-control form-control-lg text-center @error('pin') is-invalid @enderror"
                               required minlength="4" maxlength="8"
                               inputmode="numeric"
                               pattern="\d{4,8}"
                               style="letter-spacing: .5em; font-size: 1.6rem;"
                               placeholder="••••">
                        @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check-circle me-1"></i> Pointer maintenant
                    </button>
                </form>

                <hr class="my-3">
                <p class="text-muted small text-center mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Votre PIN vous est communiqué par la DRH.
                    En cas d'oubli, contactez le service RH pour le réinitialiser.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
