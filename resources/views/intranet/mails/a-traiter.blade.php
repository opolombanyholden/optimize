@extends('layouts.app')

@section('title', 'Mails à traiter')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mails.index', ['compte' => $compte->id]) }}">Mails professionnels</a></li>
    <li class="breadcrumb-item active">À traiter</li>
</ol>
@endsection

@include('intranet.mails._styles')

@push('styles')
<style>
.wt-item { display:grid; grid-template-columns:auto 1fr auto; gap:.85rem; align-items:flex-start; padding:.8rem 1rem; border-bottom:1px solid #F1F5F9; }
.wt-item:hover { background:#F8FAFC; }
.wt-prio { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; }
.wt-sujet { font-weight:600; color:#0F172A; text-decoration:none; }
.wt-sujet:hover { color:#4338CA; }
.wt-meta { font-size:.75rem; color:#64748B; }
.wt-liens { display:flex; flex-wrap:wrap; gap:.25rem; margin-top:.35rem; }
.wt-liens a { font-size:.72rem; background:#EEF2FF; color:#3730A3; border-radius:999px; padding:.05rem .5rem; text-decoration:none; }
.wt-note { font-size:.78rem; color:#78350F; background:#FFFBEB; border-radius:4px; padding:.25rem .5rem; margin-top:.35rem; white-space:pre-line; }
.wt-ech { font-size:.75rem; font-weight:600; text-align:right; white-space:nowrap; }
</style>
@endpush

@section('content')
<div class="wm-wrap">
    @include('intranet.mails._side')

    <section class="wm-main">
        <div class="wm-toolbar">
            <h1 class="h6 mb-0 me-auto fw-bold" style="color:#1E293B;"><i class="fas fa-list-check me-2" style="color:#4F46E5;"></i>À traiter
                <span class="text-muted fw-normal" style="font-size:.78rem;">· {{ $suivis->count() }} mail(s) · tous comptes · par échéance puis priorité</span>
            </h1>
        </div>

        @forelse($suivis as $s)
            @php
                $pr = \App\Models\Intranet\MailSuivi::PRIORITES[$s->priorite];
                $lien = $s->uid ? route('intranet.mails.show', ['compte' => $s->compte_id, 'uid' => $s->uid, 'dossier' => $s->dossier ?? 'INBOX']) : null;
            @endphp
            <div class="wt-item">
                <div class="wt-prio" style="background:{{ $pr['fond'] }}; color:{{ $pr['couleur'] }};" title="Priorité {{ mb_strtolower($pr['libelle']) }}"><i class="fas {{ $pr['icone'] }}"></i></div>
                <div class="min-w-0">
                    @if($lien)<a href="{{ $lien }}" class="wt-sujet">{{ $s->sujet }}</a>@else<span class="wt-sujet">{{ $s->sujet }}</span>@endif
                    <div class="wt-meta">
                        <i class="fas {{ $s->sens === 'envoye' ? 'fa-paper-plane' : 'fa-inbox' }} me-1"></i>{{ $s->sens === 'envoye' ? 'À' : 'De' }} {{ $s->correspondant ?: '—' }}
                        · {{ $s->date_mail?->translatedFormat('j M Y, H:i') }}
                        @if($comptes->count() > 1) · {{ $s->compte?->email }}@endif
                        @if($s->statut) · <strong>{{ \App\Models\Intranet\MailSuivi::STATUTS[$s->statut] }}</strong>@endif
                        @if($s->categorie) · {{ $s->categorie }}@endif
                    </div>
                    @php $liens = $s->liens->filter(fn($l) => $l->liable)->map(fn($l) => \App\Services\Mail\MailLiens::normaliser($l->liable)); @endphp
                    @if($liens->isNotEmpty())
                        <div class="wt-liens">@foreach($liens as $l)<a href="{{ $l['url'] ?? '#' }}"><i class="fas {{ $l['icon'] }} me-1"></i>{{ $l['title'] }}</a>@endforeach</div>
                    @endif
                    @if($s->note)<div class="wt-note">{{ \Illuminate\Support\Str::limit($s->note, 220) }}</div>@endif
                </div>
                <div class="wt-ech">
                    @if($s->echeance)
                        <span style="color:{{ $s->enRetard() ? '#B91C1C' : ($s->echeance->isToday() ? '#B45309' : '#047857') }};">
                            <i class="fas fa-calendar-day me-1"></i>{{ $s->enRetard() ? 'En retard · ' : ($s->echeance->isToday() ? "Aujourd'hui" : '') }}{{ $s->echeance->isToday() ? '' : $s->echeance->translatedFormat('j M') }}
                        </span>
                    @else
                        <span class="text-muted">Sans échéance</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fas fa-circle-check" style="color:#10B981;"></i>
                <p>Rien à traiter. Classez un mail (priorité, suivi « À traiter », échéance) pour le retrouver ici.</p>
            </div>
        @endforelse
    </section>
</div>
@endsection
