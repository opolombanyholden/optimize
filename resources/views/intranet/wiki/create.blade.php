@extends('layouts.app')
@section('title', 'Nouvel article Wiki')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li><li class="breadcrumb-item"><a href="{{ route('intranet.wiki.index') }}">Wiki</a></li><li class="breadcrumb-item active">Nouveau</li></ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4"><h1 class="page-title"><span class="page-title-icon"><i class="fas fa-book-open"></i></span> Nouvel article</h1></div>
    <form action="{{ route('intranet.wiki.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('intranet.wiki._form')</form>
</div>
@endsection
