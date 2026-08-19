<tr>
    <td>
        <select name="lignes[{{ $index }}][produit_id]" class="form-select form-select-sm produit-select" required>
            <option value="">— Sélectionner un article du catalogue —</option>
            @foreach($produits ?? [] as $p)
                <option value="{{ $p->id }}" @if($ligne && $ligne->produit_id == $p->id) selected @endif
                        data-unite="{{ $p->unite_mesure }}">{{ $p->code ? "[$p->code] " : '' }}{{ $p->designation }} ({{ \App\Models\Produit::TYPES[$p->type_article] ?? '' }})</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" step="0.001" min="0.001" name="lignes[{{ $index }}][quantite_demandee]" class="form-control form-control-sm text-end" value="{{ $ligne->quantite_demandee ?? 1 }}" required></td>
    <td class="unite-cell"><small class="text-muted">{{ $ligne->unite ?? ($ligne && $ligne->produit ? $ligne->produit->unite_mesure : '') }}</small></td>
    <td><input type="text" name="lignes[{{ $index }}][commentaire]" class="form-control form-control-sm" value="{{ $ligne->commentaire ?? '' }}" maxlength="500"></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-ligne"><i class="fas fa-times"></i></button></td>
</tr>
