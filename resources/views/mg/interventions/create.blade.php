@extends('layouts.app')
@section('title', 'Nouvelle intervention')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.interventions.index') }}">Interventions</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Planifier une intervention</h1>
</div>

<form action="{{ route('mg.interventions.store') }}" method="POST" enctype="multipart/form-data">
    @include('mg.interventions._form')
</form>
@endsection
