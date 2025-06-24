@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="banner_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title" data-sync="title_{{ $locale }}_{{ $data['aid'] }}">{{ $data['content']['title'] ?? '' }}</div>
            <div class="info__type">Баннер</div>
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
                value="{{ $data['content']['title'] ?? '' }}"
                title="Заголовок баннера"
                :data="[
                    'sync' => 'title_'. $locale .'_'. $data['aid']
                ]"
            />
            <x-form.ckeditor
                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][content]"
                value="{{ $data['content']['content'] ?? '' }}"
                title="Контент баннера"
            />
        </div>
    </div>
</div>