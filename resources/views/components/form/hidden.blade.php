@props([
    'id'                => null,
    'class'             => '',
    'name'              => null,
    'value'             => null,
    'form'              => null,
    'data'              => null,
])

@php
// Обработка class
$class = is_string($class) ? trim(preg_replace('/\s+/', ' ', $class)) : '';
$class = $class === '' ? [] : array_unique(explode(' ', $class));
@endphp
<label data-label="hidden">
    <input
        @isset($id) id="{{ $id }}" @endisset
        @if(!empty($class)) class="{{ implode(' ', $class) }}" @endif
        type="hidden"
        @isset($name) name="{{ $name }}" @endisset
        @isset($value) value="{{ $value }}" @endisset
        @isset($form) form="{{ $form }}" @endisset
        @if($data)
        @foreach($data as $data_name => $data_value)
        data-{{ $data_name }}="{{ $data_value }}"
        @endforeach
        @endif
    />
</label>