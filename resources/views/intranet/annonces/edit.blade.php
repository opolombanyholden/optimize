@extends('layouts.app')

@section('title', 'Modifier — ' . $annonce->title)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annonces.index') }}">Annonces</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier l'annonce
        </h1>
        <p class="page-subtitle">{{ $annonce->title }}</p>
    </div>

    <form action="{{ route('intranet.annonces.update', $annonce) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.annonces._form')
    </form>
</div>
@endsection
