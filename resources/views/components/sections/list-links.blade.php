@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="header_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title" data-sync="title_{{ $locale }}_{{ $data['aid'] }}"></div>
            <div class="info__type">Список ссылок</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>

    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale }}][{{ $data['aid'] }}]['aid']"
            value="{{ $data['aid'] }}"
        />
        <x-form.text
            name="sections[{{ $locale }}][{{ $data['aid'] }}][content][0]"
            value="{{ $data['content'] }}"
            title="Заголовок"
            :data="[
                'sync' => 'title_'. $locale .'_'. $data['aid']
            ]"
        />
    </div>
</div>