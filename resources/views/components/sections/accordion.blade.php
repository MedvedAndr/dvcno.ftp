@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="acoordion_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title" data-sync="title_{{ $locale }}_{{ $data['aid'] }}">{{ $data['content']['title'] }}</div>
            <div class="info__type">Аккордион</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>
    
    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale }}][{{ $data['aid'] }}]['aid']"
            value="{{ $data['aid'] }}"
        />
        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['content']['title'] }}"
                title="Заголовок аккордиона"
                :data="[
                    'sync' => 'title_'. $locale .'_'. $data['aid']
                ]"
            />

            <div style="font-size: 14px;">Список аккордиона</div>

            @php $i = 0; @endphp
            <div class="" data-accordion="acc_{{ $data['aid'] }}_{{ $i }}">
                @foreach($data['content']['list'] as $item)
                <div class="accordion">
                    <div class="accordion__head">
                        <div class="accordion__head_title" data-sync="item_{{ $locale }}_{{ $data['aid'] }}_{{ $i }}">{{ $item['title'] }}</div>
                        <div class="accordion__head_icon"><span data-icon=""></span></div>
                    </div>
                    <div class="accordion__body">
                        <div class="flex__col">
                            <x-form.text
                                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][list][{{ $i }}][title]"
                                value="{{ $item['title'] }}"
                                title="Заголовок"
                                :data="[
                                    'sync' => 'item_'. $locale .'_'. $data['aid'] .'_'. $i
                                ]"
                            />
                            <x-form.ckeditor
                                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][list][{{ $i }}][content]"
                                value="{{ $item['content'] }}"
                                title="Текст"
                            />
                        </div>
                    </div>
                </div>
                @php $i++; @endphp
                @endforeach
                {{-- TODO: Сделать кнопку добавления пункта аккордиона --}}
            </div>
        </div>
    </div>
</div>