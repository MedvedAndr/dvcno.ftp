@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    'value'             => null,
    'placeholder'       => null,
    'form'              => null,
    'required'          => false,
    'disabled'          => false,
    'autofocus'         => false,
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
if (!is_null($value) && $value !== '') {
    $status[] = 'not_empty';
}

if ($autofocus) {
    $status[] = 'focused';
}

if (!empty($icons)) {
    $status[] = 'iconed';
}

// Убираем дубликаты
$status = array_unique($status);

// Добавляем классы от иконок в class
$class = array_unique(array_merge($class, $icon_classes));

$timestamp = isset($value) ? strtotime($value) : null;
@endphp

<label
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="datetime"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    @isset($message)
    <span class="label__message">{{ is_string($message) ? $message : '' }}</span>
    @endisset
    @isset($placeholder)
    <span class="label__placeholder">{{ $placeholder }}</span>
    @endisset
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
    <span class="label__input">
        <input
            @isset($id) id="{{ $id }}" @endisset    
            type="hidden"
            @isset($name) name="{{ $name }}" @endisset
            @isset($value) value="{!! $value !!}" @endisset
            @isset($form) form="{{ $form }}" @endisset
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($autofocus) autofocus @endif
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        />
        <span class="label__display">
            <span data-default="дд" data-segment="day" tabindex="0">{{ is_null($timestamp) ? 'дд' : date('d', $timestamp) }}</span>
            <span class="delimiter">.</span>
            <span data-default="мм" data-segment="month" tabindex="0">{{ is_null($timestamp) ? 'мм' : date('m', $timestamp) }}</span>
            <span class="delimiter">.</span>
            <span data-default="гггг" data-segment="year" tabindex="0">{{ is_null($timestamp) ? 'гггг' : date('Y', $timestamp) }}</span>
            <span class="delimiter">&nbsp;</span>
            <span data-default="--" data-segment="hour" tabindex="0">{{ is_null($timestamp) ? 'чч' : date('H', $timestamp) }}</span>
            <span class="delimiter">:</span>
            <span data-default="--" data-segment="minute" tabindex="0">{{ is_null($timestamp) ? 'мм' : date('i', $timestamp) }}</span>
            <span class="delimiter">:</span>
            <span data-default="--" data-segment="second" tabindex="0">{{ is_null($timestamp) ? 'сс' : date('s', $timestamp) }}</span>
        </span>
    </span>
    <span class="label__calendar_icon" data-icon="calendar" tabindex="0"></span>
    <span class="label__calendar"></span>
</label>
{{-- Провер очка --}}