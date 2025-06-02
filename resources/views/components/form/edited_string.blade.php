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
    'status'            => '',
    'before'            => null,
    'after'             => null,
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
    data-label="edited_string"
    @if(!empty($status)) data-status="{{ implode(' ', $status) }}" @endif
>
    <span class="label__input">
        @isset($before)<span class="label__before">{{ $before }}</span>@endisset
        <input
            @isset($id) id="{{ $id }}" @endisset
            type="hidden"
            @isset($name) name="{{ $name }}" @endisset
            @isset($value) value="{!! $value !!}" @endisset
            @if($data)
            @foreach($data as $data_name => $data_value)
            data-{{ $data_name }}="{{ $data_value }}"
            @endforeach
            @endif
        />
        <span class="label__text">
            @isset($value){!! $value !!}@endisset
        </span>
        @isset($after)<span class="label__after">{{ $after }}</span>@endisset
    </span>

    <span class="buttons">
        <span class="label__not_edit">
            <span class="label__button" data-button="edit"><span data-icon="edit"></span></span>
        </span>
        <span class="label__edit">
            <span class="label__button" data-button="save"><span data-icon="save"></span></span>
            <span class="label__button" data-button="cancel"><span data-icon="x"></span></span>
        </span>
    </span>
</label>