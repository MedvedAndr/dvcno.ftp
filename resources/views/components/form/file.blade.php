@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    'form'              => null,
    'required'          => false,
    'disabled'          => false,
    'data'              => null,
    'title'             => null,
    'message'           => null,
    'icon'              => '',
    'icon_class'        => '',
    'status'            => '',
])

@php
// Обработка class
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));

// Обработка status
$status = is_string($status) ? trim(preg_replace('/\s+/', ' ', $status)) : '';
$status = $status === '' ? [] : array_unique(explode(' ', $status));

// Обработка icon и icon_class
$icons = is_string($icon) ? trim(preg_replace('/\s+/', ' ', $icon)) : '';
$icons = $icons === '' ? [] : explode(' ', $icons);

$icon_classes = is_string($icon_class) ? trim(preg_replace('/\s+/', ' ', $icon_class)) : '';
$icon_classes = $icon_classes === '' ? [] : explode(' ', $icon_classes);

// Добавляем префикс "icon__" к каждому классу из icon_class
$icon_classes = array_map(fn($cls) => "icon__{$cls}", $icon_classes);

// Добавляем статусы
if (!empty($icons)) {
    $status[] = 'iconed';
}

// Добавляем классы от иконок в class
$class = array_unique(array_merge($class, $icon_classes));
@endphp

<label
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="file"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">
        <input
            @isset($id) id="{{ $id }}" @endisset
            type="file"
            @isset($name) name="{{ $name }}" @endisset
            @isset($form) form="{{ $form }}" @endisset
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        />
        @if(!empty($icons))
        @foreach($icons as $icon_name)
        <span class="label__icon">
            <span data-icon="{{ $icon_name }}"></span>
        </span>
        @endforeach
        @endif
    </span>
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>