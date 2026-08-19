@extends('layouts.app')

@section('title', $success ? 'Pointage enregistré' : 'Pointage échec')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card data-card text-center mt-5 border-{{ $success ? 'success' : 'danger' }}">
            <div class="card-body p-5">
                @if($success)
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success text-white mb-3"
                          style="width:84px;height:84px;font-size:2.4rem;">
                        <i class="fas fa-check"></i>
                    </span>
                    <h4>Pointage enregistré</h4>
                    <p class="mb-3">{{ $message }}</p>
                    @if(isset($pointage))
                        <div class="d-flex justify-content-center gap-4 mt-3">
                            <div>
                                <div class="text-muted small">Entrée</div>
                                <strong class="text-success">{{ $pointage->heure_entree ? substr($pointage->heure_entree, 0, 5) : '—' }}</strong>
                            </div>
                            <div>
                                <div class="text-muted small">Sortie</div>
                                <strong class="text-info">{{ $pointage->heure_sortie ? substr($pointage->heure_sortie, 0, 5) : '—' }}</strong>
                            </div>
                        </div>
                    @endif
                @else
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-danger text-white mb-3"
                          style="width:84px;height:84px;font-size:2.4rem;">
                        <i class="fas fa-times"></i>
                    </span>
                    <h4>Échec du pointage</h4>
                    <p class="mb-0">{{ $message }}</p>
                @endif
                <hr>
                <small class="text-muted">{{ $employee->noms }} {{ $employee->prenoms }} · {{ now()->translatedFormat('d M Y H:i') }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
