{{--
    Renders <option>/<optgroup> markup for the document category selects on the
    plot form. Categories come from the database (parent → children). Pass
    $documentCategories (collection of root categories with children loaded) and
    optionally $selected (a category id) to pre-select.
--}}
@foreach ($documentCategories as $cat)
    @if ($cat->children->isNotEmpty())
        <optgroup label="{{ $cat->name }}">
            @foreach ($cat->children as $child)
                <option value="{{ $child->id }}" @selected((string) ($selected ?? '') === (string) $child->id)>{{ $child->name }}</option>
            @endforeach
        </optgroup>
    @else
        <option value="{{ $cat->id }}" data-other="{{ $cat->slug === 'other-document' ? '1' : '0' }}" @selected((string) ($selected ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
    @endif
@endforeach
