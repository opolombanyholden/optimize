@extends('layouts.app')
@section('title', 'Vitrine — Gestion du contenu')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
    <li class="breadcrumb-item active">Vitrine</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #DB2777;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#DB2777,#BE185D);"><i class="fas fa-globe"></i></span>
                Vitrine — Contenu public
            </h1>
            <p class="page-subtitle">Gérez le contenu du site vitrine accessible à <code>/</code></p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);">
            <i class="fas fa-external-link-alt me-2"></i> Voir la vitrine
        </a>
    </div>

    {{-- Onglets --}}
    <ul class="nav nav-tabs mb-4" id="vitTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-slides" type="button"><i class="fas fa-images me-1"></i> Hero ({{ $slides->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-modules" type="button"><i class="fas fa-th-large me-1"></i> Modules ({{ $modules->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-captures" type="button"><i class="fas fa-camera me-1"></i> Captures ({{ $captures->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-atouts" type="button"><i class="fas fa-star me-1"></i> Atouts ({{ $atouts->count() }})</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-settings" type="button"><i class="fas fa-sliders me-1"></i> Paramètres</button></li>
    </ul>

    <div class="tab-content">

        {{-- ═══════════ HERO SLIDES ═══════════ --}}
        <div class="tab-pane fade show active" id="tab-slides">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalSlide" onclick="resetSlideForm()">
                    <i class="fas fa-plus me-2"></i> Nouveau slide
                </button>
            </div>

            <div class="row g-3">
                @foreach($slides as $s)
                <div class="col-md-6">
                    <div class="form-card h-100" style="border-left:4px solid {{ $s->est_actif ? '#16A34A' : '#94A3B8' }};">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div style="flex:1;">
                                @if($s->eyebrow)<span class="opp-stage-badge" style="background:{{ $s->eyebrow_couleur ?? '#0D9488' }};font-size:.6rem;">{{ $s->eyebrow }}</span>@endif
                                <strong style="display:block;font-size:.9rem;margin-top:.35rem;">{!! $s->titre !!}</strong>
                                @if($s->sous_titre)<small style="color:#64748B;">{{ Str::limit($s->sous_titre, 80) }}</small>@endif
                            </div>
                            <div class="dropdown">
                                <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                    <li><button class="dropdown-item" onclick='editSlide(@json($s))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.vitrine.slides.destroy', $s) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                    </form></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2" style="font-size:.7rem;color:#64748B;">
                            <span><i class="fas fa-image"></i> {{ $s->image_path ? 'Image' : ($s->mockup_type ? 'Mockup '.$s->mockup_type : '—') }}</span>
                            <span class="ms-auto"><strong>Ordre :</strong> {{ $s->ordre }}</span>
                            @if(!$s->est_actif)<span class="opp-stage-badge" style="background:#94A3B8;font-size:.55rem;">Inactif</span>@endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════ MODULES ═══════════ --}}
        <div class="tab-pane fade" id="tab-modules">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalModule" onclick="resetModuleForm()">
                    <i class="fas fa-plus me-2"></i> Nouveau module
                </button>
            </div>
            <div class="row g-3">
                @foreach($modules as $m)
                <div class="col-md-4">
                    <div class="form-card h-100" style="border-left:4px solid {{ $m->couleur }};">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <div style="width:40px;height:40px;border-radius:8px;background:{{ $m->couleur }};color:#FFF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas {{ $m->icone }}"></i>
                            </div>
                            <div style="flex:1;">
                                <strong>{{ $m->nom }}</strong>
                                @if($m->description)<small style="display:block;color:#64748B;font-size:.72rem;">{{ Str::limit($m->description, 70) }}</small>@endif
                            </div>
                            <div class="dropdown">
                                <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                    <li><button class="dropdown-item" onclick='editModule(@json($m))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.vitrine.modules.destroy', $m) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                    </form></li>
                                </ul>
                            </div>
                        </div>
                        @if(!empty($m->features))
                        <ul style="font-size:.72rem;color:#475569;padding-left:1rem;margin-bottom:0;">
                            @foreach($m->features as $f)<li>{{ $f }}</li>@endforeach
                        </ul>
                        @endif
                        @if(!$m->est_actif)<span class="opp-stage-badge" style="background:#94A3B8;font-size:.55rem;margin-top:.5rem;">Inactif</span>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════ CAPTURES ═══════════ --}}
        <div class="tab-pane fade" id="tab-captures">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalCapture" onclick="resetCaptureForm()">
                    <i class="fas fa-plus me-2"></i> Nouvelle capture
                </button>
            </div>
            <div class="row g-3">
                @foreach($captures as $c)
                <div class="col-md-4">
                    <div class="form-card h-100">
                        @if($c->image_url)<img src="{{ $c->image_url }}" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:.5rem;">
                        @elseif($c->mockup_type)<div style="background:#F1F5F9;height:120px;border-radius:8px;margin-bottom:.5rem;display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:.78rem;">📐 Mockup : {{ $c->mockup_type }}</div>
                        @endif
                        <div class="d-flex align-items-start gap-2">
                            <div style="flex:1;">
                                @if($c->tag)<span class="opp-stage-badge" style="background:{{ $c->tag_couleur ?? '#0D9488' }};font-size:.55rem;">{{ $c->tag }}</span>@endif
                                <strong style="display:block;font-size:.85rem;">{{ $c->titre }}</strong>
                                @if($c->description)<small style="color:#64748B;font-size:.7rem;">{{ Str::limit($c->description, 60) }}</small>@endif
                            </div>
                            <div class="dropdown">
                                <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                    <li><button class="dropdown-item" onclick='editCapture(@json($c))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.vitrine.captures.destroy', $c) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                    </form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════ ATOUTS ═══════════ --}}
        <div class="tab-pane fade" id="tab-atouts">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);" data-bs-toggle="modal" data-bs-target="#modalAtout" onclick="resetAtoutForm()">
                    <i class="fas fa-plus me-2"></i> Nouvel atout
                </button>
            </div>
            <div class="row g-3">
                @foreach($atouts as $a)
                <div class="col-md-4">
                    <div class="form-card h-100" style="border-left:4px solid {{ $a->gradient_from }};">
                        <div class="d-flex align-items-start gap-2">
                            <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,{{ $a->gradient_from }},{{ $a->gradient_to }});color:#FFF;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;">
                                <i class="fas {{ $a->icone }}"></i>
                            </div>
                            <div style="flex:1;">
                                <strong>{{ $a->titre }}</strong>
                                @if($a->description)<small style="display:block;color:#64748B;font-size:.72rem;">{{ Str::limit($a->description, 80) }}</small>@endif
                            </div>
                            <div class="dropdown">
                                <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                    <li><button class="dropdown-item" onclick='editAtout(@json($a))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form action="{{ route('admin.vitrine.atouts.destroy', $a) }}" method="POST" onsubmit="return confirm('Supprimer ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                    </form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ═══════════ SETTINGS ═══════════ --}}
        <div class="tab-pane fade" id="tab-settings">
            <form method="POST" action="{{ route('admin.vitrine.settings.update') }}">
                @csrf @method('PUT')
                @foreach($settings as $groupe => $items)
                <div class="form-card mb-3">
                    <h5 style="font-size:.95rem;font-weight:700;color:#0F172A;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;">
                        <i class="fas fa-folder me-2" style="color:#DB2777;"></i> {{ ucfirst($groupe) }}
                    </h5>
                    <div class="row g-3">
                        @foreach($items as $s)
                        <div class="col-md-6">
                            <label class="form-label">{{ $s->libelle }}</label>
                            @if($s->type === 'textarea')
                            <textarea name="settings[{{ $s->cle }}]" rows="3" class="form-control">{{ $s->valeur }}</textarea>
                            @elseif($s->type === 'color')
                            <input type="color" name="settings[{{ $s->cle }}]" value="{{ $s->valeur }}" class="form-control form-control-color">
                            @else
                            <input type="{{ $s->type === 'url' ? 'url' : 'text' }}" name="settings[{{ $s->cle }}]" value="{{ $s->valeur }}" class="form-control">
                            @endif
                            <small style="color:#94A3B8;font-size:.65rem;"><code>{{ $s->cle }}</code></small>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);">
                    <i class="fas fa-save me-2"></i> Enregistrer les paramètres
                </button>
            </form>
        </div>

    </div>
