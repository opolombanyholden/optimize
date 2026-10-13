@extends('layouts.app')

@section('title', 'Mails professionnels')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Mails professionnels</li>
</ol>
@endsection

@section('content')
<div class="card data-card" style="max-width:640px; margin:3rem auto;">
    <div class="card-body text-center p-5">
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;border-radius:16px;background:#EEF2FF;color:#4F46E5;font-size:1.6rem;"><i class="fas fa-at"></i></div>
        <h1 class="h4 mb-2">Connectez votre messagerie professionnelle</h1>
        <p class="text-muted mb-4">Ajoutez un ou plusieurs comptes mail (Gmail, Outlook / Microsoft 365, OVH, Zoho, serveur de votre entreprise…) depuis votre profil pour lire et envoyer vos mails sans quitter OptimiZe.</p>
        <a href="{{ route('mon-compte.index') }}#comptes-mail" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Connecter un compte mail</a>
    </div>
</div>
@endsection
