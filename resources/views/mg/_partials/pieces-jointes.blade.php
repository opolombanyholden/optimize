@if(!empty($pieces) && $pieces->isNotEmpty())
<div class="row g-2">
    @foreach($pieces as $pj)
        @php
            $ext = strtolower(pathinfo($pj->nom_original ?? $pj->chemin, PATHINFO_EXTENSION));
            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
            $isPdf = $ext === 'pdf';
        @endphp
        <div class="col-md-4 col-lg-3">
            <div class="border rounded p-2 h-100 d-flex flex-column">
                @if($isImg)
                    <a href="{{ $pj->url }}" target="_blank"><img src="{{ $pj->url }}" alt="" class="w-100 rounded" style="max-height:150px;object-fit:cover;" loading="lazy"></a>
                @elseif($isPdf)
                    <a href="{{ $pj->url }}" target="_blank" class="text-center p-3 bg-light rounded text-decoration-none"><i class="fas fa-file-pdf fa-3x text-danger"></i></a>
                @else
                    <a href="{{ $pj->url }}" target="_blank" class="text-center p-3 bg-light rounded text-decoration-none"><i class="fas fa-file fa-3x text-muted"></i></a>
                @endif
                <small class="text-truncate mt-1" title="{{ $pj->nom_original }}">{{ $pj->nom_original }}</small>
                <small class="text-muted">{{ $pj->taille_humaine ?? '' }}</small>
            </div>
        </div>
    @endforeach
</div>
@endif