</div>

{{-- ═══════════ MODALES ═══════════ --}}

{{-- Modale SLIDE --}}
<div class="modal fade" id="modalSlide" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="slideForm" method="POST" enctype="multipart/form-data" class="modal-content" action="{{ route('admin.vitrine.slides.store') }}">
            @csrf <input type="hidden" name="_method" id="slideMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="slideTitle">Nouveau slide</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7"><label class="form-label">Titre <span class="text-danger">*</span> (peut contenir <code>&lt;span class="highlight"&gt;...&lt;/span&gt;</code>)</label>
                        <input type="text" name="titre" id="sld_titre" class="form-control" required></div>
                    <div class="col-md-5"><label class="form-label">Eyebrow (badge au-dessus)</label>
                        <input type="text" name="eyebrow" id="sld_eyebrow" class="form-control"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Icône eyebrow</label>
                        <input type="text" name="eyebrow_icone" id="sld_eyebrow_icone" class="form-control" placeholder="fa-bolt"></div>
                    <div class="col-md-3"><label class="form-label">Couleur eyebrow</label>
                        <input type="color" name="eyebrow_couleur" id="sld_eyebrow_couleur" class="form-control form-control-color" value="#0D9488"></div>
                    <div class="col-md-5"><label class="form-label">Mockup style (si pas d'image)</label>
                        <select name="mockup_type" id="sld_mockup" class="form-select">
                            <option value="">— Aucun —</option>
                            <option value="dashboard">Dashboard</option>
                            <option value="wbs">WBS</option>
                            <option value="okr">OKR / Objectifs</option>
                            <option value="validation">Validation</option>
                        </select></div>
                </div>
                <div class="mt-2"><label class="form-label">Sous-titre</label>
                    <textarea name="sous_titre" id="sld_sous_titre" rows="2" class="form-control"></textarea></div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><label class="form-label">Texte CTA</label>
                        <input type="text" name="cta_texte" id="sld_cta_texte" class="form-control" value="Demander une démo"></div>
                    <div class="col-md-6"><label class="form-label">URL CTA</label>
                        <input type="text" name="cta_url" id="sld_cta_url" class="form-control" value="#contact"></div>
                </div>
                <div class="mt-2"><label class="form-label">Stats (1 par ligne, format <code>NUM | LABEL</code>)</label>
                    <textarea name="stats_raw" id="sld_stats_raw" rows="3" class="form-control" placeholder="7 | Modules intégrés&#10;100% | Cloud sécurisé"></textarea></div>
                <div class="mt-2"><label class="form-label">Image (optionnelle, remplace le mockup)</label>
                    <input type="file" name="image" class="form-control" accept="image/*"></div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="est_actif" id="sld_actif" value="1" checked>
                    <label class="form-check-label" for="sld_actif">Slide actif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);"><span id="sld_submit">Créer</span></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale MODULE --}}
