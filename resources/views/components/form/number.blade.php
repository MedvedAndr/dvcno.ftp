@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    'value'             => null,
    'placeholder'       => null,
    'min'               => null,
    'max'               => null,
    'step'              => null,
    'form'              => null,
    // 'autocomplete'      => 'off',
    'required'          => false,
    'disabled'          => false,
    // 'autofocus'         => false,
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

// Убираем дубликаты
if (!is_null($value) && $value !== '') {
    $status[] = 'not_empty';
}

$status = array_unique($status);

// Добавляем классы от иконок в class
$class = array_unique(array_merge($class, $icon_classes));
@endphp

<label
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="number"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">    
        <input
            @isset($id) id="{{ $id }}" @endisset
            type="number"
            @isset($name) name="{{ $name }}" @endisset
            @isset($value) value="{{ $value }}" @endisset
            @isset($min) min="{{ $min }}" @endisset
            @isset($max) max="{{ $max }}" @endisset
            @isset($step) step="{{ $step }}" @endisset
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
        <span class="number_chevron_up"><span data-icon></span></span>
        <span class="number_chevron_down"><span data-icon></span></span>
    </span>
    @isset($message)
    <span class="label__message">{{ is_string($message) ? $message : '' }}</span>
    @endisset
    @isset($placeholder)
    <span class="label__placeholder">{{ $placeholder }}</span>
    @endisset
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>