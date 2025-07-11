@props([
    'index' => 1,
    'locale',
    'form_data' => [],
])

<div class="accordion" data-item="item_{{ $form_data['aid'] }}_{{ $index }}" data-index="{{ $index }}">
    <div class="accordion__head">
        <div class="accordion__head_title">
            <span data-sync="item{{ $locale ? '_'. $locale : '' }}_{{ $form_data['aid'] }}_{{ $index }}">{{ data_get($form_data, 'item.title', '') }}</span>
            <span class="empty">[Заголовок отсутствует]</span>
        </div>
        <div class="accordion__head_icon"><span data-icon=""></span></div>
        <div data-action="delete" data-component="items.list-accordion"><span data-icon="trash"></span></div>
    </div>

    <div class="accordion__body">
        <div class="flex__col">
            <x-form.text
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][list][{{ $index }}][title]"
                value="{{ data_get($form_data, 'item.title', '') }}"
                title="Заголовок элемента"
                :data="[
                    'sync' => 'item'. ($locale ? '_'. $locale : '') .'_'. $form_data['aid'] .'_'. $index
                ]"
            />

            <x-form.ckeditor
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][list][{{ $index }}][content]"
                value="{{ data_get($form_data, 'item.content', '') }}"
                title="Текст"
            />
        </div>
    </div>
</div>