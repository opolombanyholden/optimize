@extends('layouts.app')
@section('title', 'Nouvel engagement fournisseur')
@section('content')
<div class="page-header mb-3">
    <h1 class="h4"><i class="fas fa-file-contract me-2" style="color:#0A66C2;"></i>Nouvel engagement fournisseur</h1>
</div>
<form action="{{ route('appro.contrats.store') }}" method="POST" enctype="multipart/form-data">
    @include('appro.contrats._form')
</form>
@endsection
