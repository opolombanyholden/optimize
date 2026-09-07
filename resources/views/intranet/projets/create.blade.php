@extends('layouts.app')
@section('title', 'Nouveau projet')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;"><li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li><li class="breadcrumb-item"><a href="{{ route('intranet.projets.index') }}">Projets</a></li><li class="breadcrumb-item active">Nouveau</li></ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4"><h1 class="page-title"><span class="page-title-icon"><i class="fas fa-diagram-project"></i></span> Nouveau projet</h1></div>
    <form action="{{ route('intranet.projets.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('intranet.projets._form')</form>
</div>
@endsection
