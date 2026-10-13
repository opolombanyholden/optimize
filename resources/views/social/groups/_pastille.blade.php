{{-- Pastille d'identité d'un groupe : avatar (ou initiales sur sa teinte) + visages des membres empilés.
     Paramètres : $g (SocialGroup, users chargés), $taille ('md' | 'lg'), $actif (bool : activité récente). --}}
@php
    $taille = $taille ?? 'md';
    $visages = $g->relationLoaded('users') ? $g->users->take(3) : collect();
    $initialesU = fn($u) => strtoupper(mb_substr($u->prenoms ?: $u->name, 0, 1));
    $couleursP = ['#7C3AED', '#0891B2', '#D97706', '#DB2777', '#059669', '#4F46E5', '#DC2626', '#0D9488'];
    $photo = fn($u) => $u->profile_photo_path ? (preg_match('#^https?://#', $u->profile_photo_path) ? $u->profile_photo_path : asset('storage/'.ltrim($u->profile_photo_path, '/'))) : null;
@endphp
<span class="sg-pastille sg-pastille--{{ $taille }}" style="--teinte: {{ $g->teinte }};">
    @if($g->avatar_url)
        <img src="{{ $g->avatar_url }}" alt="" class="sg-pastille-img" loading="lazy">
    @else
        <span class="sg-pastille-ini" aria-hidden="true">{{ strtoupper(mb_substr($g->nom, 0, 2)) }}</span>
    @endif
    @if($visages->count() > 1)
        <span class="sg-visages" aria-hidden="true">
            @foreach($visages as $u)
                @if($photo($u))<img src="{{ $photo($u) }}" alt="" loading="lazy">@else<span style="background:{{ $couleursP[$u->id % count($couleursP)] }};">{{ $initialesU($u) }}</span>@endif
            @endforeach
        </span>
    @endif
    @if(!empty($actif))<span class="sg-actif" title="Actif à l'instant"></span>@endif
</span>
