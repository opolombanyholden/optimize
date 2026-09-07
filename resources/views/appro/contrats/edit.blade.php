@extends('layouts.app')
@section('title', 'Modifier — '.$contrat->reference)
@section('content')
<div class="page-header mb-3">
    <h1 class="h4"><i class="fas fa-file-contract me-2" style="color:#0A66C2;"></i>Modifier {{ $contrat->reference }}</h1>
</div>
<form action="{{ route('appro.contrats.update', $contrat) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('appro.contrats._form')
</form>
@endsection
