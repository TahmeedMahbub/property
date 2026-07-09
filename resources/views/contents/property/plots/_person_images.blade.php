@php
    /** @var string $group  sellers|owners */
    /** @var string $type   seller|owner */
    /** @var int    $i */
    /** @var array  $person */
    $uuid = $person['uuid'] ?? null;
    $fields = ['nid_front' => 'NID Front', 'nid_back' => 'NID Back', 'photo' => 'Photo'];
@endphp
<div class="row g-2 mt-1">
    @foreach ($fields as $field => $label)
        @php $path = $existingImage($person, $field); @endphp
        <div class="col-md-4">
            <label class="form-label small text-muted">{{ $label }}</label>
            @if ($uuid && $path)
                <div class="mb-1">
                    <a href="{{ route('plots.people.image', [$type, $uuid, $field]) }}"
                        data-media-view data-media-name="{{ $label }}">
                        <img src="{{ route('plots.people.image', [$type, $uuid, $field]) }}" alt="{{ $label }}"
                            class="rounded border" style="height:44px;width:auto;max-width:100%;object-fit:cover;">
                    </a>
                </div>
                <input type="hidden" name="{{ $group }}[{{ $i }}][{{ $field }}_existing]" value="{{ $path }}">
            @endif
            <input type="hidden" name="{{ $group }}[{{ $i }}][uuid]" value="{{ $uuid }}">
            <input type="file" accept="image/*" class="form-control form-control-sm" name="{{ $group }}[{{ $i }}][{{ $field }}]">
        </div>
    @endforeach
</div>
