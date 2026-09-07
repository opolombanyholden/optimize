{{-- Contenu injecté dans l'offcanvas #socialDrawer via fetch --}}

{{-- ── Anniversaires ──────────────────────────────────────── --}}
<div class="rs-section">
    <div class="rs-section-title"><i class="fas fa-cake-candles" style="color:#DB2777;"></i> Anniversaires (7 jours)</div>
    @forelse($anniversaires as $emp)
    @php
        $bday = $emp->date_naissance->copy()->year(now()->year);
        if ($bday->lt(now()->startOfDay())) $bday->addYear();
        $jours = (int) now()->startOfDay()->diffInDays($bday, false);
        $label = $jours === 0 ? "Aujourd'hui" : ($jours === 1 ? 'Demain' : $bday->isoFormat('dddd D MMM'));
        $photoPath = $emp->user?->profile_photo_path;
        $photoUrl = $photoPath && preg_match('#^https?://#', $photoPath) ? $photoPath
                    : ($photoPath ? asset('storage/'.ltrim($photoPath,'/')) : null);
        $initiales = strtoupper(mb_substr($emp->prenoms ?? '',0,1).mb_substr($emp->noms ?? '',0,1));
        $nbWishes = (int) ($wishesData[$emp->id] ?? 0);
        $dejaEnvoye = $wishesDejaEnvoyes->contains($emp->id);
    @endphp
    <div class="rs-bday-row">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="" class="rs-avatar" loading="lazy">
        @else
            <div class="rs-avatar rs-avatar-fb">{{ $initiales ?: '?' }}</div>
        @endif
        <div class="rs-bday-body">
            <div class="rs-bday-name">
                @if($jours === 0)<i class="fas fa-cake-candles me-1" style="color:#DB2777;"></i>@endif
                {{ $emp->prenoms }} {{ $emp->noms }}
            </div>
            <div class="rs-bday-meta">{{ $label }}@if($nbWishes > 0) · <i class="fas fa-heart" style="color:#DB2777;"></i> {{ $nbWishes }}@endif</div>
        </div>
        @if($dejaEnvoye)
            <span class="rs-sent" title="Vœux envoyés"><i class="fas fa-check"></i></span>
        @else
            <button type="button" class="rs-wish-btn" data-bs-toggle="modal" data-bs-target="#modalWish-{{ $emp->id }}">🎂</button>
        @endif
    </div>
    @empty
    <div class="rs-empty"><i class="fas fa-cake-candles me-1"></i>Aucun anniversaire cette semaine.</div>
    @endforelse
</div>

{{-- ── Publications publiques ─────────────────────────────── --}}
<div class="rs-section">
    <div class="rs-section-title"><i class="fas fa-users" style="color:#7C3AED;"></i> Publications publiques</div>
    @forelse($publications as $pub)
    @php
        $obj = $pub->publishable;
        $titre = $obj->titre ?? $obj->title ?? $obj->objet ?? $obj->nom ?? 'Publication';
        $type  = class_basename($pub->publishable_type);
    @endphp
    <div class="rs-post">
        <div class="rs-post-title">{{ \Illuminate\Support\Str::limit($titre, 70) }}</div>
        <div class="rs-post-meta">
            <i class="fas fa-user me-1"></i>{{ $pub->auteur?->prenoms }} {{ $pub->auteur?->name ?? '—' }}
            · <span class="rs-post-badge">{{ $type }}</span>
            · <i class="fas fa-clock ms-1"></i> {{ $pub->publie_le?->diffForHumans() }}
        </div>
    </div>
    @empty
    <div class="rs-empty"><i class="fas fa-inbox me-1"></i>Aucune publication récente.</div>
    @endforelse
</div>

{{-- ── Médiathèque récente ────────────────────────────────── --}}
<div class="rs-section">
    <div class="rs-section-title"><i class="fas fa-images" style="color:#0891B2;"></i> Médiathèque récente</div>
    @if($medias->count())
    <div class="rs-media-grid">
        @foreach($medias as $media)
        @php
            $vignette = $media->thumbnail_url ?: $media->url_externe
                ?: ($media->est_image ? $media->fichier_url : ($media->apercu_url ?? null));
            $urlOk = $vignette && preg_match('#^https?://[^\s"\'()<>]+$#', $vignette);
        @endphp
        <a href="{{ route('intranet.mediatheque.show', $media) }}" class="rs-media-tile" title="{{ $media->titre }}">
            @if($urlOk)
                <img src="{{ $vignette }}" alt="" loading="lazy">
            @else
                <div class="rs-media-fb"><i class="fas fa-{{ $media->est_video ? 'film' : 'image' }}"></i></div>
            @endif
            @if($media->est_video)<span class="rs-media-video">▶</span>@endif
        </a>
        @endforeach
    </div>
    @else
    <div class="rs-empty"><i class="fas fa-images me-1"></i>Aucun média.</div>
    @endif
</div>

{{-- Modales de vœux — placées dans le body après injection par JS --}}
@foreach($anniversaires as $emp)
@if(!$wishesDejaEnvoyes->contains($emp->id))
<div class="modal fade rs-wish-modal" id="modalWish-{{ $emp->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('intranet.anniversaires.wish', $emp) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#DB2777,#BE185D); color:#fff; border:0;">
                <h5 class="modal-title">🎂 Souhaiter à {{ $emp->prenoms }} {{ $emp->noms }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Message personnalisé (facultatif).</p>
                <textarea name="message" class="form-control" rows="3" maxlength="500" placeholder="Joyeux anniversaire ! 🎂"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn" style="background:#DB2777; color:#fff; font-weight:600;"><i class="fas fa-paper-plane me-1"></i>Envoyer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
