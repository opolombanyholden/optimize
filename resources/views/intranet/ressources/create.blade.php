@extends('layouts.app')
@section('title', ($type === 'dossier' ? 'Nouveau dossier' : 'Ajouter un fichier'))

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.ressources.index') }}">Ressources</a></li>
    <li class="breadcrumb-item active">{{ $type === 'dossier' ? 'Nouveau dossier' : 'Ajouter' }}</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas {{ $type === 'dossier' ? 'fa-folder-plus' : 'fa-file-arrow-up' }}"></i></span>
            {{ $type === 'dossier' ? 'Nouveau dossier' : 'Ajouter un fichier' }}
        </h1>
        @if($parent)
        <p class="page-subtitle">Dans le dossier : {{ $parent->titre }}</p>
        @endif
    </div>

    <form action="{{ route('intranet.ressources.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.ressources._form')
    </form>
</div>
@endsection
