@extends('layouts.app')
@section('title', 'Modifier ' . $source->label)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Modifier la source « {{ $source->label }} »</h1>
    <a href="{{ route('finance.v2.sources.index') }}" class="btn btn-outline-secondary">Retour</a>
</div>
<form action="{{ route('finance.v2.sources.update', $source) }}" method="POST">@csrf @method('PUT') @include('finance.v2.sources._form')</form>
@endsection
