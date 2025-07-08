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
//dump($value);
$timestamp = (isset($value) && !is_null($value) && !empty($value)) ? strtotime($value) : null;
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
            @if(isset($value) && !is_null($value)) value="{!! $value !!}" @endif 
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
            <span data-default="--" data-segment="hour" tabindex="0">{{ is_null($timestamp) ? '--' : date('H', $timestamp) }}</span>
            <span class="delimiter">:</span>
            <span data-default="--" data-segment="minute" tabindex="0">{{ is_null($timestamp) ? '--' : date('i', $timestamp) }}</span>
            <span class="delimiter">:</span>
            <span data-default="--" data-segment="second" tabindex="0">{{ is_null($timestamp) ? '--' : date('s', $timestamp) }}</span>
        </span>
    </span>
    <span class="label__calendar_icon" tabindex="0"><span data-icon="calendar"></span></span>
    <span class="label__calendar">
        <span class="calendar__date">
            <span class="calendar__panel">
                <span class="calendar__month">
                    <span class="calendar__month_prev"><span data-icon="chevron-left"></span></span>
                    <span class="calendar__month_display">
                        <span data-value="01" data-status="active">Январь</span>
                        <span data-value="02">Февраль</span>
                        <span data-value="03">Март</span>
                        <span data-value="04">Апрель</span>
                        <span data-value="05">Май</span>
                        <span data-value="06">Июнь</span>
                        <span data-value="07">Июль</span>
                        <span data-value="08">Август</span>
                        <span data-value="09">Сентябрь</span>
                        <span data-value="10">Октябрь</span>
                        <span data-value="11">Ноябрь</span>
                        <span data-value="12">Декабрь</span>
                    </span>
                    <span class="calendar__month_next"><span data-icon="chevron-right"></span></span>
                </span>
                <span class="calendar__year">
                    <span class="calendar__year_prev"><span data-icon="chevron-left"></span></span>
                    <span class="calendar__year_display">2025</span>
                    <span class="calendar__year_next"><span data-icon="chevron-right"></span></span>
                </span>
            </span>
            <span class="calendar__weekdays">
                <span class="calendar__weekday">Пн</span>
                <span class="calendar__weekday">Вт</span>
                <span class="calendar__weekday">Ср</span>
                <span class="calendar__weekday">Чт</span>
                <span class="calendar__weekday">Пт</span>
                <span class="calendar__weekday dayoff">Сб</span>
                <span class="calendar__weekday dayoff">Вс</span>
            </span>
            <span class="calendar__days"></span>
        </span>
    </span>
</label>