{{-- Historique des interactions CRM (contact, organisation, opportunité).
     Les mails journalisés depuis le webmail renvoient vers le message si le compte appartient à l'utilisateur.
     Paramètres : $interactions (collection CrmInteraction), $mailA (adresse pour « Envoyer un mail », optionnel),
                  $crmParam ([type => id] pour pré-cocher la fiche à l'envoi). --}}
@php
    $mesComptes = auth()->user()->comptesMail()->pluck('id')->all();
@endphp
<div class="contact-detail-card mt-3">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h4 class="contact-detail-card-title mb-0"><i class="fas fa-clock-rotate-left"></i> Historique des interactions</h4>
        @if(!empty($mailA))
            <a href="{{ route('intranet.mails.create', ['a' => $mailA] + ($crmParam ?? [])) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope me-1"></i>Envoyer un mail</a>
        @endif
    </div>
    @if($interactions->count())
        <div class="interactions-timeline">
            @foreach($interactions as $i)
            <div class="interaction-item">
                <div class="interaction-icon" style="background:{{ $i->couleur }};"><i class="fas {{ $i->type === 'email' ? ($i->mail_sens === 'envoye' ? 'fa-paper-plane' : 'fa-envelope-open-text') : $i->icone }}"></i></div>
                <div class="interaction-body">
                    <div class="interaction-header">
                        <strong>{{ $i->objet }}</strong>
                        <span class="text-muted small" title="{{ $i->date_interaction->format('d/m/Y H:i') }}">{{ $i->date_interaction->diffForHumans() }}</span>
                    </div>
                    @if($i->description)<div class="interaction-desc" style="white-space:pre-line;">{{ $i->description }}</div>@endif
                    <div class="interaction-meta">
                        <span class="badge-soft">{{ ucfirst($i->type) }}</span>
                        @if($i->realisateur) · par {{ trim($i->realisateur->prenoms.' '.$i->realisateur->name) }}@endif
                        @if($i->mail_uid && in_array($i->mail_compte_id, $mesComptes, true))
                            · <a href="{{ route('intranet.mails.show', ['compte' => $i->mail_compte_id, 'uid' => $i->mail_uid, 'dossier' => $i->mail_dossier]) }}"><i class="fas fa-arrow-up-right-from-square me-1"></i>Ouvrir le mail</a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <p class="text-muted mb-0" style="font-size:.85rem;">Aucune interaction pour l'instant. Les mails envoyés ou reçus peuvent être ajoutés ici depuis la messagerie.</p>
    @endif
</div>
