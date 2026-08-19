@extends('layouts.app')
@section('title', 'Modifier — ' . $article->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li><li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index') }}">Wiki</a></li><li class="breadcrumb-item active">Modifier</li></ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4"><h1 class="page-title"><span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span> Modifier l'article</h1><p class="page-subtitle">{{ $article->titre }}</p></div>
    <form action="{{ route('intranet.wiki.update', $article) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') @include('intranet.wiki._form')</form>
</div>
@endsection
