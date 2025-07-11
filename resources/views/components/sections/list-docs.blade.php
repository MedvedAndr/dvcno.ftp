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
            <div class="info__type">Список документов</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>

    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][aid]"
            value="{{ $data['aid'] }}"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][aid]"
            value="{{ $data['aid'] }}"
            :data="[
                'field-context' => 'items.list-doc',
            ]"
        />

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
                title="Заголовок документов"
            />

            <div style="font-size: 14px;">Перечень документов</div>

            <div data-list="item_{{ $data['aid'] }}" data-list-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @isset($data['content']['list'])
                @foreach($data['content']['list'] as $item)
                <x-items.list-doc
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

            <div class="button" data-action="add" data-component="items.list-doc" data-target-container="item_{{ $data['aid'] }}" data-multi-language="true">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить документ</span>
            </div>
        </div>
    </div>
</div>