@extends('layouts.app')
@section('title', 'Modifier — ' . $intervention->label)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('mg.interventions.index') }}">Interventions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mg.interventions.show', $intervention) }}">{{ $intervention->label }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Modifier — {{ $intervention->label }}</h1>
</div>

<form action="{{ route('mg.interventions.update', $intervention) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('mg.interventions._form')
</form>
@endsection
