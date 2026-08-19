@extends('layouts.app')
@section('title', 'Modifier — ' . $media->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mediatheque.index') }}">Médiathèque</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier le média
        </h1>
        <p class="page-subtitle">{{ $media->titre }}</p>
    </div>
    <form action="{{ route('intranet.mediatheque.update', $media) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.mediatheque._form')
    </form>
</div>
@endsection
