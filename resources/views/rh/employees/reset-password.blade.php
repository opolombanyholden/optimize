@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe — ' . $employee->noms . ' ' . $employee->prenoms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.index') }}">Employés</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.show', $employee) }}">{{ $employee->noms }} {{ $employee->prenoms }}</a></li>
        <li class="breadcrumb-item active">Réinitialisation du mot de passe</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-key text-warning me-2"></i> Réinitialisation du mot de passe</h1>
        <p class="text-muted mb-0">Procédure administrative tracée — l'utilisateur recevra le mot de passe par email.</p>
    </div>
    <a href="{{ route('rh.employees.show', $employee) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Annuler
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="alert alert-warning border-warning">
            <h6 class="alert-heading"><i class="fas fa-shield-halved me-1"></i> Information importante</h6>
            <ul class="mb-0 ps-3" style="font-size:.875rem;">
                <li>Le mot de passe actuel de l'utilisateur n'est jamais lisible (hash unidirectionnel).</li>
                <li>Le nouveau mot de passe est envoyé <strong>par email</strong> à l'utilisateur ({{ $employee->user->email }}).</li>
                <li>L'utilisateur sera <strong>forcé de le changer</strong> à sa prochaine connexion.</li>
                <li>Toutes ses sessions actives seront invalidées.</li>
                <li>Cette opération est tracée dans le journal d'audit.</li>
            </ul>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header bg-light">
                <strong><i class="fas fa-user me-1"></i> Utilisateur ciblé</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Nom complet</small>
                        <strong>{{ $employee->noms }} {{ $employee->prenoms }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Matricule</small>
                        <strong>{{ $employee->matricule ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mt-2">
                        <small class="text-muted d-block">Email destinataire</small>
                        <strong>{{ $employee->user->email }}</strong>
                    </div>
                    <div class="col-md-6 mt-2">
                        <small class="text-muted d-block">Dernier changement</small>
                        <strong>{{ $employee->user->password_changed_at?->translatedFormat('d M Y H:i') ?? 'Jamais (compte initial)' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('rh.employees.reset-password.action', $employee) }}" method="POST" autocomplete="off">
            @csrf
            <div class="card data-card">
                <div class="card-header">
                    <strong><i class="fas fa-key me-1"></i> Choix du mot de passe</strong>
                </div>
                <div class="card-body">

                    {{-- Mode --}}
                    <div class="mb-3">
                        <label class="form-label">Mode de génération <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" value="auto" id="mode-auto" {{ old('mode', 'auto') === 'auto' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode-auto">
                                <strong>Généré automatiquement</strong> <span class="badge bg-success ms-1">Recommandé</span>
                                <br><small class="text-muted">Mot de passe fort de 16 caractères, généré aléatoirement.</small>
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="mode" value="manuel" id="mode-manuel" {{ old('mode') === 'manuel' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode-manuel">
                                <strong>Saisir manuellement</strong>
                                <br><small class="text-muted">Vous choisissez un mot de passe (8 caractères min., majuscules + minuscules + chiffres).</small>
                            </label>
                        </div>
                        @error('mode')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Champs manuels (visibles seulement si mode=manuel) --}}
                    <div id="manuelFields" class="{{ old('mode') === 'manuel' ? '' : 'd-none' }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" minlength="8"
                                           class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" tabindex="-1" data-toggle-pwd="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">8 car. min · maj + min + chiffres</small>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirmer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" minlength="8"
                                           class="form-control" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" tabindex="-1" data-toggle-pwd="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-header border-top">
                    <strong><i class="fas fa-lock me-1"></i> Confirmation administrateur</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif <small class="text-muted">(facultatif, tracé dans l'audit)</small></label>
                        <input type="text" name="motif" id="motif" class="form-control"
                               placeholder="Ex : oubli signalé, départ collaborateur, compte compromis…"
                               maxlength="255" value="{{ old('motif') }}">
                    </div>

                    <div class="mb-3">
                        <label for="admin_password" class="form-label">
                            Votre mot de passe <span class="text-danger">*</span>
                            <small class="text-muted">(pour confirmer votre identité)</small>
                        </label>
                        <div class="input-group">
                            <input type="password" name="admin_password" id="admin_password"
                                   class="form-control @error('admin_password') is-invalid @enderror"
                                   required autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary" tabindex="-1"
                                    data-toggle-pwd="admin_password" aria-label="Afficher / masquer le mot de passe" title="Afficher / masquer">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-check mb-0">
                        <input type="checkbox" name="confirmation" id="confirmation" value="1"
                               class="form-check-input @error('confirmation') is-invalid @enderror" required>
                        <label for="confirmation" class="form-check-label">
                            Je confirme avoir vérifié l'identité de l'utilisateur. Je comprends que le mot de passe sera envoyé par email
                            à <strong>{{ $employee->user->email }}</strong> et que l'utilisateur devra le changer immédiatement.
                        </label>
                        @error('confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('rh.employees.show', $employee) }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i> Réinitialiser et envoyer par email
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
    (function () {
        const radios = document.querySelectorAll('input[name="mode"]');
        const manuelFields = document.getElementById('manuelFields');
        const pwdInput = document.getElementById('password');
        const pwdConfirm = document.getElementById('password_confirmation');

        function refresh() {
            const mode = document.querySelector('input[name="mode"]:checked')?.value;
            if (mode === 'manuel') {
                manuelFields.classList.remove('d-none');
                pwdInput?.setAttribute('required', 'required');
                pwdConfirm?.setAttribute('required', 'required');
            } else {
                manuelFields.classList.add('d-none');
                pwdInput?.removeAttribute('required');
                pwdConfirm?.removeAttribute('required');
                if (pwdInput) pwdInput.value = '';
                if (pwdConfirm) pwdConfirm.value = '';
            }
        }
        radios.forEach(r => r.addEventListener('change', refresh));
        refresh();

        // Toggle œil pour chaque champ password
        document.querySelectorAll('[data-toggle-pwd]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-toggle-pwd');
                const input = document.getElementById(id);
                if (!input) return;
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                btn.querySelector('i')?.classList.toggle('fa-eye', showing);
                btn.querySelector('i')?.classList.toggle('fa-eye-slash', !showing);
            });
        });
    })();
</script>
@endpush
@endsection
