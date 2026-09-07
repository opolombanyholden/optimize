@extends('layouts.app')

@section('title', 'Publication')

@push('styles')
<style>
.sp-wrap { max-width:720px; margin:0 auto; }
.sp-back { display:inline-flex; align-items:center; gap:.35rem; color:#0A66C2; font-size:.82rem; font-weight:600; text-decoration:none; margin-bottom:.75rem; }
.sp-back:hover { text-decoration:underline; }
.sp-card { background:#fff; border:1px solid #E0DFDC; padding:1rem 1.15rem; }
.sp-head { display:flex; align-items:center; gap:.7rem; margin-bottom:.75rem; }
.sp-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover; }
.sp-avatar-fb { display:flex; align-items:center; justify-content:center; background:#0A66C2; color:#fff; font-size:.88rem; font-weight:600; }
.sp-author { font-weight:600; color:#191919; font-size:.9rem; line-height:1.2; }
.sp-date { font-size:.75rem; color:#9CA3AF; margin-top:.15rem; }
.sp-content { color:#191919; font-size:.95rem; line-height:1.55; margin-bottom:.75rem; }
.sp-content p { margin:0 0 .5rem; }
.sp-content h1, .sp-content h2, .sp-content h3 { font-weight:600; color:#191919; margin:.75rem 0 .35rem; }
.sp-content ul, .sp-content ol { padding-left:1.4rem; }
.sp-content blockquote { border-left:3px solid #0A66C2; padding-left:.75rem; color:#666; }
.sp-content a { color:#0A66C2; text-decoration:none; }
.sp-content a:hover { text-decoration:underline; }
.sp-stats { display:flex; gap:1rem; font-size:.78rem; color:#666; border-top:1px solid #EDEBE8; padding-top:.6rem; margin-top:.6rem; }
</style>
@endpush

@section('content')
<div class="sp-wrap">
    <a href="{{ route('social.dashboard') }}" class="sp-back"><i class="fas fa-arrow-left"></i> Retour au fil</a>
    <div class="sp-card">
        @php
            $a = $post->auteur;
            $ph = $a?->profile_photo_path;
            $phU = $ph && preg_match('#^https?://#', $ph) ? $ph : ($ph ? asset('storage/'.ltrim($ph,'/')) : null);
            $ini = strtoupper(mb_substr($a?->prenoms ?? '',0,1).mb_substr($a?->name ?? '',0,1));
        @endphp
        <div class="sp-head">
            @if($phU)<img src="{{ $phU }}" alt="" class="sp-avatar">
            @else<div class="sp-avatar sp-avatar-fb">{{ $ini ?: '?' }}</div>@endif
            <div>
                <div class="sp-author">{{ $a?->prenoms }} {{ $a?->name ?? '—' }}</div>
                <div class="sp-date"><i class="fas fa-clock me-1"></i>{{ $post->created_at?->diffForHumans() }}</div>
            </div>
        </div>
        <div class="sp-content">{!! $post->contenu !!}</div>
        @if($post->piecesJointes->count())
        <div style="margin:.5rem 0; display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.35rem;">
            @foreach($post->piecesJointes as $pj)
                @if($pj->categorie === 'image')
                <a href="{{ $pj->url }}" target="_blank"><img src="{{ $pj->url }}" alt="" style="width:100%; aspect-ratio:1/1; object-fit:cover;"></a>
                @elseif($pj->categorie === 'video')
                <video src="{{ $pj->url }}" controls style="width:100%;"></video>
                @endif
            @endforeach
        </div>
        @endif
        <div class="sp-stats">
            <span><i class="fas fa-heart" style="color:#0A66C2;"></i> {{ $post->likes_count ?? 0 }} j'aime</span>
            <span><i class="fas fa-comment" style="color:#0A66C2;"></i> {{ $post->commentaires_count ?? 0 }} commentaire{{ ($post->commentaires_count ?? 0) > 1 ? 's' : '' }}</span>
        </div>
    </div>
</div>
@endsection
