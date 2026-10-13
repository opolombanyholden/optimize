{{-- Mails liés à une entité (projet, tâche, courrier, opportunité, dépense…).
     Les mails confidentiels ne sont visibles que par leur propriétaire ; seul le propriétaire peut ouvrir le mail.
     Paramètre : $entite (modèle Eloquent liable). --}}
@php
    $mailsLies = \App\Services\Mail\MailLiens::mailsLies($entite, auth()->user());
    $typeEntite = \App\Services\Mail\MailLiens::typeDe($entite);
    $peutEcrire = $typeEntite && auth()->user()->comptesMail()->exists();
    $P = \App\Models\Intranet\MailSuivi::PRIORITES;
@endphp
<div class="card data-card mt-3 mails-lies">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="fas fa-envelope-open-text me-2 text-muted"></i>Mails liés @if($mailsLies->count())<span class="text-muted fw-normal" style="font-size:.8rem;">({{ $mailsLies->count() }})</span>@endif</h5>
        @if($peutEcrire)
            <a href="{{ route('intranet.mails.create', ['lien' => [$typeEntite.':'.$entite->getKey()]]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen me-1"></i>Écrire un mail lié</a>
        @endif
    </div>
    @forelse($mailsLies as $s)
        @php
            $pr = $P[$s->priorite];
            $estMien = $s->user_id === auth()->id();
            $url = $estMien && $s->uid ? route('intranet.mails.show', ['compte' => $s->compte_id, 'uid' => $s->uid, 'dossier' => $s->dossier ?? 'INBOX']) : null;
        @endphp
        <div class="ml-item">
            <span class="ml-prio" style="background:{{ $pr['fond'] }}; color:{{ $pr['couleur'] }};" title="Priorité {{ mb_strtolower($pr['libelle']) }}"><i class="fas {{ $pr['icone'] }}"></i></span>
            <div class="min-w-0 flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    @if($url)<a href="{{ $url }}" class="ml-sujet">{{ $s->sujet }}</a>@else<span class="ml-sujet">{{ $s->sujet }}</span>@endif
                    @if($s->categorie)<span class="ml-tag">{{ $s->categorie }}</span>@endif
                    @if($s->statut)<span class="ml-tag {{ $s->statut === 'traite' ? 'ok' : '' }}">{{ \App\Models\Intranet\MailSuivi::STATUTS[$s->statut] }}</span>@endif
                    @if($s->echeance && $s->statut !== 'traite')<span class="ml-tag {{ $s->enRetard() ? 'retard' : '' }}"><i class="fas fa-calendar-day me-1"></i>{{ $s->echeance->translatedFormat('j M Y') }}</span>@endif
                    @if($s->confidentiel)<span class="ml-tag" title="Visible par vous seul"><i class="fas fa-user-lock"></i></span>@endif
                </div>
                <div class="ml-meta">
                    <i class="fas {{ $s->sens === 'envoye' ? 'fa-paper-plane' : 'fa-inbox' }} me-1"></i>{{ $s->sens === 'envoye' ? 'Envoyé à' : 'Reçu de' }} {{ $s->correspondant ?: '—' }}
                    · {{ $s->date_mail?->translatedFormat('j M Y, H:i') }}
                    · classé par {{ $estMien ? 'vous' : trim($s->utilisateur?->prenoms.' '.$s->utilisateur?->name) }}
                </div>
                @if($s->note)<div class="ml-note">{{ $s->note }}</div>@endif
            </div>
        </div>
    @empty
        <div class="text-muted px-3 py-3" style="font-size:.85rem;">Aucun mail lié. Depuis la messagerie, utilisez « Classer / lier » sur un mail, ou « Classement &amp; liens » lors de la rédaction.</div>
    @endforelse
</div>

@once
@push('styles')
<style>
.mails-lies .ml-item { display:flex; gap:.75rem; align-items:flex-start; padding:.7rem 1.1rem; border-top:1px solid #F1F5F9; }
.mails-lies .ml-prio { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.mails-lies .ml-sujet { font-weight:600; color:#0F172A; font-size:.88rem; text-decoration:none; }
.mails-lies a.ml-sujet:hover { color:#4338CA; }
.mails-lies .ml-meta { font-size:.75rem; color:#64748B; margin-top:.1rem; }
.mails-lies .ml-tag { font-size:.68rem; background:#F1F5F9; color:#475569; border-radius:999px; padding:.05rem .5rem; }
.mails-lies .ml-tag.retard { background:#FEE2E2; color:#B91C1C; }
.mails-lies .ml-tag.ok { background:#DCFCE7; color:#166534; }
.mails-lies .ml-note { font-size:.78rem; color:#78350F; background:#FFFBEB; border-radius:4px; padding:.3rem .55rem; margin-top:.35rem; white-space:pre-line; }
</style>
@endpush
@endonce
