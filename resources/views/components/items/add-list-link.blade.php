@props([
    'index' => 1,
    'locale',
    'form_data' => [],
])

<div class="accordion" data-item="item_{{ $form_data['aid'] }}_{{ $index }}" data-index="{{ $index }}">
    <div class="accordion__head">
        <div class="accordion__head_title">
            <span data-sync="item_{{ $locale }}_{{ $form_data['aid'] }}_{{ $index }}">{{ $form_data['item']['title'] }}</span>
            <span class="empty">[Заголовок отсутствует]</span>
        </div>
        <div class="accordion__head_icon"><span data-icon=""></span></div>
        <div data-item-del="add-list-link"><span data-icon="trash"></span></div>
    </div>

    <div class="accordion__body">
        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][{{ $index }}][title]"
                value="{{ $form_data['item']['title'] ?? '' }}"
                title="Заголовок ссылки"
                :data="[
                    'sync' => 'item_'. $locale .'_'. $form_data['aid'] .'_'. $index
                ]"
            />

            <x-form.text
                name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][{{ $index }}][link]"
                value="{{ $form_data['item']['link'] ?? '' }}"
                title="Адрес ссылки"
            />
        </div>
    </div>
</div>