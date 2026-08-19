@extends('layouts.app')

@section('title', 'Nouveau paiement')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.payements.index') }}">Payements</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-money-bill-transfer me-2 text-muted"></i>Nouveau paiement</h1>
        <p class="text-muted mb-0">Enregistrer un reglement de bulletin de paie</p>
    </div>
    <a href="{{ route('rh.payements.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Erreur :</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $paiesData = [];
    foreach ($paies as $p) {
        $paiesData[$p->id] = [
            'employee_id'    => $p->employee_id,
            'employee_label' => trim(($p->employee?->noms ?? '') . ' ' . ($p->employee?->prenoms ?? '')),
            'reste_a_payer'  => $p->reste_a_payer ?? $p->net_a_payer ?? 0,
        ];
    }
@endphp

<form method="POST" action="{{ route('rh.payements.store') }}">
    @csrf

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card data-card">
                <div class="card-header">
                    <h5><i class="fas fa-file-invoice-dollar me-2 text-muted"></i>Bulletin et employe</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="paie_id" class="form-label">Bulletin de paie <span class="text-danger">*</span></label>
                            <select name="paie_id" id="paie_id" class="form-select" required>
                                <option value="">-- Selectionner un bulletin --</option>
                                @foreach($paies as $p)
                                    <option value="{{ $p->id }}"
                                        {{ old('paie_id', $paie?->id) == $p->id ? 'selected' : '' }}>
                                        {{ $p->label }} - {{ $p->employee?->noms }} {{ $p->employee?->prenoms }} - Reste a payer {{ number_format($p->reste_a_payer ?? $p->net_a_payer ?? 0, 0, ',', ' ') }} XAF
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="employee_label_display" class="form-label">Employe</label>
                            <input type="text" id="employee_label_display" class="form-control"
                                   value="{{ $paie?->employee ? trim($paie->employee->noms . ' ' . $paie->employee->prenoms) : '' }}"
                                   disabled>
                            <input type="hidden" name="employee_id" id="employee_id"
                                   value="{{ old('employee_id', $paie?->employee_id) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card data-card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-money-check-alt me-2 text-muted"></i>Details du paiement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="date_payement" class="form-label">Date du paiement <span class="text-danger">*</span></label>
                            <input type="date" name="date_payement" id="date_payement" class="form-control"
                                   value="{{ old('date_payement', now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="montant" class="form-label">Montant (XAF) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="montant" id="montant" class="form-control"
                                   value="{{ old('montant', $paie?->reste_a_payer ?? '') }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="mode_payement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                            <select name="mode_payement" id="mode_payement" class="form-select" required>
                                @foreach(\App\Models\Payement::MODES as $key => $label)
                                    <option value="{{ $key }}" {{ old('mode_payement') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="reference_payement" class="form-label">Reference</label>
                            <input type="text" name="reference_payement" id="reference_payement" class="form-control"
                                   value="{{ old('reference_payement') }}" placeholder="N de cheque, transaction...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card data-card">
                <div class="card-header">
                    <h5><i class="fas fa-university me-2 text-muted"></i>Coordonnees bancaires</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="banque" class="form-label">Banque</label>
                        <input type="text" name="banque" id="banque" class="form-control"
                               value="{{ old('banque') }}">
                    </div>
                    <div class="mb-0">
                        <label for="iban" class="form-label">IBAN / Numero de compte</label>
                        <input type="text" name="iban" id="iban" class="form-control"
                               value="{{ old('iban') }}">
                    </div>
                </div>
            </div>

            <div class="card data-card mt-4">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Enregistrer le paiement
                    </button>
                    <a href="{{ route('rh.payements.index') }}" class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    const paiesData = @json($paiesData);
    const paieSelect = document.getElementById('paie_id');
    const employeeIdInput = document.getElementById('employee_id');
    const employeeDisplay = document.getElementById('employee_label_display');
    const montantInput = document.getElementById('montant');

    paieSelect?.addEventListener('change', function() {
        const id = this.value;
        if (id && paiesData[id]) {
            employeeIdInput.value = paiesData[id].employee_id ?? '';
            employeeDisplay.value = paiesData[id].employee_label ?? '';
            if (!montantInput.dataset.touched) {
                montantInput.value = paiesData[id].reste_a_payer ?? '';
            }
        } else {
            employeeIdInput.value = '';
            employeeDisplay.value = '';
        }
    });

    montantInput?.addEventListener('input', function() {
        this.dataset.touched = '1';
    });
})();
</script>
@endsection
