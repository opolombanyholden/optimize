@extends('layouts.app')

@section('title', 'Mon compte')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Mon compte</li>
</ol>
@endsection

@php
    // Préréglages des fournisseurs courants (remplissent les champs serveur du formulaire)
    $presets = [
        'gmail'   => ['label' => 'Gmail / Google Workspace', 'imap' => 'imap.gmail.com', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => 'smtp.gmail.com', 'smtp_port' => 465, 'smtp_ssl' => 1,
                      'aide' => "Activez la validation en 2 étapes puis créez un « mot de passe d'application » (Compte Google › Sécurité) : c'est lui qu'il faut saisir ici."],
        'outlook' => ['label' => 'Outlook / Microsoft 365', 'imap' => 'outlook.office365.com', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => 'smtp.office365.com', 'smtp_port' => 587, 'smtp_ssl' => 0,
                      'aide' => "L'accès IMAP/SMTP doit être autorisé par l'administrateur Microsoft 365 ; avec la double authentification, utilisez un mot de passe d'application."],
        'ovh'     => ['label' => 'OVH (MX Plan / Email Pro)', 'imap' => 'ssl0.ovh.net', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => 'ssl0.ovh.net', 'smtp_port' => 465, 'smtp_ssl' => 1, 'aide' => ''],
        'zoho'    => ['label' => 'Zoho Mail', 'imap' => 'imap.zoho.com', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => 'smtp.zoho.com', 'smtp_port' => 465, 'smtp_ssl' => 1, 'aide' => "L'accès IMAP doit être activé dans les paramètres Zoho Mail."],
        'yahoo'   => ['label' => 'Yahoo Mail', 'imap' => 'imap.mail.yahoo.com', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => 'smtp.mail.yahoo.com', 'smtp_port' => 465, 'smtp_ssl' => 1, 'aide' => "Créez un mot de passe d'application dans la sécurité du compte Yahoo."],
        'autre'   => ['label' => 'Autre / serveur de l\'entreprise', 'imap' => '', 'imap_port' => 993, 'imap_ssl' => 1, 'smtp' => '', 'smtp_port' => 587, 'smtp_ssl' => 0, 'aide' => 'Demandez les paramètres IMAP et SMTP à votre service informatique.'],
    ];
    $initiales = strtoupper(mb_substr($user->prenoms ?: $user->name, 0, 1).mb_substr($user->name, 0, 1));
@endphp