<div class="modal fade" id="modalModule" tabindex="-1">
    <div class="modal-dialog">
        <form id="moduleForm" method="POST" class="modal-content" action="{{ route('admin.vitrine.modules.store') }}">
            @csrf <input type="hidden" name="_method" id="moduleMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="moduleTitle">Nouveau module</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="mod_nom" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="mod_desc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-7"><label class="form-label">Icône (FontAwesome)</label>
                        <input type="text" name="icone" id="mod_icone" class="form-control" placeholder="fa-coins" value="fa-cube"></div>
                    <div class="col-md-5"><label class="form-label">Couleur</label>
                        <input type="color" name="couleur" id="mod_couleur" class="form-control form-control-color" value="#0D9488"></div>
                </div>
                <div class="mt-3"><label class="form-label">Features (1 par ligne, max 3-4)</label>
                    <textarea name="features_raw" id="mod_features_raw" rows="4" class="form-control" placeholder="Comptabilité analytique&#10;Multi-exercices&#10;Suivi budgétaire"></textarea></div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="est_actif" id="mod_actif" value="1" checked>
                    <label class="form-check-label" for="mod_actif">Module actif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);"><span id="mod_submit">Créer</span></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale CAPTURE --}}
<div class="modal fade" id="modalCapture" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="captureForm" method="POST" enctype="multipart/form-data" class="modal-content" action="{{ route('admin.vitrine.captures.store') }}">
            @csrf <input type="hidden" name="_method" id="captureMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="captureTitle">Nouvelle capture</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="cap_titre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="cap_desc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Tag</label>
                        <input type="text" name="tag" id="cap_tag" class="form-control" placeholder="PMP / WBS"></div>
                    <div class="col-md-3"><label class="form-label">Couleur tag</label>
                        <input type="color" name="tag_couleur" id="cap_tag_couleur" class="form-control form-control-color" value="#0D9488"></div>
                    <div class="col-md-4"><label class="form-label">URL affichée</label>
                        <input type="text" name="url_affichee" id="cap_url" class="form-control" placeholder="/projet/5/wbs"></div>
                </div>
                <div class="mt-3"><label class="form-label">Mockup style (si pas d'image)</label>
                    <select name="mockup_type" id="cap_mockup" class="form-select">
                        <option value="">— Aucun —</option>
                        <option value="dashboard">Dashboard</option>
                        <option value="wbs">WBS</option>
                        <option value="kanban">Kanban</option>
                        <option value="annuaire">Annuaire</option>
                        <option value="okr">OKR</option>
                        <option value="validation">Validation</option>
                    </select></div>
                <div class="mt-3"><label class="form-label">Image (remplace le mockup)</label>
                    <input type="file" name="image" class="form-control" accept="image/*"></div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="est_actif" id="cap_actif" value="1" checked>
                    <label class="form-check-label" for="cap_actif">Capture active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);"><span id="cap_submit">Créer</span></button>
            </div>
        </form>
    </div>
