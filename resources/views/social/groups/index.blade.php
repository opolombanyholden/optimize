@extends('layouts.app')

@section('title', 'Groupes de discussion')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css"
      rel="stylesheet"
      integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD"
      crossorigin="anonymous">
<style>
.gp-wrapper { max-width:900px; margin:0 auto; }
.gp-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:.75rem; }
.gp-header h1 { font-size:1.3rem; font-weight:700; margin:0; color:#0F172A; }
.gp-btn-new { background:#0A66C2; color:#fff; border:0; padding:.5rem 1rem; border-radius:0; font-weight:600; font-size:.85rem; }
.gp-btn-new:hover { color:#fff; opacity:.92; }
.gp-section-title { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94A3B8; margin:1.25rem 0 .6rem; }
.gp-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:.75rem; }
@media(max-width:600px) { .gp-grid { grid-template-columns:1fr; } }
.gp-card { background:#fff; border:1px solid #E0DFDC; padding:1rem 1.1rem; display:flex; align-items:center; gap:.85rem; text-decoration:none; color:inherit; transition:border-color .15s, background .15s; }
.gp-card:hover { border-color:#0A66C2; background:#FAFAF9; color:inherit; }
.gp-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; flex-shrink:0; background:#0A66C2; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.95rem; }
.gp-info { flex:1; min-width:0; }
.gp-name { font-weight:700; color:#1E293B; margin:0; font-size:.9rem; }
.gp-meta { font-size:.72rem; color:#94A3B8; margin-top:.2rem; }
.gp-badge-public { font-size:.6rem; background:#DCFCE7; color:#059669; padding:.15rem .45rem; border-radius:0; font-weight:700; text-transform:uppercase; }
.gp-empty { text-align:center; padding:3rem 1rem; color:#94A3B8; background:#fff; border:1px dashed #E2E8F0; }
</style>
@endpush

@section('content')
<div class="gp-wrapper">
    <div class="gp-header">
        <h1><i class="fas fa-comments me-2" style="color:#0A66C2;"></i>Groupes de discussion</h1>
        <button type="button" class="gp-btn-new" data-bs-toggle="modal" data-bs-target="#gpCreateModal">
            <i class="fas fa-plus me-1"></i>Nouveau groupe
        </button>
    </div>

    <div class="gp-section-title">Mes groupes</div>
    @if($mesGroupes->count())
    <div class="gp-grid">
        @foreach($mesGroupes as $g)
        <a href="{{ route('social.groups.show', $g) }}" class="gp-card">
            @if($g->avatar_url)
                <img src="{{ $g->avatar_url }}" alt="" class="gp-avatar" style="background:none;">
            @else
                <span class="gp-avatar">{{ strtoupper(mb_substr($g->nom, 0, 2)) }}</span>
            @endif
            <div class="gp-info">
                <p class="gp-name">{{ $g->nom }}</p>
                <div class="gp-meta">
                    <i class="fas fa-users me-1"></i>{{ $g->members_count }} membre{{ $g->members_count > 1 ? 's' : '' }}
                    · <i class="fas fa-message ms-1 me-1"></i>{{ $g->messages_count }}
                    @if($g->is_public) · <span class="gp-badge-public">Public</span>@endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="gp-empty">
        <i class="fas fa-comments d-block mb-2" style="font-size:2rem; opacity:.4;"></i>
        <p class="mb-0">Vous n'êtes membre d'aucun groupe. Créez-en un ou rejoignez un groupe public.</p>
    </div>
    @endif

    @if($groupesPublics->count())
    <div class="gp-section-title">Groupes publics à découvrir</div>
    <div class="gp-grid">
        @foreach($groupesPublics as $g)
        <a href="{{ route('social.groups.show', $g) }}" class="gp-card">
            @if($g->avatar_url)
                <img src="{{ $g->avatar_url }}" alt="" class="gp-avatar" style="background:none;">
            @else
                <span class="gp-avatar">{{ strtoupper(mb_substr($g->nom, 0, 2)) }}</span>
            @endif
            <div class="gp-info">
                <p class="gp-name">{{ $g->nom }}</p>
                <div class="gp-meta">
                    <i class="fas fa-users me-1"></i>{{ $g->members_count }} membre{{ $g->members_count > 1 ? 's' : '' }}
                    · <span class="gp-badge-public">Public</span>
                </div>
                @if($g->description)
                <div class="gp-meta" style="margin-top:.15rem;">{{ \Illuminate\Support\Str::limit($g->description, 60) }}</div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

{{-- Modale : nouveau groupe --}}
<div class="modal fade" id="gpCreateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('social.groups.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header" style="background:#0A66C2; color:#fff; border:0;">
                <h5 class="modal-title"><i class="fas fa-comments me-2"></i>Nouveau groupe</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nom du groupe <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" required maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-muted small">(facultatif)</span></label>
                    <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Avatar <span class="text-muted small">(facultatif)</span></label>
                    <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Membres à inviter</label>
                    <select name="members[]" multiple class="form-select gp-members-select" placeholder="Rechercher des personnes…">
                        @foreach(\App\Models\User::where('id','!=',auth()->id())->orderBy('name')->get(['id','prenoms','name']) as $u)
                            <option value="{{ $u->id }}">{{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_public" value="1" id="gpPublic">
                    <label class="form-check-label" for="gpPublic">Groupe public (joignable par tous les collaborateurs)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="gp-btn-new"><i class="fas fa-plus me-1"></i>Créer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
        integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf"
        crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('gpCreateModal');
    modal?.addEventListener('shown.bs.modal', function () {
        var sel = modal.querySelector('.gp-members-select');
        if (sel && !sel.tomselect) {
            new TomSelect(sel, { plugins:['remove_button'], maxOptions:500, dropdownParent: 'body' });
        }
    });
});
</script>
@endpush
