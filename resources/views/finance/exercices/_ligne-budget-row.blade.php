@php
    $dot  = (float) ($l->dotation_etat ?? 0);
    $fp   = (float) ($l->fonds_propres ?? 0);
    $repB = (float) ($l->reports_budgetaire ?? 0);
    $repT = (float) ($l->reports_tresorerie ?? 0);
    $total = $dot + $fp + $repB + $repT;
@endphp
<tr class="ligne-row" data-id="{{ $l->id }}">
    <td>
        <input type="hidden" name="lignes[{{ $i }}][id]" value="{{ $l->id }}" class="input-id">
        <input type="hidden" name="lignes[{{ $i }}][_delete]" value="0" class="input-delete">
        <input type="hidden" name="lignes[{{ $i }}][id_codeanalytique]" value="{{ $l->id_codeanalytique }}">
        @if($lock)
            <code>{{ $l->id_budgetligne }}</code>
        @else
            <input type="text" name="lignes[{{ $i }}][id_budgetligne]" class="form-control form-control-sm" value="{{ $l->id_budgetligne }}">
        @endif
    </td>
    <td>
        @if($lock)
            {{ $l->commentaire ?? $l->ligne?->libelle ?? '—' }}
            @if($l->ligne)<br><small class="text-muted">{{ $l->ligne->id_codeanalytique }} {{ $l->ligne->libelle }}</small>@endif
        @else
            <input type="text" name="lignes[{{ $i }}][commentaire]" class="form-control form-control-sm" value="{{ $l->commentaire ?? $l->ligne?->libelle ?? '' }}">
            @if($l->ligne)<small class="text-muted">{{ $l->ligne->id_codeanalytique }} {{ $l->ligne->libelle }}</small>@endif
        @endif
    </td>
    <td class="text-end">
        @if($lock)
            {{ number_format($dot, 0, ',', ' ') }}
        @else
            <input type="number" step="0.01" min="0" name="lignes[{{ $i }}][dotation_etat]"
                   class="form-control form-control-sm text-end numeric input-dot" value="{{ $dot }}">
        @endif
    </td>
    <td class="text-end">
        @if($lock)
            {{ number_format($fp, 0, ',', ' ') }}
        @else
            <input type="number" step="0.01" min="0" name="lignes[{{ $i }}][fonds_propres]"
                   class="form-control form-control-sm text-end numeric input-fp" value="{{ $fp }}">
        @endif
    </td>
    <td class="text-end">
        @if($lock)
            {{ number_format($repB, 0, ',', ' ') }}
        @else
            <input type="number" step="0.01" min="0" name="lignes[{{ $i }}][reports_budgetaire]"
                   class="form-control form-control-sm text-end numeric input-repb" value="{{ $repB }}">
        @endif
    </td>
    <td class="text-end">
        @if($lock)
            {{ number_format($repT, 0, ',', ' ') }}
        @else
            <input type="number" step="0.01" min="0" name="lignes[{{ $i }}][reports_tresorerie]"
                   class="form-control form-control-sm text-end numeric input-rept" value="{{ $repT }}">
        @endif
    </td>
    <td class="text-end fw-bold cell-total">{{ number_format($total, 0, ',', ' ') }}</td>
    @if(!$lock)
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-supprimer" title="Supprimer"><i class="fas fa-trash"></i></button>
        </td>
    @endif
</tr>
