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
        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][title]"
                value="{{ $data['title'] }}"
                title="Название секции"
                :data="[
                    'sync' => 'title_'. $locale->aid .'_'. $data['aid']
                ]"
            />

            <x-form.text
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['content']['title'] ?? '' }}"
                title="Заголовок для видео"
            />

            <div style="font-size: 14px;">Перечень видео</div>

            <div data-items="item_{{ $data['aid'] }}" data-items-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @isset($data['content']['list'])
                @foreach($data['content']['list'] as $item)
                <x-items.add-list-video
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'title' => $item['title'],
                            'document' => $item['document'],
                        ]
                    ]"
                />
                @php $i++; @endphp
                @endforeach
                @endisset
            </div>

            <div class="button" data-item-add="add-list-video" data-item-list="item_{{ $data['aid'] }}">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить видео</span>
            </div>
        </div>
    </div>
</div>