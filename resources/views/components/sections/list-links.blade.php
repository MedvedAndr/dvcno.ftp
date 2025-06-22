@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="header_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title"><span data-sync="title_{{ $locale->aid }}_{{ $data['aid'] }}">{{ $data['title'] }}</span><span class="empty">[Название отсутствует]</span></div>
            <div class="info__type">Список ссылок</div>
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
                'item-data' => 'add-list-link',
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][title]"
            value=""
            :data="[
                'item-data' => 'add-list-link',
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

            <div style="font-size: 14px;">Перечень ссылок</div>

            <div data-items="item_{{ $data['aid'] }}" data-items-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @foreach($data['content'] as $item)
                <x-items.add-list-link
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'title' => $item['title'],
                            'link' => $item['link'],
                        ]
                    ]"
                />
                @php $i++; @endphp
                @endforeach
            </div>
            <div class="button" data-item-add="add-list-link" data-item-list="item_{{ $data['aid'] }}">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить ссылку</span>
            </div>
        </div>
    </div>
</div>