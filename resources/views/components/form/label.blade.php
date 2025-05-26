@props([
    'id'            => null,
    'class'         => '',
    'for'           => null,
    'title'         => null,
    'icon'          => '',
])

@php
// Обработка class
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));

// Обработка icon
$icons = is_string($icon) ? trim(preg_replace('/\s+/', ' ', $icon)) : '';
$icons = $icons === '' ? [] : explode(' ', $icons);
@endphp

<label
    @isset($id) id="{{ $id }}" @endisset
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    @isset($for) for="{{ $for }}" @endisset
    data-label="label"
>
    @if(!empty($icons))
    @foreach($icons as $icon_name)
    <span class="label__icon">
        <span data-icon="{{ $icon_name }}"></span>
    </span>
    @endforeach
    @endif

    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>