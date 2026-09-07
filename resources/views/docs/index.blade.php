@extends('layouts.docs')

@section('title', 'Bibliothèque des guides')

@section('crumb')
    <span class="ds-crumb">Bibliothèque</span>
@endsection

@push('styles')
<style>
.dx-hero {
    max-width: 1000px; margin: 0 auto; padding: 3rem 1.5rem 1.5rem;
}
.dx-eyebrow {
    font-family: var(--ds-sans); font-size: .7rem; font-weight: 700;
    letter-spacing: .18em; text-transform: uppercase; color: var(--ds-brand);
    margin-bottom: 1rem;
}
.dx-title {
    font-family: var(--ds-sans); font-size: 2.4rem; font-weight: 800; line-height: 1.1;
    letter-spacing: -0.02em; color: var(--ds-ink); margin: 0 0 .5rem; text-wrap: balance;
}
.dx-sub {
    font-family: var(--ds-serif); font-style: italic; color: var(--ds-mute);
    font-size: 1.1rem; max-width: 620px; margin: 0;
}

.dx-grid {
    max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem 4rem;
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;
}
@media (max-width: 720px) { .dx-grid { grid-template-columns: 1fr; padding: 0 1rem 3rem; } }

.dx-card {
    background: var(--ds-panel); border: 1px solid var(--ds-line);
    padding: 1.5rem; position: relative;
    display: flex; flex-direction: column; gap: 1rem;
    transition: border-color .15s, transform .15s;
}
.dx-card.is-ready:hover { border-color: var(--ds-brand); transform: translateY(-2px); }
.dx-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.dx-card-ico {
    width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.15rem; flex-shrink: 0;
}
.dx-status {
    font-family: var(--ds-sans); font-size: .65rem; font-weight: 800; letter-spacing: .1em;
    text-transform: uppercase; padding: .25rem .55rem; border-radius: 3px;
}
.dx-status-ready { background: #D1FAE5; color: #059669; }
.dx-status-soon { background: var(--ds-line-2); color: var(--ds-mute); }
.dx-card-title {
    font-family: var(--ds-sans); font-size: 1.15rem; font-weight: 700; color: var(--ds-ink);
    margin: 0 0 .35rem; letter-spacing: -0.01em;
}
.dx-card-sub {
    font-family: var(--ds-serif); font-size: .92rem; color: var(--ds-body);
    line-height: 1.5; margin: 0;
}
.dx-card-meta {
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    font-family: var(--ds-sans); font-size: .75rem; color: var(--ds-mute);
    padding-top: 1rem; border-top: 1px solid var(--ds-line-2);
}
.dx-card-meta span { display: inline-flex; align-items: center; gap: .35rem; }
.dx-card-actions {
    display: flex; gap: .5rem; padding-top: 1rem; border-top: 1px solid var(--ds-line-2); margin-top: auto;
}
.dx-card-actions .ds-btn { flex: 1; justify-content: center; }
.dx-card.is-soon { opacity: .7; }
.dx-card.is-soon .dx-card-actions { display: none; }
.dx-card.is-soon::after {
    content: 'Bientôt disponible'; position: absolute; top: 1.25rem; right: 1.5rem;
    font-family: var(--ds-sans); font-size: .7rem; font-weight: 700;
    color: var(--ds-mute); letter-spacing: .05em;
}
</style>
@endpush

@section('content')
<div class="dx-hero">
    <div class="dx-eyebrow">Bibliothèque OptimiZe</div>
    <h1 class="dx-title">Choisissez le guide qui vous concerne</h1>
    <p class="dx-sub">Chaque module OptimiZe dispose de son manuel de prise en main pas à pas. Consultez en ligne ou téléchargez au format PDF pour référence hors-ligne.</p>
</div>

<div class="dx-grid">
    @foreach($guides as $slug => $g)
    <article class="dx-card is-{{ $g['status'] }}">
        <div class="dx-card-head">
            <div class="dx-card-ico" style="background:{{ $g['accent_soft'] }}; color:{{ $g['accent'] }};">
                <i class="fas {{ $g['icon'] }}"></i>
            </div>
            @if($g['status'] === 'ready')
            <span class="dx-status dx-status-ready">Disponible</span>
            @endif
        </div>

        <div>
            <h2 class="dx-card-title">{{ $g['title'] }}</h2>
            <p class="dx-card-sub">{{ $g['subtitle'] }}</p>
        </div>

        <div class="dx-card-meta">
            <span><i class="fas fa-user-tie"></i> {{ $g['audience'] }}</span>
            @if($g['status'] === 'ready')
            <span><i class="fas fa-clock"></i> {{ $g['duration'] }}</span>
            <span><i class="fas fa-list-ol"></i> {{ $g['chapters'] }} chapitres</span>
            @endif
        </div>

        @if($g['status'] === 'ready')
        <div class="dx-card-actions">
            <a href="{{ route('docs.show', $slug) }}" class="ds-btn ds-btn-primary">
                <i class="fas fa-book-open"></i> Consulter
            </a>
            <a href="{{ route('docs.pdf', $slug) }}" class="ds-btn ds-btn-outline">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
        @endif
    </article>
    @endforeach
</div>
@endsection
