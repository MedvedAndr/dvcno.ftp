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
            <div class="info__type">Аккордион</div>
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
                'item-data' => 'add-list-accordion',
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][title]"
            value=""
            :data="[
                'item-data' => 'add-list-accordion',
            ]"
        />

        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['title'] }}"
                title="Название секции"
                :data="[
                    'sync' => 'title_'. $locale->aid .'_'. $data['aid']
                ]"
            />

            <x-form.text
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['content']['title'] }}"
                title="Заголовок аккордиона"
            />

            <div style="font-size: 14px;">Список элементов аккордиона</div>

            <div data-items="item_{{ $data['aid'] }}" data-items-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @foreach($data['content']['list'] as $item)
                <x-items.add-list-accordion
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'title' => $item['title'],
                            'content' => $item['content'],
                        ]
                    ]"
                />
                {{-- <div class="accordion">
                    <div class="accordion__head">
                        <div class="accordion__head_title" data-sync="item_{{ $locale->aid }}_{{ $data['aid'] }}_{{ $i }}">{{ $item['title'] }}</div>
                        <div class="accordion__head_icon"><span data-icon=""></span></div>
                    </div>
                    <div class="accordion__body">
                        <div class="flex__col">
                            <x-form.text
                                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][list][{{ $i }}][title]"
                                value="{{ $item['title'] }}"
                                title="Заголовок"
                                :data="[
                                    'sync' => 'item_'. $locale->aid .'_'. $data['aid'] .'_'. $i
                                ]"
                            />
                            <x-form.ckeditor
                                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][list][{{ $i }}][content]"
                                value="{{ $item['content'] }}"
                                title="Текст"
                            />
                        </div>
                    </div>
                </div> --}}
                @php $i++; @endphp
                @endforeach
            </div>
            <div class="button" data-item-add="add-list-accordion" data-item-list="item_{{ $data['aid'] }}">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить элемент</span>
            </div>
        </div>
    </div>
</div>