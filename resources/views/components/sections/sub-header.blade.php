@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="header_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title">
                <span data-sync="title_{{ $locale }}_{{ $data['aid'] }}">{{ $data['title'] }}</span>
                <span class="empty">[Название отсутствует]</span>
            </div>
            <div class="info__type">Подзаголовок</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>

    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale }}][{{ $data['aid'] }}][aid]"
            value="{{ $data['aid'] }}"
        />

        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale }}][{{ $data['aid'] }}][title]"
                value="{{ $data['title'] }}"
                title="Название секции"
                :data="[
                    'sync' => 'title_'. $locale .'_'. $data['aid']
                ]"
            />
            <x-form.text
                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][0]"
                value="{{ $data['content'] }}"
                title="Текст подзаголовка"
            />
        </div>
    </div>
</div>