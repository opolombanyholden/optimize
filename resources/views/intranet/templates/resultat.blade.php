@extends('layouts.app')
@section('title', 'Document généré — ' . $template->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">Document généré</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: #16A34A;"><i class="fas fa-check-circle"></i></span>
                Document généré
            </h1>
            <p class="page-subtitle">À partir du template « {{ $template->titre }} »</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-intranet">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
            <a href="{{ route('intranet.templates.utiliser', $template) }}" class="btn btn-light">
                <i class="fas fa-rotate me-2"></i> Regénérer
            </a>
            <a href="{{ route('intranet.templates.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Templates
            </a>
        </div>
    </div>

    <div class="tpl-result-container">
        <div class="tpl-result-page" id="printArea">
            {!! $html !!}
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .tpl-result-container {
        background: #64748B;
        border-radius: 14px;
        padding: 2rem;
        display: flex;
        justify-content: center;
    }
    .tpl-result-page {
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        padding: 25mm 20mm;
        box-shadow: 0 8px 40px rgba(0,0,0,.25);
        font-family: 'Inter', serif;
        font-size: 11pt;
        line-height: 1.7;
        color: #1E293B;
    }
    .tpl-result-page h1 { font-size: 20pt; margin-bottom: .8em; }
    .tpl-result-page h2 { font-size: 16pt; margin-top: 1.2em; }
    .tpl-result-page h3 { font-size: 13pt; }
    .tpl-result-page p  { margin-bottom: .6em; }
    .tpl-result-page table { border-collapse: collapse; width: 100%; }
    .tpl-result-page table th, .tpl-result-page table td { border: 1px solid #CBD5E1; padding: 6px 10px; }

    @media print {
        .sidebar, .top-navbar, .page-header-intranet, .tpl-result-container { all: unset; }
        .main-content { margin: 0; padding: 0; }
        .tpl-result-page { box-shadow: none; padding: 15mm; width: auto; }
    }
    @media (max-width: 768px) {
        .tpl-result-page { width: 100%; min-height: auto; padding: 1.5rem; }
    }
</style>
@endpush