</div>

{{-- Modale ATOUT --}}
<div class="modal fade" id="modalAtout" tabindex="-1">
    <div class="modal-dialog">
        <form id="atoutForm" method="POST" class="modal-content" action="{{ route('admin.vitrine.atouts.store') }}">
            @csrf <input type="hidden" name="_method" id="atoutMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="atoutTitle">Nouvel atout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="ato_titre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="ato_desc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Icône</label>
                        <input type="text" name="icone" id="ato_icone" class="form-control" placeholder="fa-bolt" value="fa-star"></div>
                    <div class="col-md-3"><label class="form-label">Gradient début</label>
                        <input type="color" name="gradient_from" id="ato_grad_from" class="form-control form-control-color" value="#0D9488"></div>
                    <div class="col-md-3"><label class="form-label">Gradient fin</label>
                        <input type="color" name="gradient_to" id="ato_grad_to" class="form-control form-control-color" value="#0F766E"></div>
                </div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" name="est_actif" id="ato_actif" value="1" checked>
                    <label class="form-check-label" for="ato_actif">Atout actif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#DB2777,#BE185D);"><span id="ato_submit">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function resetSlideForm() {
    document.getElementById('slideTitle').textContent = 'Nouveau slide';
    document.getElementById('slideForm').reset();
    document.getElementById('slideMethod').value = 'POST';
    document.getElementById('sld_submit').textContent = 'Créer';
    document.getElementById('slideForm').action = '{{ route("admin.vitrine.slides.store") }}';
}
function editSlide(s) {
    document.getElementById('slideTitle').textContent = 'Modifier le slide';
    document.getElementById('sld_titre').value = s.titre || '';
    document.getElementById('sld_eyebrow').value = s.eyebrow || '';
    document.getElementById('sld_eyebrow_icone').value = s.eyebrow_icone || '';
    document.getElementById('sld_eyebrow_couleur').value = s.eyebrow_couleur || '#0D9488';
    document.getElementById('sld_mockup').value = s.mockup_type || '';
    document.getElementById('sld_sous_titre').value = s.sous_titre || '';
    document.getElementById('sld_cta_texte').value = s.cta_texte || '';
    document.getElementById('sld_cta_url').value = s.cta_url || '';
    document.getElementById('sld_stats_raw').value = (s.stats || []).map(st => `${st.num} | ${st.label}`).join('\n');
    document.getElementById('sld_actif').checked = !!s.est_actif;
    document.getElementById('slideMethod').value = 'PUT';
    document.getElementById('sld_submit').textContent = 'Enregistrer';
    document.getElementById('slideForm').action = '/admin/vitrine/slides/' + s.id;
    new bootstrap.Modal(document.getElementById('modalSlide')).show();
}

