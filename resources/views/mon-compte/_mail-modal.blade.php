{{-- Modale d'ajout ($compte = null) ou de modification d'un compte mail --}}
@php
    $id = $compte ? 'edit-'.$compte->id : 'create';
    // Valeurs ressoumises seulement pour la modale qui a échoué
    $estCible = session('mail_modal') === $id
        || ($errors->any() && (string) old('_compte_id', '') === (string) ($compte?->id ?? ''));
    $v = fn(string $champ, $defaut = null) => $estCible ? old($champ, $compte?->$champ ?? $defaut) : ($compte?->$champ ?? $defaut);
@endphp
<div class="modal fade" id="mailModal-{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ $compte ? route('mon-compte.mails.update', $compte) : route('mon-compte.mails.store') }}" method="POST" class="modal-content" data-mail-form autocomplete="off">
            @csrf
            @if($compte) @method('PUT') <input type="hidden" name="_compte_id" value="{{ $compte->id }}"> @endif
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-at me-2"></i>{{ $compte ? 'Modifier '.$compte->email : 'Connecter un compte mail' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($estCible && (session('error') || $errors->any()))
                    <div class="alert alert-danger" style="font-size:.85rem;">
                        {{ session('error') }}
                        @if($errors->any())<ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>@endif
                    </div>
                @endif

                @unless($compte)
                <label class="form-label">Fournisseur</label>
                <div class="mc-presets mb-2">
                    @foreach($presets as $cle => $p)
                        <button type="button" class="mc-preset" data-preset="{{ $cle }}">{{ $p['label'] }}</button>
                    @endforeach
                </div>
                <div class="alert alert-info py-2 d-none" style="font-size:.8rem;" data-preset-aide></div>
                @endunless

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Adresse email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ $v('email') }}" required maxlength="150" placeholder="prenom.nom@entreprise.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom affiché à l'envoi</label>
                        <input type="text" name="nom_expediteur" class="form-control" value="{{ $v('nom_expediteur') }}" maxlength="150" placeholder="{{ trim(auth()->user()->prenoms.' '.auth()->user()->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Identifiant <span class="text-danger">*</span></label>
                        <input type="text" name="identifiant" class="form-control" value="{{ $v('identifiant') }}" required maxlength="255" @if($compte) data-touche="1" @endif>
                        <div class="form-text">Souvent identique à l'adresse email.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe @unless($compte)<span class="text-danger">*</span>@endunless</label>
                        <input type="password" name="mot_de_passe" class="form-control" {{ $compte ? '' : 'required' }} maxlength="500" autocomplete="new-password" placeholder="{{ $compte ? 'Inchangé si vide' : '' }}">
                    </div>
                </div>

                <details class="mt-3" data-serveurs {{ $compte || $estCible ? 'open' : '' }}>
                    <summary class="text-muted" style="font-size:.85rem; cursor:pointer;">Paramètres des serveurs (IMAP / SMTP)</summary>
                    <div class="row g-3 mt-1">
                        <div class="col-12"><div class="fw-semibold" style="font-size:.8rem;">Réception — IMAP</div></div>
                        <div class="col-md-6"><label class="form-label">Serveur</label><input type="text" name="serveur_entrant" class="form-control" value="{{ $v('serveur_entrant') }}" required maxlength="255" placeholder="imap.entreprise.com"></div>
                        <div class="col-md-3"><label class="form-label">Chiffrement</label>
                            <select name="ssl_entrant" class="form-select"><option value="1" @selected($v('ssl_entrant', true))>SSL / TLS</option><option value="0" @selected(!$v('ssl_entrant', true))>STARTTLS</option></select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Port</label><input type="number" name="port_entrant" class="form-control" value="{{ $v('port_entrant', 993) }}" required></div>

                        <div class="col-12"><div class="fw-semibold" style="font-size:.8rem;">Envoi — SMTP</div></div>
                        <div class="col-md-6"><label class="form-label">Serveur</label><input type="text" name="serveur_sortant" class="form-control" value="{{ $v('serveur_sortant') }}" required maxlength="255" placeholder="smtp.entreprise.com"></div>
                        <div class="col-md-3"><label class="form-label">Chiffrement</label>
                            <select name="ssl_sortant" class="form-select"><option value="1" @selected($v('ssl_sortant', false))>SSL / TLS</option><option value="0" @selected(!$v('ssl_sortant', false))>STARTTLS</option></select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Port</label><input type="number" name="port_sortant" class="form-control" value="{{ $v('port_sortant', 587) }}" required></div>
                    </div>
                </details>

                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="is_default" value="0">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="mailDefaut-{{ $id }}" @checked($v('is_default', false))>
                    <label class="form-check-label" for="mailDefaut-{{ $id }}">Compte par défaut (ouvert en premier dans « Mails pro »)</label>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted me-auto" style="font-size:.75rem;"><i class="fas fa-plug-circle-check me-1"></i>La connexion est testée avant l'enregistrement.</span>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">{{ $compte ? 'Enregistrer' : 'Connecter' }}</button>
            </div>
        </form>
    </div>
</div>
