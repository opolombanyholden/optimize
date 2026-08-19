@extends('layouts.app')
@section('title', 'Utiliser — ' . $template->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.show', $template) }}">{{ $template->titre }}</a></li>
    <li class="breadcrumb-item active">Utiliser</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon" style="background: {{ $template->couleur }};"><i class="fas fa-wand-magic-sparkles"></i></span>
            Générer un document
        </h1>
        <p class="page-subtitle">Remplissez les variables du template « {{ $template->titre }} »</p>
    </div>

    @if($template->est_html && count($variables))
    <form action="{{ route('intranet.templates.generer', $template) }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-main">
                <div class="form-card">
                    <div class="form-card-header"><i class="fas fa-wand-magic-sparkles"></i> Variables à remplir</div>
                    <div class="form-card-body">
                        @foreach($variables as $var)
                        <div class="mb-3">
                            <label class="form-label">{{ ucfirst(str_replace('_', ' ', $var)) }}</label>
                            <input type="text" name="vars[{{ $var }}]" class="form-control"
                                   placeholder="Valeur pour @{{ $var }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-side">
                <div class="form-card">
                    <div class="form-card-header"><i class="fas fa-eye"></i> Aperçu du template</div>
                    <div class="form-card-body">
                        <div class="tpl-preview-mini">{!! Str::limit(strip_tags($template->contenu), 300) !!}</div>
                    </div>
                </div>

                <div class="form-card">
                    <div class="form-card-header"><i class="fas fa-circle-info"></i> Variables détectées</div>
                    <div class="form-card-body">
                        <div class="tpl-card-vars" style="flex-wrap:wrap;">
                            @foreach($variables as $v)
                                <span class="tpl-var">@{{ $v }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-intranet w-100">
                        <i class="fas fa-wand-magic-sparkles me-2"></i> Générer le document
                    </button>
                    <a href="{{ route('intranet.templates.show', $template) }}" class="btn btn-light w-100 mt-2">Annuler</a>
                </div>
            </div>
        </div>
    </form>
    @elseif(!$template->est_html)
    <div class="form-card">
        <div class="form-card-body text-center py-4">
            <i class="fas fa-file-arrow-down fa-3x text-muted mb-3 d-block"></i>
            <p>Ce template est un fichier modèle. Téléchargez-le et remplissez-le manuellement.</p>
            <a href="{{ route('intranet.templates.download', $template) }}" class="btn btn-intranet">
                <i class="fas fa-download me-2"></i> Télécharger le modèle
            </a>
        </div>
    </div>
    @else
    <div class="form-card">
        <div class="form-card-body text-center py-4">
            <i class="fas fa-circle-info fa-3x text-muted mb-3 d-block"></i>
            <p>Aucune variable détectée dans ce template.</p>
            <a href="{{ route('intranet.templates.show', $template) }}" class="btn btn-light">Retour</a>
        </div>
    </div>
    @endif

</div>
@endsection
