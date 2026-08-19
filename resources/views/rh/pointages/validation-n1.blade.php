@extends('layouts.app')

@section('title', 'Validation N+1 des pointages')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Validation N+1</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-user-shield me-2 text-muted"></i>Validation des pointages télétravail</h1>
        <p class="text-muted mb-0">
            @if($estSuperAdmin)
                Vue super-admin : tous les pointages en attente.
            @else
                Pointages de vos collaborateurs en attente de validation.
            @endif
        </p>
    </div>
    <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour aux pointages</a>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employé</th>
                    <th>Entrée / Sortie</th>
                    <th class="text-end">Heures</th>
                    <th>Mode</th>
                    <th>IP</th>
                    <th class="text-end">Décision</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pointages as $p)
                    <tr>
                        <td>{{ $p->date->translatedFormat('D d M Y') }}</td>
                        <td>
                            <strong>{{ $p->employee->noms }}</strong> {{ $p->employee->prenoms }}
                            <br><small class="text-muted">{{ $p->employee->matricule }}</small>
                        </td>
                        <td>
                            <i class="fas fa-sign-in-alt text-success me-1"></i> {{ $p->heure_entree ? substr($p->heure_entree, 0, 5) : '—' }}
                            @if($p->heure_sortie)
                                <br><i class="fas fa-sign-out-alt text-info me-1"></i> {{ substr($p->heure_sortie, 0, 5) }}
                            @endif
                        </td>
                        <td class="text-end">
                            <strong>{{ number_format($p->total, 2, ',', ' ') }} h</strong>
                            @if($p->h_sup > 0)
                                <br><small class="text-warning">(+{{ number_format($p->h_sup, 2, ',', ' ') }} sup)</small>
                            @endif
                        </td>
                        <td><span class="badge bg-warning text-dark"><i class="fas fa-house-laptop me-1"></i>{{ $p->mode_libelle }}</span></td>
                        <td><code class="small">{{ $p->ip_address ?? '—' }}</code></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mApprouve{{ $p->id }}">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#mRejete{{ $p->id }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- Modal approuver --}}
                    <div class="modal fade" id="mApprouve{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('pointage.validation-n1.action', $p) }}" method="POST" class="modal-content">
                                @csrf
                                <input type="hidden" name="decision" value="approuve">
                                <div class="modal-header">
                                    <h5 class="modal-title">Approuver le pointage</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Approuver le pointage de <strong>{{ $p->employee->noms }} {{ $p->employee->prenoms }}</strong> du {{ $p->date->format('d/m/Y') }} ({{ number_format($p->total, 2, ',', ' ') }} h) ?</p>
                                    <div class="mb-0">
                                        <label class="form-label">Commentaire (optionnel)</label>
                                        <textarea name="commentaire" rows="2" class="form-control" maxlength="500"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-success">Approuver</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Modal rejeter --}}
                    <div class="modal fade" id="mRejete{{ $p->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('pointage.validation-n1.action', $p) }}" method="POST" class="modal-content">
                                @csrf
                                <input type="hidden" name="decision" value="rejete">
                                <div class="modal-header">
                                    <h5 class="modal-title">Rejeter le pointage</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Rejeter le pointage de <strong>{{ $p->employee->noms }} {{ $p->employee->prenoms }}</strong> du {{ $p->date->format('d/m/Y') }} ?</p>
                                    <div class="mb-0">
                                        <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                                        <textarea name="commentaire" rows="3" class="form-control" maxlength="500" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-danger">Rejeter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Aucun pointage en attente de validation.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pointages->hasPages())
        <div class="card-footer">{{ $pointages->links() }}</div>
    @endif
</div>
@endsection
