@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    'value'             => null,
    'form'              => null,
    'required'          => false,
    'disabled'          => false,
    'autofocus'         => false,
    'data'              => null,
    'title'             => null,
    'message'           => null,
    'status'            => '',
])

@php
// Обработка class
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));

// Обработка status
$status = is_string($status) ? trim(preg_replace('/\s+/', ' ', $status)) : '';
$status = $status === '' ? [] : array_unique(explode(' ', $status));

// Добавляем статусы
if (!is_null($value) && $value !== '') {
    $status[] = 'not_empty';
}

if ($autofocus) {
    $status[] = 'focused';
}

// Убираем дубликаты
$status = array_unique($status);
@endphp

<label
    @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
    data-label="ckeditor"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">
        <textarea
            @isset($id) id="{{ $id }}" @endisset
            @isset($name) name="{{ $name }}" @endisset
            @isset($form) form="{{ $form }}" @endisset
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($autofocus) autofocus @endif
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        >@isset($value){!! $value !!}@endisset</textarea>
    </span>
    @isset($message)
    <span class="label__message">{{ is_string($message) ? $message : '' }}</span>
    @endisset
    @isset($title)
    <span class="label__title">{{ $title }}</span>
    @endisset
</label>