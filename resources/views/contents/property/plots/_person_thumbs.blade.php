@php
    /** @var string $type   seller|owner */
    /** @var \App\Models\PlotSeller|\App\Models\PlotOwner $person */
    $thumbs = ['nid_front' => 'NID Front', 'nid_back' => 'NID Back', 'photo' => 'Photo'];
    $items = [];
    foreach ($thumbs as $field => $label) {
        if (! empty($person->{$field})) {
            $items[] = [
                'url' => route('plots.people.image', [$type, $person->uuid, $field]),
                'name' => $label,
            ];
        }
    }
@endphp
@if (! empty($items))
    <div class="d-flex gap-1 align-items-center">
        @foreach ($items as $i => $item)
            <a href="{{ $item['url'] }}" title="{{ $item['name'] }}"
                data-media-view data-media-index="{{ $i }}" data-media-items='@json($items)'>
                <img src="{{ $item['url'] }}" alt="{{ $item['name'] }}"
                    class="rounded border" style="height:34px;width:34px;object-fit:cover;">
            </a>
        @endforeach
    </div>
@else
    <span class="text-muted">—</span>
@endif
