@extends('layouts.app')

@section('title', 'Employe - ' . $employee->noms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.index') }}">Employes</a></li>
        <li class="breadcrumb-item active">{{ $employee->noms }} {{ $employee->prenoms }}</li>
    </ol>
@endsection

@section('content')
@php
    $photoSrc = $employee->user?->profile_photo_path
        ? asset('storage/' . $employee->user->profile_photo_path)
        : asset('img/default-avatar.svg');
    // One-time display d'un mot de passe temporaire post-reset.
    // session()->pull() consomme la valeur : pas de re-affichage au refresh.
    $resetOtp = session()->pull('reset_password_otp');
    if ($resetOtp && (int) ($resetOtp['employee_id'] ?? 0) !== (int) $employee->id) {
        $resetOtp = null; // sécurité : ne pas afficher si l'employé ne correspond pas
    }
@endphp

@if($resetOtp)
<div class="alert alert-danger border-danger d-flex align-items-start gap-3" role="alert" id="resetOtpBanner">
    <i class="fas fa-triangle-exclamation fs-3 text-danger"></i>
    <div class="flex-grow-1">
        <h6 class="alert-heading mb-1">
            <i class="fas fa-envelope-circle-check me-1"></i>
            Email non envoyé — affichage de secours pour {{ $resetOtp['employee_name'] }}
        </h6>
        <p class="mb-2 small">
            L'envoi par email a échoué. Le mot de passe a tout de même été réinitialisé.
            Communiquez-le à l'utilisateur <strong>par un canal sécurisé</strong>
            (en personne, téléphone, message chiffré). Il devra le changer à sa prochaine connexion.
            <strong>Ce mot de passe ne sera plus affiché ensuite.</strong>
        </p>
        <div class="input-group" style="max-width: 480px;">
            <input type="text" id="otpField" class="form-control font-monospace fw-bold" readonly
                   value="{{ $resetOtp['temp_password'] }}" style="letter-spacing:.1em;">
            <button type="button" class="btn btn-outline-secondary" id="otpCopy" title="Copier">
                <i class="fas fa-copy"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" id="otpDismiss" title="Effacer maintenant">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
        <small class="text-muted d-block mt-2">
            Email du compte : <strong>{{ $resetOtp['email'] }}</strong> ·
            Généré le {{ \Carbon\Carbon::parse($resetOtp['generated_at'])->translatedFormat('d M Y H:i:s') }}
        </small>
    </div>
</div>
@push('scripts')
<script>
    (function () {
        const banner = document.getElementById('resetOtpBanner');
        const field = document.getElementById('otpField');
        const copyBtn = document.getElementById('otpCopy');
        const dismissBtn = document.getElementById('otpDismiss');
        if (!banner) return;
        copyBtn?.addEventListener('click', async () => {
            try { await navigator.clipboard.writeText(field.value); copyBtn.classList.add('btn-success'); copyBtn.classList.remove('btn-outline-secondary'); setTimeout(() => { copyBtn.classList.remove('btn-success'); copyBtn.classList.add('btn-outline-secondary'); }, 1200); } catch (e) {}
        });
        dismissBtn?.addEventListener('click', () => { field.value = ''; banner.remove(); });
        // Auto-masquage défensif après 10 minutes
        setTimeout(() => { field.value = ''; banner.remove(); }, 10 * 60 * 1000);
    })();
</script>
@endpush
@endif
<div class="page-header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $photoSrc }}" alt="Photo"
             style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #E2E8F0;background:#F8FAFC;">
        <div>
            <h1 class="mb-0">{{ $employee->noms }} {{ $employee->prenoms }}</h1>
            <p class="text-muted mb-0">
                {{ $employee->poste ?? 'Poste non defini' }}
                @if($employee->departement) &mdash; {{ $employee->departement }} @endif
                @if($employee->user)
                    · <i class="fas fa-envelope text-muted" style="font-size:.75rem;"></i>
                    <span style="font-size:.82rem;">{{ $employee->user->email }}</span>
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.employees.edit', $employee) }}" class="btn btn-primary">
            <i class="fas fa-pen me-1"></i>Modifier
        </a>
        <a href="{{ route('rh.employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Identite --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-id-card me-2 text-muted"></i>Identite</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Matricule</td>
                        <td><strong>{{ $employee->matricule ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nom complet</td>
                        <td>{{ $employee->noms }} {{ $employee->prenoms }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date de naissance</td>
                        <td>{{ $employee->date_naissance?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lieu de naissance</td>
                        <td>{{ $employee->lieu_naissance ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nationalite</td>
                        <td>{{ $employee->nationalite ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sexe</td>
                        <td>{{ $employee->sexe == 'M' ? 'Masculin' : ($employee->sexe == 'F' ? 'Feminin' : '-') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Situation matrimoniale</td>
                        <td>{{ $employee->situation_matrimoniale ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nombre d'enfants</td>
                        <td>{{ $employee->nombre_enfants ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($employee->statut == 1)
                                <span class="badge badge-status badge-actif">Actif</span>
                            @elseif($employee->statut == 2)
                                <span class="badge badge-status badge-inactif">Inactif</span>
                            @elseif($employee->statut == 3)
                                <span class="badge badge-status badge-en-attente">Suspendu</span>
                            @else
                                <span class="badge badge-status badge-brouillon">Non defini</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-address-book me-2 text-muted"></i>Contact</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Email</td>
                        <td>{{ $employee->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telephone</td>
                        <td>{{ $employee->contact ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Adresse</td>
                        <td>{{ $employee->adresse ?? '-' }}</td>
                    </tr>
                    @php
                        $hasLoc = $employee->pays || $employee->province || $employee->departement_geo
                              || $employee->prefecture || $employee->sous_prefecture || $employee->zone_type;
                    @endphp
                    @if($hasLoc)
                        <tr><td colspan="2" class="pt-3"><strong class="text-muted"><i class="fas fa-map-location-dot me-1"></i> Localisation administrative</strong></td></tr>
                        @if($employee->pays)<tr><td class="text-muted">Pays</td><td>{{ $employee->pays }}</td></tr>@endif
                        @if($employee->province)<tr><td class="text-muted">Province</td><td>{{ $employee->province }}</td></tr>@endif
                        @if($employee->departement_geo)<tr><td class="text-muted">Département</td><td>{{ $employee->departement_geo }}</td></tr>@endif
                        @if($employee->prefecture)<tr><td class="text-muted">Préfecture</td><td>{{ $employee->prefecture }}</td></tr>@endif
                        @if($employee->sous_prefecture)<tr><td class="text-muted">Sous-préfecture</td><td>{{ $employee->sous_prefecture }}</td></tr>@endif
                        @if($employee->zone_type)
                            <tr><td class="text-muted">Zone</td><td>
                                @if($employee->zone_type === 'urbaine')
                                    <span class="badge bg-primary"><i class="fas fa-city me-1"></i> Urbaine</span>
                                @else
                                    <span class="badge bg-success"><i class="fas fa-tree me-1"></i> Rurale</span>
                                @endif
                            </td></tr>
                        @endif
                        @if($employee->zone_type === 'urbaine')
                            @if($employee->commune)<tr><td class="text-muted">Commune</td><td>{{ $employee->commune }}</td></tr>@endif
                            @if($employee->arrondissement)<tr><td class="text-muted">Arrondissement</td><td>{{ $employee->arrondissement }}</td></tr>@endif
                            @if($employee->quartier_loc)<tr><td class="text-muted">Quartier</td><td>{{ $employee->quartier_loc }}</td></tr>@endif
                        @elseif($employee->zone_type === 'rurale')
                            @if($employee->canton)<tr><td class="text-muted">Canton</td><td>{{ $employee->canton }}</td></tr>@endif
                            @if($employee->regroupement_village)<tr><td class="text-muted">Regroupement de village</td><td>{{ $employee->regroupement_village }}</td></tr>@endif
                            @if($employee->village)<tr><td class="text-muted">Village</td><td>{{ $employee->village }}</td></tr>@endif
                        @endif
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Emploi --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-briefcase me-2 text-muted"></i>Emploi</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Date d'embauche</td>
                        <td>{{ $employee->date_embauche?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type de contrat</td>
                        <td>{{ $employee->type_contrat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Poste</td>
                        <td>{{ $employee->poste ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Departement</td>
                        <td>{{ $employee->departement ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Supérieur hiérarchique</td>
                        <td>
                            @if($employee->superieur_poste_id)
                                @php $sa = $employee->superieurActuel(); @endphp
                                <i class="fas fa-briefcase me-1 text-muted small"></i>
                                <strong>{{ $employee->superieurPoste?->libelle }}</strong>
                                @if($sa)
                                    <br><small class="text-muted">Titulaire actuel : <a href="{{ route('rh.employees.show', $sa) }}">{{ $sa->noms }} {{ $sa->prenoms }}</a></small>
                                @else
                                    <br><small class="text-warning"><i class="fas fa-circle-exclamation me-1"></i> Poste vacant</small>
                                @endif
                            @elseif($employee->superieur)
                                {{ $employee->superieur->noms }} {{ $employee->superieur->prenoms }}
                                <br><small class="text-muted">(lien direct par personne — legacy)</small>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Remuneration --}}
    <div class="col-12 col-lg-6">
        <div class="card data-card h-100">
            <div class="card-header">
                <h5><i class="fas fa-money-bill-wave me-2 text-muted"></i>Remuneration</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Salaire de base</td>
                        <td><strong>{{ $employee->salaire_base ? number_format($employee->salaire_base, 0, ',', ' ') . ' F' : '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">IBAN</td>
                        <td>{{ $employee->iban ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">N. securite sociale</td>
                        <td>{{ $employee->numero_secu ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIP</td>
                        <td>
                            @if($employee->nip)
                                <code style="background:#F1F5F9;padding:.15rem .5rem;border-radius:4px;">{{ $employee->nip }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Onglets : Absences, Competences, Qualifications --}}
<div class="card data-card mt-4">
    <div class="card-header p-0">
        <ul class="nav nav-tabs card-header-tabs" id="employeeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="absences-tab" data-bs-toggle="tab" data-bs-target="#absences" type="button" role="tab">
                    <i class="fas fa-calendar-times me-1"></i>Absences
                    <span class="badge bg-secondary ms-1">{{ $employee->absences->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="competences-tab" data-bs-toggle="tab" data-bs-target="#competences" type="button" role="tab">
                    <i class="fas fa-star me-1"></i>Competences
                    <span class="badge bg-secondary ms-1">{{ $employee->competences->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="qualifications-tab" data-bs-toggle="tab" data-bs-target="#qualifications" type="button" role="tab">
                    <i class="fas fa-graduation-cap me-1"></i>Qualifications
                    <span class="badge bg-secondary ms-1">{{ $employee->qualifications->count() }}</span>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="employeeTabContent">
            {{-- Absences --}}
            <div class="tab-pane fade show active" id="absences" role="tabpanel">
                @if($employee->absences->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune absence enregistree</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Date debut</th>
                                    <th>Date fin</th>
                                    <th>Jours</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->absences as $absence)
                                <tr>
                                    <td>{{ $absence->type_abscence ?? '-' }}</td>
                                    <td>{{ $absence->debut?->format('d/m/Y') }}</td>
                                    <td>{{ $absence->fin?->format('d/m/Y') }}</td>
                                    <td>{{ $absence->duree_jours ?? '-' }}</td>
                                    <td>
                                        @if($absence->statut == 0)
                                            <span class="badge badge-status badge-en-attente">En attente</span>
                                        @elseif($absence->statut == 1)
                                            <span class="badge badge-status badge-valide">Approuve</span>
                                        @elseif($absence->statut == 2)
                                            <span class="badge badge-status badge-rejete">Rejete</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Competences --}}
            <div class="tab-pane fade" id="competences" role="tabpanel">
                @if($employee->competences->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune competence enregistree</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Competence</th>
                                    <th>Niveau</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->competences as $competence)
                                <tr>
                                    <td>{{ $competence->label ?? $competence->nom ?? '-' }}</td>
                                    <td>{{ $competence->niveau ?? '-' }}</td>
                                    <td>{{ $competence->description ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Qualifications --}}
            <div class="tab-pane fade" id="qualifications" role="tabpanel">
                @if($employee->qualifications->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune qualification enregistree</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Qualification</th>
                                    <th>Institution</th>
                                    <th>Annee</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->qualifications as $qualification)
                                <tr>
                                    <td>{{ $qualification->label ?? $qualification->titre ?? '-' }}</td>
                                    <td>{{ $qualification->institution ?? '-' }}</td>
                                    <td>{{ $qualification->annee ?? '-' }}</td>
                                    <td>{{ $qualification->description ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
