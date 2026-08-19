@extends('layouts.app')
@section('title', 'Modifier — ' . $template->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span> Modifier le template</h1>
        <p class="page-subtitle">{{ $template->titre }}</p>
    </div>
    <form action="{{ route('intranet.templates.update', $template) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT') @include('intranet.templates._form')
    </form>
</div>
@endsection
