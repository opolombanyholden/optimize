@extends('layouts.app')
@section('title', 'Nouvel article')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.news.index') }}">Actualités</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-newspaper"></i></span>
            Nouvel article
        </h1>
        <p class="page-subtitle">Rédigez un article pour le magazine interne</p>
    </div>

    <form action="{{ route('intranet.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.news._form')
    </form>
</div>
@endsection
