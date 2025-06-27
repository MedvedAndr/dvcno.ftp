@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="list_docs_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title">
                <span data-sync="title_{{ $locale->aid }}_{{ $data['aid'] }}">{{ $data['title'] }}</span>
                <span class="empty">[Название отсутствует]</span>
            </div>
            <div class="info__type">Список видео</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>

    <div class="expander__body">
        @dump($data)
    </div>
</div>