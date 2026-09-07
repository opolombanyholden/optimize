@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="card shadow-lg border-0" style="width: 100%; max-width: 420px; border-radius: 16px;">
    <div class="card-body p-4 p-md-5">
        {{-- Logo --}}
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: #1e293b;">
                <span style="color: #2563eb;">Optimi</span>Ze
            </h2>
            <p class="text-muted small">Connectez-vous à votre espace ERP</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email"
                        class="form-control border-start-0 @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus
                        placeholder="votre@email.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" id="password" name="password"
                        class="form-control border-start-0 @error('password') is-invalid @enderror"
                        required placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Remember me --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small text-decoration-none" href="{{ route('password.request') }}">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
            </button>
        </form>

        <div class="text-center mt-4">
            <a class="small text-decoration-none" href="{{ route('vitrine.public') }}">
                <i class="fas fa-compass me-1"></i>Découvrir OptimiZe
            </a>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">&copy; {{ date('Y') }} OptimiZe ERP — Tous droits réservés</small>
        </div>
    </div>
</div>
@endsection
