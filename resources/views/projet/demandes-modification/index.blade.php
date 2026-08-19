@extends('layouts.app')
@section('title', 'Demandes de modification')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item active">Demandes de modification</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #F59E0B;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#F59E0B,#D97706);"><i class="fas fa-pen-fancy"></i></span>
                Demandes de modification / suppression
            </h1>
            <p class="page-subtitle">Approbation des modifications et suppressions sur les entités verrouillées</p>
        </div>
    </div>

    @if($demandes->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Entité</th>
                    <th>Demande</th>
                    <th>Motif</th>
                    <th>Demandeur</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($demandes as $d)
                <tr>
                    <td>
                        <span class="opp-stage-badge" style="background:{{ $d->type_demande === 'modification' ? '#F59E0B' : '#DC2626' }};font-size:.65rem;">
                            {{ $d->type_libelle }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size:.75rem;color:#64748B;">{{ $d->entite_type_libelle }}</div>
                        <strong>{{ $d->entite_nom }}</strong>
                    </td>
                    <td><span class="opp-stage-badge" style="background:{{ $d->statut_couleur }};font-size:.6rem;">{{ $d->statut_libelle }}</span></td>
                    <td style="max-width:250px;font-size:.78rem;">{{ Str::limit($d->motif, 80) }}</td>
                    <td>{{ $d->demandeur?->prenoms }} {{ $d->demandeur?->name }}</td>
                    <td style="font-size:.75rem;">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="{{ route('projet.demandes.approuver', $d) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn-action success btn-sm" title="Approuver" onclick="return confirm('Approuver cette demande ?')"><i class="fas fa-check"></i></button>
                            </form>
                            <button class="btn-action danger btn-sm" title="Rejeter" onclick="rejeterDemande({{ $d->id }})"><i class="fas fa-times"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#FFFBEB;"><i class="fas fa-check-circle" style="color:#16A34A;"></i></div>
        <h3>Aucune demande en attente</h3>
        <p>Toutes les demandes ont été traitées.</p>
    </div>
    @endif
</div>

{{-- Modale rejet --}}
<div class="modal fade" id="modalRejetDemande" tabindex="-1">
    <div class="modal-dialog">
        <form id="rejetDemandeForm" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#DC2626,#B91C1C);">
                <h5 class="modal-title text-white"><i class="fas fa-times me-2"></i> Rejeter la demande</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                    <textarea name="commentaire" rows="3" class="form-control" required minlength="5"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-2"></i> Rejeter</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function rejeterDemande(demandeId) {
    document.getElementById('rejetDemandeForm').action = '/projet/demandes/' + demandeId + '/rejeter';
    new bootstrap.Modal(document.getElementById('modalRejetDemande')).show();
}
</script>
@endpush
@endsection
