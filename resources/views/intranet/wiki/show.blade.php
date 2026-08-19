@extends('layouts.app')
@section('title', $article->titre . ' — Wiki')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index') }}">Wiki</a></li>
    @if($article->categorie)
    <li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index', ['categorie' => $article->categorie->id]) }}">{{ $article->categorie->nom }}</a></li>
    @endif
    <li class="breadcrumb-item active">{{ Str::limit($article->titre, 35) }}</li>
</ol>
@endsection

@section('content')
@php
    $catColor = $article->categorie?->couleur ?? '#4F46E5';
    $auteur = $article->auteur;
    $auteurInitiales = $auteur ? strtoupper(substr($auteur->prenoms ?? $auteur->name, 0, 1)) . strtoupper(substr($auteur->name, 0, 1)) : 'AN';
@endphp

<div class="page-intranet" style="--accent: {{ $catColor }};">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg, {{ $catColor }}15 0%, transparent 100%); border-radius:14px; padding:2rem 2rem 2.5rem; margin-bottom:1.5rem; border:1px solid {{ $catColor }}22;">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div class="d-flex flex-wrap gap-2">
                @if($article->categorie)
                <a href="{{ route('intranet.wiki.index', ['categorie' => $article->categorie->id]) }}"
                   class="opp-stage-badge" style="background:{{ $catColor }};text-decoration:none;">
                    <i class="fas {{ $article->categorie->icone ?? 'fa-folder' }} me-1"></i> {{ $article->categorie->nom }}
                </a>
                @endif
                @if($article->is_epingle)<span class="opp-stage-badge" style="background:#F59E0B;"><i class="fas fa-thumbtack me-1"></i> Épinglé</span>@endif
                <span class="opp-stage-badge" style="background:#64748B;">v{{ $article->version }}</span>
                @if($article->parent)
                <a href="{{ route('intranet.wiki.show', $article->parent) }}" class="opp-stage-badge" style="background:#94A3B8;text-decoration:none;">
                    <i class="fas fa-arrow-up-from-bracket me-1"></i> {{ Str::limit($article->parent->titre, 25) }}
                </a>
                @endif
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-2">
                @can('update:wiki')
                <a href="{{ route('intranet.wiki.edit', $article) }}" class="btn btn-light btn-sm"><i class="fas fa-pen-to-square me-1"></i> Modifier</a>
                @endcan
                <a href="{{ route('intranet.wiki.index', ['categorie' => $article->categorie_id]) }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Retour</a>
                @can('delete:wiki')
                <form action="{{ route('intranet.wiki.destroy', $article) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet article ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-light btn-sm" style="color:#DC2626;"><i class="fas fa-trash"></i></button>
                </form>
                @endcan
            </div>
        </div>

        <h1 style="font-size:clamp(1.6rem, 3vw, 2.4rem); font-weight:800; letter-spacing:-0.02em; line-height:1.15; color:#0F172A; margin-bottom:.75rem;">
            {{ $article->titre }}
        </h1>

        @if($article->extrait)
        <p style="font-size:1.05rem; color:#475569; max-width:760px; margin-bottom:1.25rem;">{{ $article->extrait }}</p>
        @endif

        {{-- Auteur + meta --}}
        <div class="d-flex flex-wrap align-items-center gap-3" style="font-size:.85rem; color:#475569;">
            <div class="d-flex align-items-center gap-2">
                @if($auteur?->profile_photo_path)
                <img src="{{ asset('storage/' . $auteur->profile_photo_path) }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                @else
                <div style="width:36px;height:36px;border-radius:50%;background:{{ $catColor }};color:#FFF;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;">{{ $auteurInitiales }}</div>
                @endif
                <div>
                    <div style="font-weight:700;color:#0F172A;font-size:.85rem;">{{ $auteur?->prenoms }} {{ $auteur?->name }}</div>
                    <div style="font-size:.7rem;color:#94A3B8;">{{ $article->created_at->translatedFormat('d F Y') }}</div>
                </div>
            </div>
            <div style="border-left:1px solid #E2E8F0; padding-left:1rem; display:flex; gap:1rem;">
                <span><i class="far fa-clock me-1"></i> Mis à jour {{ $article->updated_at->diffForHumans() }}</span>
                <span><i class="fas fa-eye me-1"></i> {{ $article->vues }} vues</span>
                @if($article->temps_lecture)<span><i class="fas fa-book-open me-1"></i> {{ $article->temps_lecture }} min de lecture</span>@endif
                @if($article->editeur && $article->editeur->id !== $auteur?->id)
                <span><i class="fas fa-pen me-1"></i> Modifié par {{ $article->editeur->prenoms }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Layout 2 colonnes --}}
    <div class="row g-4">
        {{-- Colonne principale --}}
        <div class="col-lg-{{ $tableDesMatieres->count() > 1 || $article->enfants->count() ? '8' : '12' }}">

            {{-- Média principal --}}
            @if($article->media_url && $article->media_principal_type === 'image')
            <figure style="margin:0 0 1.5rem;">
                <img src="{{ $article->media_url }}" alt="" class="lightbox-trigger" style="width:100%;border-radius:12px;cursor:zoom-in;box-shadow:0 6px 20px rgba(0,0,0,0.06);">
            </figure>
            @elseif($article->media_url && $article->media_principal_type === 'video')
            <div style="margin:0 0 1.5rem;">
                <video src="{{ $article->media_url }}" controls style="width:100%;border-radius:12px;"></video>
            </div>
            @endif

            {{-- Contenu principal --}}
            <article class="contact-detail-card" style="padding:2rem;">
                <div class="annonce-content" style="font-size:1rem;line-height:1.75;color:#1E293B;">
                    {!! $article->contenu !!}
                </div>

                {{-- Tags --}}
                @if(!empty($article->tags))
                <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid #F1F5F9; display:flex; flex-wrap:wrap; gap:.5rem;">
                    <span style="font-size:.75rem;color:#64748B;font-weight:600;align-self:center;">Tags :</span>
                    @foreach($article->tags as $t)
                    <span style="background:{{ $catColor }}15;color:{{ $catColor }};padding:.25rem .65rem;border-radius:6px;font-size:.78rem;font-weight:500;">#{{ $t }}</span>
                    @endforeach
                </div>
                @endif
            </article>

            {{-- Médias attachés --}}
            @include('intranet._partials.media-display', ['entity' => $article])

            {{-- Commentaires --}}
            @if($article->commentairesActifs())
            <section class="contact-detail-card mt-3">
                <h4 class="contact-detail-card-title"><i class="fas fa-comment"></i> Commentaires ({{ $article->commentaires->count() }})</h4>
                @forelse($article->commentaires->where('parent_id', null) as $comment)
                <div class="comment-item">
                    <div class="comment-avatar">{{ strtoupper(substr($comment->user->prenoms ?? $comment->user->name, 0, 1)) }}</div>
                    <div class="comment-body">
                        <div class="comment-header"><strong>{{ $comment->user->prenoms ?? '' }} {{ $comment->user->name }}</strong><span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span></div>
                        <div class="comment-text">{{ $comment->contenu }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">Aucun commentaire pour cet article.</p>
                @endforelse
            </section>
            @endif

        </div>

        {{-- Sidebar : TOC + Sous-articles --}}
        @if($tableDesMatieres->count() > 1 || $article->enfants->count())
        <div class="col-lg-4">

            {{-- Table des matières --}}
            @if($tableDesMatieres->count() > 1)
            <div class="contact-detail-card mb-3" style="position:sticky; top:80px;">
                <h4 class="contact-detail-card-title"><i class="fas fa-list-ul"></i> Sommaire</h4>
                <ul style="list-style:none; padding:0; margin:0; font-size:.85rem;">
                    @foreach($tableDesMatieres as $item)
                    <li style="border-left:3px solid {{ $item->id === $article->id ? $catColor : 'transparent' }}; padding:.4rem .85rem; transition:all .2s;">
                        <a href="{{ route('intranet.wiki.show', $item) }}" style="text-decoration:none; color:{{ $item->id === $article->id ? '#0F172A' : '#64748B' }}; font-weight:{{ $item->id === $article->id ? '700' : '500' }};">
                            {{ $item->titre }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Sous-articles --}}
            @if($article->enfants->count())
            <div class="contact-detail-card mb-3">
                <h4 class="contact-detail-card-title"><i class="fas fa-sitemap"></i> Sous-articles ({{ $article->enfants->count() }})</h4>
                <ul style="list-style:none; padding:0; margin:0;">
                    @foreach($article->enfants as $child)
                    <li style="padding:.5rem 0; border-bottom:1px solid #F1F5F9; font-size:.85rem;">
                        <a href="{{ route('intranet.wiki.show', $child) }}" style="text-decoration:none; color:#0F172A; display:flex; align-items:center; gap:.5rem;">
                            <i class="fas fa-file-lines" style="color:{{ $catColor }};font-size:.75rem;"></i>
                            {{ $child->titre }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Stats --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-chart-simple"></i> Statistiques</h4>
                <ul class="contact-info-list">
                    <li><strong>Vues :</strong> {{ $article->vues }}</li>
                    <li><strong>Version :</strong> v{{ $article->version }}</li>
                    @if($article->temps_lecture)<li><strong>Lecture :</strong> {{ $article->temps_lecture }} min</li>@endif
                    <li><strong>Créé :</strong> {{ $article->created_at->format('d/m/Y') }}</li>
                    <li><strong>Modifié :</strong> {{ $article->updated_at->format('d/m/Y') }}</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@include('intranet._partials.lightbox')
@endsection
