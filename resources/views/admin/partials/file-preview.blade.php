@php $ext = strtolower(pathinfo($filePath ?? '', PATHINFO_EXTENSION)); @endphp
<div class="card mb-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">{{ $fileLabel }}</span>
        @if($filePath)
            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
        @endif
    </div>
    <div class="card-body p-0">
        @if(!$filePath)
            <p class="text-muted text-center my-4">Aucun fichier.</p>
        @elseif($ext === 'pdf')
            <iframe src="{{ $previewUrl }}" style="width:100%;height:72vh;border:0"></iframe>
        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            <img src="{{ $previewUrl }}" class="img-fluid d-block mx-auto" alt="{{ $fileLabel }}">
        @else
            <p class="text-muted text-center my-4">Aperçu indisponible pour ce format (.{{ $ext }}). Utilisez le bouton de téléchargement.</p>
        @endif
    </div>
</div>
