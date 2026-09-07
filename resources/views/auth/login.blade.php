@extends('layouts.auth')

@section('title', 'Connexion')

@push('styles')
<style>
    :root {
        --brand-teal: #0D9488;
        --brand-teal-dark: #0F766E;
        --brand-emerald-deep: #064E3B;
        --brand-slate-900: #0F172A;
        --brand-slate-600: #475569;
        --brand-slate-400: #94A3B8;
        --brand-mint-50: #F0FDFA;
        --brand-mint-100: #CCFBF1;
        --brand-mint-200: #A7F3D0;
        --brand-border: #E2E8F0;
    }

    .auth-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1.15fr 1fr;
    }

    /* ═══ PANNEAU GAUCHE — Brand ═══ */
    .auth-brand {
        position: relative;
        overflow: hidden;
        color: #fff;
        padding: 3rem 3.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background:
            radial-gradient(circle at 25% 15%, rgba(20, 184, 166, 0.35) 0%, transparent 45%),
            radial-gradient(circle at 85% 80%, rgba(16, 185, 129, 0.25) 0%, transparent 55%),
            linear-gradient(135deg, #0D9488 0%, #0F766E 35%, #064E3B 100%);
    }

    /* Grid subtil overlay */
    .auth-brand::before {
        content: "";
        position: absolute; inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 48px 48px;
        pointer-events: none;
    }

    /* Blob décoratif bas-droite */
    .auth-brand::after {
        content: "";
        position: absolute;
        bottom: -120px; right: -120px;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(167, 243, 208, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .auth-brand > * { position: relative; z-index: 1; }

    .brand-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: -0.02em;
    }

    .brand-mark {
        width: 44px; height: 44px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 12px;
        display: grid; place-items: center;
        backdrop-filter: blur(8px);
        animation: pulse-mark 3s ease-in-out infinite;
    }

    .brand-mark i { font-size: 1.35rem; }

    @keyframes pulse-mark {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255,255,255,0.2); }
        50%      { transform: scale(1.04); box-shadow: 0 0 0 12px rgba(255,255,255,0); }
    }

    .brand-hero {
        max-width: 480px;
    }

    .brand-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.8rem;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(8px);
    }

    .brand-title {
        font-size: 2.75rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 1rem;
    }

    .brand-title em {
        font-style: normal;
        background: linear-gradient(135deg, #A7F3D0 0%, #CCFBF1 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .brand-subtitle {
        font-size: 1.05rem;
        color: rgba(240, 253, 250, 0.75);
        line-height: 1.6;
        margin-bottom: 2.5rem;
    }

    .brand-features {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .brand-feature {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.85rem 1.1rem;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px;
        backdrop-filter: blur(8px);
        opacity: 0;
        transform: translateY(8px);
        animation: fade-up 0.6s ease-out forwards;
    }
    .brand-feature:nth-child(1) { animation-delay: 0.15s; }
    .brand-feature:nth-child(2) { animation-delay: 0.30s; }
    .brand-feature:nth-child(3) { animation-delay: 0.45s; }

    @keyframes fade-up {
        to { opacity: 1; transform: translateY(0); }
    }

    .feature-icon {
        width: 38px; height: 38px;
        display: grid; place-items: center;
        background: rgba(255,255,255,0.15);
        border-radius: 9px;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .feature-text strong {
        display: block;
        font-size: 0.92rem;
        font-weight: 600;
        color: #fff;
    }
    .feature-text span {
        font-size: 0.8rem;
        color: rgba(240, 253, 250, 0.65);
    }

    .brand-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.78rem;
        color: rgba(240, 253, 250, 0.55);
    }

    .brand-footer .signature {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .brand-footer .dot-live {
        width: 8px; height: 8px;
        background: #34D399;
        border-radius: 50%;
        box-shadow: 0 0 8px #34D399;
        animation: pulse-dot 1.8s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%      { opacity: 0.4; }
    }

    /* ═══ PANNEAU DROIT — Form ═══ */
    .auth-form-wrap {
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 2.5rem;
        overflow-y: auto;
    }

    .auth-form {
        width: 100%;
        max-width: 400px;
    }

    .form-title {
        font-size: 1.85rem;
        font-weight: 700;
        color: var(--brand-slate-900);
        letter-spacing: -0.02em;
        margin-bottom: 0.35rem;
    }

    .form-lead {
        font-size: 0.95rem;
        color: var(--brand-slate-600);
        margin-bottom: 2rem;
    }

    .alert-inline {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        font-size: 0.85rem;
        margin-bottom: 1.25rem;
    }
    .alert-inline.ok  { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .alert-inline.err { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    .alert-inline i { margin-top: 0.15rem; }

    .field-group { margin-bottom: 1.15rem; }

    .field-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--brand-slate-900);
        margin-bottom: 0.5rem;
    }

    .field-input {
        position: relative;
    }

    .field-input input {
        width: 100%;
        padding: 0.85rem 1rem 0.85rem 2.75rem;
        font-size: 0.95rem;
        border: 1.5px solid var(--brand-border);
        border-radius: 10px;
        background: #fff;
        color: var(--brand-slate-900);
        transition: border-color 0.15s, box-shadow 0.15s;
        font-family: inherit;
    }

    .field-input input:focus {
        outline: none;
        border-color: var(--brand-teal);
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.12);
    }

    .field-input input.has-error {
        border-color: #DC2626;
    }
    .field-input input.has-error:focus {
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
    }

    .field-input .field-icon {
        position: absolute;
        left: 1rem; top: 50%;
        transform: translateY(-50%);
        color: var(--brand-slate-400);
        font-size: 0.95rem;
        pointer-events: none;
    }

    .field-input .toggle-pw {
        position: absolute;
        right: 0.6rem; top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--brand-slate-400);
        padding: 0.35rem 0.55rem;
        cursor: pointer;
        border-radius: 6px;
    }
    .field-input .toggle-pw:hover { color: var(--brand-slate-600); background: #F1F5F9; }

    .field-error {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.78rem;
        color: #DC2626;
    }

    .field-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .remember {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--brand-slate-600);
        cursor: pointer;
        user-select: none;
    }

    .remember input {
        width: 16px; height: 16px;
        accent-color: var(--brand-teal);
        cursor: pointer;
    }

    .link-muted {
        font-size: 0.83rem;
        color: var(--brand-teal-dark);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s;
    }
    .link-muted:hover { color: var(--brand-slate-900); }

    .btn-submit {
        width: 100%;
        padding: 0.9rem 1rem;
        background: var(--brand-teal);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        cursor: pointer;
        transition: transform 0.1s, box-shadow 0.15s, background 0.15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
    }
    .btn-submit:hover {
        background: var(--brand-teal-dark);
        box-shadow: 0 8px 20px -6px rgba(13, 148, 136, 0.4);
    }
    .btn-submit:active { transform: translateY(1px); }

    .divider {
        display: flex;
        align-items: center;
        margin: 1.75rem 0 1.25rem;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--brand-slate-400);
    }
    .divider::before, .divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--brand-border);
    }
    .divider span { padding: 0 0.9rem; }

    .discover-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 1.5px solid var(--brand-border);
        border-radius: 10px;
        color: var(--brand-slate-900);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: all 0.15s;
    }
    .discover-link:hover {
        border-color: var(--brand-teal);
        color: var(--brand-teal-dark);
        background: var(--brand-mint-50);
    }

    .form-footer {
        margin-top: 2rem;
        text-align: center;
        font-size: 0.75rem;
        color: var(--brand-slate-400);
    }

    /* ═══ MOBILE ═══ */
    @media (max-width: 991.98px) {
        .auth-shell {
            grid-template-columns: 1fr;
        }
        .auth-brand {
            padding: 2rem 1.5rem;
            min-height: auto;
        }
        .brand-hero { display: none; }
        .brand-features { display: none; }
        .brand-footer { display: none; }
        .brand-header { justify-content: center; }
        .auth-form-wrap {
            padding: 2rem 1.25rem;
        }
    }

    @media (max-width: 480px) {
        .form-title { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="auth-shell">

    {{-- ─── PANNEAU GAUCHE : BRAND ─── --}}
    <aside class="auth-brand">
        <div class="brand-header">
            <div class="brand-mark"><i class="fas fa-cube"></i></div>
            <span>OptimiZe</span>
        </div>

        <div class="brand-hero">
            <span class="brand-eyebrow">
                <i class="fas fa-sparkles"></i> ERP intégré · Version 2
            </span>
            <h1 class="brand-title">
                L'ERP qui <em>unifie</em> tous vos métiers.
            </h1>
            <p class="brand-subtitle">
                Finance, RH, Achats, Projet, Objectifs, Intranet — une seule plateforme,
                pensée pour les organisations qui veulent piloter au réel.
            </p>

            <div class="brand-features">
                <div class="brand-feature">
                    <div class="feature-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="feature-text">
                        <strong>7 modules intégrés</strong>
                        <span>Une donnée saisie une fois, disponible partout</span>
                    </div>
                </div>
                <div class="brand-feature">
                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                    <div class="feature-text">
                        <strong>Temps réel</strong>
                        <span>Dashboards, cascades d'avancement, alertes vivantes</span>
                    </div>
                </div>
                <div class="brand-feature">
                    <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                    <div class="feature-text">
                        <strong>Sécurisé & souverain</strong>
                        <span>Hébergement contrôlé, rôles fins, journal d'activité</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-footer">
            <span>&copy; {{ date('Y') }} Yubile Technologie</span>
            <span class="signature">
                <span class="dot-live"></span>
                Système opérationnel
            </span>
        </div>
    </aside>

    {{-- ─── PANNEAU DROIT : FORM ─── --}}
    <main class="auth-form-wrap">
        <div class="auth-form">
            <h2 class="form-title">Bon retour</h2>
            <p class="form-lead">Connectez-vous à votre espace OptimiZe.</p>

            @if(session('status'))
                <div class="alert-inline ok">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div class="alert-inline err">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="field-group">
                    <label class="field-label" for="email">Adresse e-mail</label>
                    <div class="field-input">
                        <i class="fas fa-envelope field-icon"></i>
                        <input id="email" type="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="vous@organisation.com"
                               autocomplete="email"
                               autofocus required
                               class="{{ $errors->has('email') ? 'has-error' : '' }}">
                    </div>
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Mot de passe</label>
                    <div class="field-input">
                        <i class="fas fa-lock field-icon"></i>
                        <input id="password" type="password" name="password"
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required
                               class="{{ $errors->has('password') ? 'has-error' : '' }}">
                        <button type="button" class="toggle-pw" onclick="togglePwd()" aria-label="Afficher le mot de passe">
                            <i class="fas fa-eye" id="pwEye"></i>
                        </button>
                    </div>
                    @error('password') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-row">
                    <label class="remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Se souvenir de moi</span>
                    </label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-muted">Mot de passe oublié ?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    Se connecter
                </button>
            </form>

            <div class="divider"><span>Nouveau chez nous ?</span></div>

            <a href="{{ route('vitrine.public') }}" class="discover-link">
                <i class="fas fa-compass"></i>
                Découvrir OptimiZe
            </a>

            <p class="form-footer">
                &copy; {{ date('Y') }} OptimiZe ERP · Tous droits réservés
            </p>
        </div>
    </main>
</div>

<script>
    function togglePwd() {
        const input = document.getElementById('password');
        const eye   = document.getElementById('pwEye');
        const isPw  = input.type === 'password';
        input.type  = isPw ? 'text' : 'password';
        eye.classList.toggle('fa-eye', !isPw);
        eye.classList.toggle('fa-eye-slash', isPw);
    }
</script>
@endsection