function resetModuleForm() {
    document.getElementById('moduleTitle').textContent = 'Nouveau module';
    document.getElementById('moduleForm').reset();
    document.getElementById('mod_couleur').value = '#0D9488';
    document.getElementById('mod_icone').value = 'fa-cube';
    document.getElementById('moduleMethod').value = 'POST';
    document.getElementById('mod_submit').textContent = 'Créer';
    document.getElementById('moduleForm').action = '{{ route("admin.vitrine.modules.store") }}';
}
function editModule(m) {
    document.getElementById('moduleTitle').textContent = 'Modifier le module';
    document.getElementById('mod_nom').value = m.nom || '';
    document.getElementById('mod_desc').value = m.description || '';
    document.getElementById('mod_icone').value = m.icone || 'fa-cube';
    document.getElementById('mod_couleur').value = m.couleur || '#0D9488';
    document.getElementById('mod_features_raw').value = (m.features || []).join('\n');
    document.getElementById('mod_actif').checked = !!m.est_actif;
    document.getElementById('moduleMethod').value = 'PUT';
    document.getElementById('mod_submit').textContent = 'Enregistrer';
    document.getElementById('moduleForm').action = '/admin/vitrine/modules/' + m.id;
    new bootstrap.Modal(document.getElementById('modalModule')).show();
}

function resetCaptureForm() {
    document.getElementById('captureTitle').textContent = 'Nouvelle capture';
    document.getElementById('captureForm').reset();
    document.getElementById('cap_tag_couleur').value = '#0D9488';
    document.getElementById('captureMethod').value = 'POST';
    document.getElementById('cap_submit').textContent = 'Créer';
    document.getElementById('captureForm').action = '{{ route("admin.vitrine.captures.store") }}';
}
function editCapture(c) {
    document.getElementById('captureTitle').textContent = 'Modifier la capture';
    document.getElementById('cap_titre').value = c.titre || '';
    document.getElementById('cap_desc').value = c.description || '';
    document.getElementById('cap_tag').value = c.tag || '';
    document.getElementById('cap_tag_couleur').value = c.tag_couleur || '#0D9488';
    document.getElementById('cap_url').value = c.url_affichee || '';
    document.getElementById('cap_mockup').value = c.mockup_type || '';
    document.getElementById('cap_actif').checked = !!c.est_actif;
    document.getElementById('captureMethod').value = 'PUT';
    document.getElementById('cap_submit').textContent = 'Enregistrer';
    document.getElementById('captureForm').action = '/admin/vitrine/captures/' + c.id;
    new bootstrap.Modal(document.getElementById('modalCapture')).show();
}

function resetAtoutForm() {
    document.getElementById('atoutTitle').textContent = 'Nouvel atout';
    document.getElementById('atoutForm').reset();
    document.getElementById('ato_grad_from').value = '#0D9488';
    document.getElementById('ato_grad_to').value = '#0F766E';
    document.getElementById('ato_icone').value = 'fa-star';
    document.getElementById('atoutMethod').value = 'POST';
    document.getElementById('ato_submit').textContent = 'Créer';
    document.getElementById('atoutForm').action = '{{ route("admin.vitrine.atouts.store") }}';
}
function editAtout(a) {
    document.getElementById('atoutTitle').textContent = 'Modifier l\'atout';
    document.getElementById('ato_titre').value = a.titre || '';
    document.getElementById('ato_desc').value = a.description || '';
    document.getElementById('ato_icone').value = a.icone || 'fa-star';
    document.getElementById('ato_grad_from').value = a.gradient_from || '#0D9488';
    document.getElementById('ato_grad_to').value = a.gradient_to || '#0F766E';
    document.getElementById('ato_actif').checked = !!a.est_actif;
    document.getElementById('atoutMethod').value = 'PUT';
    document.getElementById('ato_submit').textContent = 'Enregistrer';
    document.getElementById('atoutForm').action = '/admin/vitrine/atouts/' + a.id;
    new bootstrap.Modal(document.getElementById('modalAtout')).show();
}
</script>
@endpush
@endsection
