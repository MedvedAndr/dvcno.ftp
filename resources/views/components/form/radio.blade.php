@props([
    'id'        => null,
    'class'     => '',
    'name'      => null,
    'value'     => null,
    'checked'   => false,
    'disabled'  => false,
    'required'  => false,
    'data'      => null,
    'title'     => null,
    'status'    => '',
])

@php
// Обрабатываем классы
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));

// Обрабатываем статус
$status = is_string($status) ? trim(preg_replace('/\s+/', ' ', $status)) : '';
$status = $status === '' ? [] : array_unique(explode(' ', $status));

// Добавляем статус "checked", если чекбокс установлен
if ($checked) {
    $status[] = 'checked';
}

// Добавляем статус "disabled", если чекбокс отключён
if ($disabled) {
    $status[] = 'disabled';
}

// Убираем дубликаты статусов
$status = array_unique($status);
@endphp

<label 
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="radio"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">
        <input
            @isset($id) id="{{ $id }}" @endisset
            type="radio"
            @isset($name) name="{{ $name }}" @endisset
            @isset($value) value="{{ $value }}" @endisset
            @if($checked) checked @endif
            @if($disabled) disabled @endif
            @if($required) required @endif
            hidden
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        />
        <span class="label__radio_icon"></span>
    </span>
    
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>
