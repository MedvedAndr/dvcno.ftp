@props([
    'id'            => null,
    'class'         => '',
    'name'          => null,
    'disabled'      => false,
    'data'          => null,
    'title'         => null,
    'icon'          => '',
    'icon_class'    => '',
])

@php
// Обработка class
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));

// Обработка icon и icon_class
$icons = is_string($icon) ? trim(preg_replace('/\s+/', ' ', $icon)) : '';
$icons = $icons === '' ? [] : explode(' ', $icons);

$icon_classes = is_string($icon_class) ? trim(preg_replace('/\s+/', ' ', $icon_class)) : '';
$icon_classes = $icon_classes === '' ? [] : explode(' ', $icon_classes);

// Добавляем префикс "icon__" к каждому классу из icon_class
$icon_classes = array_map(fn($cls) => "icon__{$cls}", $icon_classes);

// Добавляем классы от иконок в class
$class = array_unique(array_merge($class, $icon_classes));
@endphp

<button
    @isset($id) id="{{ $id }}" @endisset
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    type="submit"
    @isset($name) name="{{ $name }}" @endisset
    @if($disabled) disabled @endif
    @if($data)
    @foreach($data as $data_name => $data_value)
    data-{{ $data_name }}="{{ $data_value }}"
    @endforeach
    @endif
>
    @isset($title)
    <span class="button__title">{{ $title }}</span>
    @endisset

    @if(!empty($icons))
    @foreach($icons as $icon_name)
    <span class="button__icon">
        <span data-icon="{{ $icon_name }}"></span>
    </span>
    @endforeach
    @endif
</button>