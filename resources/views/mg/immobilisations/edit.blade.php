@extends('layouts.app')
@section('title', 'Modifier ' . $immobilisation->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('mg.immobilisations.index') }}">Immobilisations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mg.immobilisations.show', $immobilisation) }}">{{ $immobilisation->designation }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Modifier — {{ $immobilisation->designation }}</h1>
    @if($immobilisation->code)<code class="small text-muted">{{ $immobilisation->code }}</code>@endif
</div>

<form action="{{ route('mg.immobilisations.update', $immobilisation) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('mg.immobilisations._form')
</form>
@endsection
