@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="acoordion_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title">
                <span data-sync="title_{{ $locale->aid }}_{{ $data['aid'] }}">{{ $data['title'] }}</span>
                <span class="empty">[Название отсутствует]</span>
            </div>
            <div class="info__type">Список блоков</div>
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
                'field-context' => 'items.list-block_'. $index,
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

            <div style="font-size: 14px;">Список блоков</div>

            <div data-list="item_{{ $data['aid'] }}" data-list-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @foreach($data['content'] as $item)
                <x-items.list-block
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'link' => $item['link'] ?? '',
                            'image' => $item['image'] ?? [],
                            'title' => $item['title'] ?? '',
                            'subtitle' => $item['subtitle'] ?? '',
                            'content' => $item['content'] ?? '',
                        ]
                    ]"
                />
                @php $i++; @endphp
                @endforeach
            </div>
            
            <div class="button" data-action="add" data-component="items.list-block" data-target-container="item_{{ $data['aid'] }}" data-field-set="{{ $index }}" data-multi-language="true">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить блок</span>
            </div>
        </div>
    </div>
</div>