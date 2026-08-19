@extends('layouts.app')
@section('title', 'Nouveau template')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-file-circle-plus"></i></span> Nouveau template</h1>
        <p class="page-subtitle">Créez un modèle de document réutilisable</p>
    </div>
    <form action="{{ route('intranet.templates.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @include('intranet.templates._form')
    </form>
</div>
@endsection
