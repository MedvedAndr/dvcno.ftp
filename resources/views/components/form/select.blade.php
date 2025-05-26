@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    // 'value'             => null,
    // 'placeholder'       => null,
    'form'              => null,
    // 'autocomplete'      => 'off',
    'required'          => false,
    'disabled'          => false,
    // 'autofocus'         => false,
    'data'              => null,
    'options'           => [],
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

// Убираем дубликаты
$status = array_unique($status);

// Добавляем классы от иконок в class
$class = array_unique(array_merge($class, $icon_classes));
@endphp

<label
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="select"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">
        <select
            @isset($id) id="{{ $id }}" @endisset
            @isset($name) name="{{ $name }}" @endisset
            @isset($form) form="{{ $form }}" @endisset
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        >
            @foreach($options as $option)
            <option
                @isset($option['value']) value="{{ $option['value'] }}" @endisset
                @if(isset($option['selected']) && $option['selected'] === true) selected @endif
            >@isset($option['title']){{ $option['title'] }}@endisset</option>
            @endforeach
        </select>
        @if(!empty($icons))
        @foreach($icons as $icon_name)
        <span class="label__icon">
            <span data-icon="{{ $icon_name }}"></span>
        </span>
        @endforeach
        @endif
        <span class="select_chevron"><span data-icon></span></span>
    </span>
    @isset($message)
    <span class="label__message">{{ is_string($message) ? $message : '' }}</span>
    @endisset
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>