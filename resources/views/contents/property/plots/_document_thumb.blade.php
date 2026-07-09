{{--
    Small clickable document thumbnail (like the person NID thumbnails). Images
    render an image preview; PDFs render a PDF icon. Clicking opens the file in
    the global media modal. Pass $doc (an App\Models\Document).
--}}
@php $isImage = str_starts_with((string) $doc->mime_type, 'image/'); @endphp
<a href="{{ route('documents.preview', $doc->uuid) }}" class="d-inline-block"
    data-media-view data-media-url="{{ route('documents.preview', $doc->uuid) }}"
    data-media-name="{{ $doc->title }}" data-media-type="{{ $doc->mime_type }}" title="{{ $doc->title }}">
    @if ($isImage)
        <img src="{{ route('documents.preview', $doc->uuid) }}" alt="{{ $doc->title }}"
            class="rounded border" style="height:40px;width:40px;object-fit:cover;">
    @else
        <span class="rounded border d-inline-flex align-items-center justify-content-center bg-light text-danger"
            style="height:40px;width:40px;">
            <i class="mdi mdi-file-pdf-box mdi-24px"></i>
        </span>
    @endif
</a>
