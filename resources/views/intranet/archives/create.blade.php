@extends('layouts.app')
@section('title', 'Archiver un document')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.archives.index') }}">Archives</a></li>
    <li class="breadcrumb-item active">Archiver</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-box-archive"></i></span> Archiver un document</h1>
        <p class="page-subtitle">Enregistrez un document dans le système d'archivage</p>
    </div>
    <form action="{{ route('intranet.archives.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @include('intranet.archives._form')
    </form>
</div>
@endsection
