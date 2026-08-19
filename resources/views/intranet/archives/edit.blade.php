@extends('layouts.app')
@section('title', 'Modifier — ' . $archive->reference)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.archives.index') }}">Archives</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span> Modifier l'archive</h1>
        <p class="page-subtitle">{{ $archive->reference }} — {{ $archive->titre }}</p>
    </div>
    <form action="{{ route('intranet.archives.update', $archive) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT') @include('intranet.archives._form')
    </form>
</div>
@endsection
