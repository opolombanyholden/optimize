@extends('layouts.app')
@section('title', 'Nouvelle source')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle source de financement</h1>
    <a href="{{ route('finance.v2.sources.index') }}" class="btn btn-outline-secondary">Retour</a>
</div>
<form action="{{ route('finance.v2.sources.store') }}" method="POST">@csrf @include('finance.v2.sources._form')</form>
@endsection
