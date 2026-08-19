@extends('layouts.app')

@section('title', 'Changement de mot de passe obligatoire')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-6 col-lg-5">
        <div class="card data-card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-shield-halved me-1"></i> Changement de mot de passe requis</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small">
                    Votre mot de passe a été défini ou réinitialisé par un administrateur.
                    Pour des raisons de sécurité, vous devez en définir un nouveau avant de continuer.
                </div>

                @if(session('warning'))
                    <div class="alert alert-info">{{ session('warning') }}</div>
                @endif

                <form action="{{ route('password.force-change.update') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel (temporaire) <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" id="current_password"
                               class="form-control @error('current_password') is-invalid @enderror"
                               required autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" minlength="8"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="new-password">
                        <small class="form-text text-muted">8 caractères min, mélange majuscules/minuscules et chiffres.</small>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" minlength="8"
                               class="form-control" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-warning w-100"><i class="fas fa-key me-1"></i> Changer mon mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
