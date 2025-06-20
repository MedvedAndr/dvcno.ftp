@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="format_text_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title" data-sync=""></div>
            <div class="info__type">Форматируемый текст</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>

    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale }}][{{ $data['aid'] }}]['aid']"
            value="{{ $data['aid'] }}"
        />
        <x-form.ckeditor
            name="sections[{{ $locale }}][{{ $data['aid'] }}][content][0]"
            value="{{ $data['content'] }}"
            title="Текст"
        />
    </div>
</div>