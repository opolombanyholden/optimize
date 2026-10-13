@extends('layouts.app')

@php
    $dossierCourant = collect($dossiers)->firstWhere('nom', $dossier);
    $titre = $dossierCourant['libelle'] ?? $dossier;
    $estEnvoyes = ($dossierCourant['role'] ?? null) === 'sent' || ($dossierCourant['role'] ?? null) === 'drafts';
@endphp

@section('title', $titre.' — Mails')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mails.index', ['compte' => $compte->id]) }}">Mails professionnels</a></li>
    <li class="breadcrumb-item active">{{ $titre }}</li>
</ol>
@endsection

@include('intranet.mails._styles')

@section('content')
<div class="wm-wrap">
    @include('intranet.mails._side')

    <section class="wm-main">
        <div class="wm-toolbar">
            <h1 class="h6 mb-0 me-auto fw-bold" style="color:#1E293B;">{{ $titre }}
                <span class="text-muted fw-normal" style="font-size:.78rem;">· {{ $liste['total'] }} message(s)</span>
            </h1>
            <form method="GET" class="d-flex gap-1">
                <input type="hidden" name="compte" value="{{ $compte->id }}">
                <input type="hidden" name="dossier" value="{{ $dossier }}">
                <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:220px;" placeholder="Rechercher…">
                <button class="btn btn-sm btn-outline-secondary" title="Rechercher"><i class="fas fa-search"></i></button>
            </form>
            <a href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier]) }}" class="btn btn-sm btn-outline-secondary" title="Actualiser"><i class="fas fa-rotate-right"></i></a>
        </div>

        @if($erreur)
            <div class="alert alert-danger m-3" style="font-size:.85rem;">
                <strong><i class="fas fa-triangle-exclamation me-1"></i>Impossible d'accéder à la boîte {{ $compte->email }}.</strong>
                <div class="mt-1">{{ $erreur }}</div>
                <a href="{{ route('mon-compte.index') }}#comptes-mail" class="btn btn-sm btn-outline-danger mt-2">Vérifier les paramètres du compte</a>
            </div>
        @elseif(empty($liste['messages']))
            <div class="empty-state">
                <i class="fas {{ $q ? 'fa-magnifying-glass' : 'fa-inbox' }}"></i>
                <p>{{ $q ? 'Aucun message ne correspond à « '.$q.' ».' : 'Aucun message dans ce dossier.' }}</p>
            </div>
        @else
            @foreach($liste['messages'] as $m)
                @php
                    $personne = $estEnvoyes ? $m['a'] : $m['de'];
                    $sv = $m['message_id'] ? ($suivis[$m['message_id']] ?? null) : null;
                    $pr = $sv && $sv->priorite !== 'normale' ? \App\Models\Intranet\MailSuivi::PRIORITES[$sv->priorite] : null;
                @endphp
                <a href="{{ route('intranet.mails.show', [$compte, $m['uid'], 'dossier' => $dossier]) }}" class="wm-row {{ $m['vu'] ? '' : 'unread' }}">
                    <i class="fas fa-star wm-flag {{ $m['suivi'] ? 'on' : '' }}"></i>
                    <span class="wm-from" title="{{ $personne['email'] }}">{{ $estEnvoyes ? 'À : ' : '' }}{{ $personne['nom'] ?: $personne['email'] }}</span>
                    <span class="wm-subject">
                        @if($m['repondu'])<i class="fas fa-reply text-muted me-1" style="font-size:.7rem;" title="Répondu"></i>@endif
                        @if($pr)<span class="wm-prio" style="background:{{ $pr['fond'] }}; color:{{ $pr['couleur'] }};" title="Priorité {{ mb_strtolower($pr['libelle']) }}"><i class="fas {{ $pr['icone'] }}"></i></span>@endif
                        {{ $m['sujet'] }}
                        @if($m['pieces'])<i class="fas fa-paperclip text-muted ms-1" style="font-size:.72rem;" title="Pièce(s) jointe(s)"></i>@endif
                        @if($sv && $sv->liens_count)<span class="wm-tag" title="Lié à {{ $sv->liens_count }} élément(s)"><i class="fas fa-link me-1"></i>{{ $sv->liens_count }}</span>@endif
                        @if($sv?->categorie)<span class="wm-tag">{{ $sv->categorie }}</span>@endif
                        @if($sv?->echeance && $sv->statut !== 'traite')<span class="wm-tag {{ $sv->enRetard() ? 'retard' : '' }}" title="Échéance"><i class="fas fa-calendar-day me-1"></i>{{ $sv->echeance->translatedFormat('j M') }}</span>@endif
                    </span>
                    <span class="wm-date">{{ $m['date'] ? ($m['date']->isToday() ? $m['date']->format('H:i') : ($m['date']->isCurrentYear() ? $m['date']->translatedFormat('j M') : $m['date']->format('d/m/Y'))) : '' }}</span>
                </a>
            @endforeach

            @if($pages > 1)
            <div class="d-flex justify-content-between align-items-center p-3" style="font-size:.8rem;">
                <span class="text-muted">Page {{ $page }} / {{ $pages }}</span>
                <div class="btn-group">
                    <a class="btn btn-sm btn-outline-secondary {{ $page <= 1 ? 'disabled' : '' }}" href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier, 'q' => $q ?: null, 'page' => $page - 1]) }}"><i class="fas fa-chevron-left"></i></a>
                    <a class="btn btn-sm btn-outline-secondary {{ $page >= $pages ? 'disabled' : '' }}" href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier, 'q' => $q ?: null, 'page' => $page + 1]) }}"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            @endif
        @endif
    </section>
</div>
@endsection