@push('styles')
<style>
.mc-avatar { width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#4F46E5,#7C3AED); color:#fff; font-size:1.5rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
.mc-info dt { font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:#94A3B8; font-weight:600; }
.mc-info dd { font-size:.88rem; color:#1E293B; margin-bottom:.75rem; }
.mc-mail { display:flex; gap:1rem; align-items:center; padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9; }
.mc-mail:last-child { border-bottom:0; }
.mc-mail-icon { width:42px; height:42px; border-radius:10px; background:#EEF2FF; color:#4F46E5; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.mc-mail-email { font-weight:600; color:#1E293B; font-size:.9rem; }
.mc-mail-meta { font-size:.75rem; color:#64748B; }
.mc-sig { padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9; }
.mc-sig:last-child { border-bottom:0; }
.mc-sig-apercu { border-left:3px solid #E2E8F0; padding:.35rem .85rem; font-size:.85rem; color:#334155; overflow:hidden; }
.mc-sig-apercu img { max-width:220px; height:auto; }
.mc-sig-apercu p { margin:0; }
.mc-presets { display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:.5rem; }
.mc-preset { border:1px solid #E2E8F0; border-radius:8px; padding:.55rem .7rem; font-size:.8rem; background:#fff; text-align:left; }
.mc-preset:hover { border-color:#A5B4FC; }
.mc-preset.active { border-color:#4F46E5; background:#EEF2FF; color:#3730A3; font-weight:600; }
@media (max-width: 575.98px) { .mc-mail { flex-wrap:wrap; } .mc-mail-actions { width:100%; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1>Mon compte</h1>
    <p class="text-muted mb-0" style="font-size:.85rem;">Vos informations et les comptes mail professionnels connectés à OptimiZe.</p>
</div>

<div class="row g-3">
    {{-- ══ Profil ══ --}}
    <div class="col-lg-4">
        <div class="card data-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="mc-avatar">{{ $initiales }}</div>
                    <div>
                        <div class="fw-bold" style="font-size:1.05rem; color:#1E293B;">{{ trim($user->prenoms.' '.$user->name) }}</div>
                        <div class="text-muted" style="font-size:.82rem;">{{ $user->poste ?: '—' }}</div>
                    </div>
                </div>
                <dl class="mc-info mb-0">
                    <dt>Email de connexion</dt><dd>{{ $user->email }}</dd>
                    <dt>Matricule</dt><dd>{{ $user->matricule ?: '—' }}</dd>
                    <dt>Contact</dt><dd>{{ $user->contact ?: '—' }}</dd>
                    <dt>Rôle(s)</dt><dd>{{ $user->roles->pluck('name')->implode(', ') ?: '—' }}</dd>
                </dl>
                <a href="{{ route('intranet.annuaire.collaborateurs.show', $user) }}" class="btn btn-sm btn-outline-secondary mt-2"><i class="fas fa-id-card me-1"></i>Voir ma fiche annuaire</a>
            </div>
        </div>
    </div>

    {{-- ══ Comptes mail ══ --}}
    <div class="col-lg-8" id="comptes-mail">
        <div class="card data-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5><i class="fas fa-at me-2 text-muted"></i>Comptes mail professionnels</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mailModal-create"><i class="fas fa-plus me-1"></i>Connecter un compte</button>
            </div>

            @forelse($comptes as $c)
                <div class="mc-mail">
                    <div class="mc-mail-icon"><i class="fas fa-envelope"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="mc-mail-email text-truncate">
                            {{ $c->email }}
                            @if($c->is_default)<span class="badge-status badge-valide ms-1">Par défaut</span>@endif
                        </div>
                        <div class="mc-mail-meta">
                            Envoi sous le nom « {{ $c->expediteur }} » · {{ $c->serveur_entrant }} / {{ $c->serveur_sortant }}
                        </div>
                        <div class="mc-mail-meta mt-1">
                            @if($c->derniere_erreur)
                                <span class="text-danger"><i class="fas fa-circle-exclamation me-1"></i>{{ $c->derniere_erreur }}</span>
                            @elseif($c->derniere_connexion_at)
                                <span class="text-success"><i class="fas fa-circle-check me-1"></i>Connecté — dernière synchronisation {{ $c->derniere_connexion_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mc-mail-actions d-flex gap-1 flex-shrink-0">
                        <a href="{{ route('intranet.mails.index', ['compte' => $c->id]) }}" class="btn btn-sm btn-outline-primary" title="Ouvrir la boîte"><i class="fas fa-inbox"></i></a>
                        <form action="{{ route('mon-compte.mails.tester', $c) }}" method="POST">@csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Tester la connexion"><i class="fas fa-plug-circle-check"></i></button>
                        </form>
                        @unless($c->is_default)
                        <form action="{{ route('mon-compte.mails.defaut', $c) }}" method="POST">@csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Définir par défaut"><i class="fas fa-star"></i></button>
                        </form>
                        @endunless
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#mailModal-edit-{{ $c->id }}" title="Modifier"><i class="fas fa-pen"></i></button>
                        <form action="{{ route('mon-compte.mails.destroy', $c) }}" method="POST" onsubmit="return confirm('Déconnecter {{ $c->email }} ? Les mails restent sur le serveur.');">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Déconnecter"><i class="fas fa-link-slash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-at"></i>
                    <p class="mb-3">Aucun compte mail connecté. Connectez votre messagerie professionnelle pour lire et envoyer vos mails depuis OptimiZe.</p>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mailModal-create"><i class="fas fa-plus me-1"></i>Connecter un compte</button>
                </div>
            @endforelse

            <div class="card-footer bg-white text-muted" style="font-size:.75rem;">
                <i class="fas fa-lock me-1"></i>Les mots de passe sont chiffrés en base. Les mails ne sont pas copiés : ils sont lus directement sur votre serveur de messagerie.
            </div>
        </div>

        {{-- ══ Signatures ══ --}}
        <div class="card data-card mt-3" id="signatures">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5><i class="fas fa-signature me-2 text-muted"></i>Signatures</h5>
                <div class="d-flex gap-2">
                    @if($modeles->isNotEmpty())
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-building me-1"></i>Modèles de l'entreprise</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach($modeles as $mod)
                                <li><form action="{{ route('mon-compte.signatures.modele', $mod) }}" method="POST">@csrf<button class="dropdown-item">{{ $mod->nom }}</button></form></li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sigModal-create"><i class="fas fa-plus me-1"></i>Nouvelle signature</button>
                </div>
            </div>
            @forelse($signatures as $sig)
                <div class="mc-sig">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-semibold" style="color:#1E293B; font-size:.9rem;">{{ $sig->nom }}</span>
                        @if($sig->par_defaut)<span class="badge-status badge-valide">Par défaut</span>@endif
                        @if($sig->modele_id)<span class="badge-status badge-brouillon" title="Mise à jour automatiquement quand l'administrateur modifie le modèle"><i class="fas fa-building me-1"></i>Modèle entreprise</span>@endif
                        @if($sig->estVerrouillee())<span class="badge-status badge-en-attente" title="Imposée par l'entreprise"><i class="fas fa-lock me-1"></i>Imposée</span>@endif
                        <div class="ms-auto d-flex gap-1">
                            @unless($sig->par_defaut)
                            <form action="{{ route('mon-compte.signatures.defaut', $sig) }}" method="POST">@csrf
                                <button class="btn btn-sm btn-outline-secondary" title="Définir par défaut"><i class="fas fa-star"></i></button>
                            </form>
                            @endunless
                            @unless($sig->modele_id)
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sigModal-edit-{{ $sig->id }}" title="Modifier"><i class="fas fa-pen"></i></button>
                            @endunless
                            @unless($sig->estVerrouillee())
                            <form action="{{ route('mon-compte.signatures.destroy', $sig) }}" method="POST" onsubmit="return confirm('Supprimer cette signature ?');">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                            @endunless
                        </div>
                    </div>
                    {{-- Contenu nettoyé par liste blanche à l'enregistrement (App\Support\HtmlSur) --}}
                    <div class="mc-sig-apercu">{!! $sig->html() !!}</div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-signature"></i>
                    <p class="mb-3">Aucune signature. Créez-en une ou plusieurs (professionnelle, courte, en anglais…) et choisissez-la au moment d'écrire.</p>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sigModal-create"><i class="fas fa-plus me-1"></i>Créer une signature</button>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ Modales ajout / modification ══ --}}
@include('mon-compte._mail-modal', ['compte' => null, 'presets' => $presets])
@foreach($comptes as $c)
    @include('mon-compte._mail-modal', ['compte' => $c, 'presets' => $presets])
@endforeach
@include('mon-compte._signature-modal', ['signature' => null])
@foreach($signatures->whereNull('modele_id') as $sig)
    @include('mon-compte._signature-modal', ['signature' => $sig])
@endforeach
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" integrity="sha384-VvSC4PGxeMkOaAmyuDGZECjY2dkqdO/IdBYBUK+BCYNc3WIvRxHLUzQ5OSgUaMA7" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js" integrity="sha384-hcxmSutM10NL6iGBAA0LStIhy+kWJxfrhqWVMRuABZH5Vqztexq2nBz/Xnfllly9" crossorigin="anonymous"></script>
<script>
// Éditeur de signature initialisé à l'ouverture de la modale (dimensions correctes pour Quill)
document.querySelectorAll('[data-sig-form]').forEach(function (form) {
    form.closest('.modal').addEventListener('shown.bs.modal', function () {
        window.initEditeurSignature(form.querySelector('[data-sig-editeur]'));
    });
});
</script>
<script>
(function () {
    const PRESETS = @json($presets);
    document.querySelectorAll('[data-mail-form]').forEach(function (form) {
        const champ = n => form.querySelector('[name="' + n + '"]');
        const aide = form.querySelector('[data-preset-aide]');
        form.querySelectorAll('.mc-preset').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const p = PRESETS[this.dataset.preset];
                form.querySelectorAll('.mc-preset').forEach(b => b.classList.toggle('active', b === this));
                champ('serveur_entrant').value = p.imap; champ('port_entrant').value = p.imap_port; champ('ssl_entrant').value = p.imap_ssl;
                champ('serveur_sortant').value = p.smtp; champ('port_sortant').value = p.smtp_port; champ('ssl_sortant').value = p.smtp_ssl;
                aide.textContent = p.aide; aide.classList.toggle('d-none', !p.aide);
                form.querySelector('[data-serveurs]').open = this.dataset.preset === 'autre';
            });
        });
        // L'identifiant reprend l'email tant qu'il n'a pas été modifié à la main
        const email = champ('email'), ident = champ('identifiant');
        email?.addEventListener('input', function () { if (!ident.dataset.touche) ident.value = this.value; });
        ident?.addEventListener('input', function () { this.dataset.touche = '1'; });
        // Ports usuels selon le chiffrement
        champ('ssl_entrant')?.addEventListener('change', function () { champ('port_entrant').value = this.value === '1' ? 993 : 143; });
        champ('ssl_sortant')?.addEventListener('change', function () { champ('port_sortant').value = this.value === '1' ? 465 : 587; });
        form.addEventListener('submit', function () {
            const b = form.querySelector('[type=submit]');
            b.disabled = true; b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Test de connexion…';
        });
    });

    // Rouvre la modale concernée après une erreur de validation / de connexion
    const aRouvrir = @json(session('mail_modal') ?? ($errors->any() ? (old('_compte_id') ? 'edit-'.old('_compte_id') : 'create') : null));
    if (aRouvrir) {
        const m = document.getElementById('mailModal-' + aRouvrir);
        if (m) bootstrap.Modal.getOrCreateInstance(m).show();
    }
    const sigARouvrir = @json(old('_signature_form'));
    if (sigARouvrir && @json($errors->any())) {
        const m = document.getElementById('sigModal-' + sigARouvrir);
        if (m) bootstrap.Modal.getOrCreateInstance(m).show();
    }
    if (location.hash === '#comptes-mail' && !@json($comptes->isNotEmpty()) && !aRouvrir) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mailModal-create')).show();
    }
})();
</script>
@endpush
