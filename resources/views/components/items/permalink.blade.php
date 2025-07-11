@props([
    'id' => null,
    'index' => 1,
    'locale',
    'form_data' => [],
])

<div
    data-id="{{ $id ? $id : '_'. $index }}"
    data-item="item_{{ $index }}"
    data-index="{{ $index }}"
    data-animation="fade_in"
>
    <div class="menu__item" data-expander="menu_permalink_{{ $index }}">
        <div class="expander__head">
            <div class="expander__info">
                <div class="info__image"></div>
                <div class="info__icon"></div>
                <div class="info__title" data-sync="title_{{ $index }}_{{ $locale }}">{{ isset($form_data['menu_name']) ? $form_data['menu_name'] : '' }}</div>
                <div class="info__type">Произвольная ссылка</div>
            </div>
            <div class="expander__icon">
                <span data-icon></span>
            </div>
        </div>
    
        <div class="expander__body">
            <div class="expander__body_wrapper">
                <x-form.hidden
                    name="points[_{{ $index }}][{{ $locale }}][language_id]"
                    value="{{ isset($form_data['language_id']) ? $form_data['language_id'] : '' }}"
                />
                <x-form.hidden
                    name="points[_{{ $index }}][{{ $locale }}][item_type]"
                    value="link"
                />
                <x-form.text
                    class="menu__url"
                    name="points[_{{ $index }}][{{ $locale }}][url]"
                    value="{{ isset($form_data['menu_url']) ? $form_data['menu_url'] : '' }}"
                    title="URL"
                    :data="[
                        'sync' => 'url_'. $index
                    ]"
                />
                <x-form.text
                    class="menu__title"
                    name="points[_{{ $index }}][{{ $locale }}][title]"
                    value="{{ isset($form_data['menu_name']) ? $form_data['menu_name'] : '' }}"
                    title="Текст ссылки"
                    :data="[
                        'sync' => 'title_'. $index .'_'. $locale
                    ]"
                />
                <x-form.select
                    class="menu__parent"
                    name="points[_{{ $index }}][{{ $locale }}][parent_id]"
                    title="Родительское меню"
                    :data="[
                        'sync' => 'select_parent_'. $index
                    ]"
                />
                <x-form.number
                    class="menu__order"
                    name="points[_{{ $index }}][{{ $locale }}][order]"
                    value="{{ $index }}"
                    min="1"
                    title="Порядок меню"
                    :data="[
                        'sync' => 'order_'. $index
                    ]"
                />
                <x-form.togglebox
                    class="menu__enabled"
                    name="points[_{{ $index }}][{{ $locale }}][enabled]"
                    value="1"
                    checked
                    title="Активен"
                    :data="[
                        'sync' => 'enabled_'. $index
                    ]"
                />
                <div class="button error menu__delete" data-action="delete" data-component="items.permalink">
                    <span class="button__title">Удалить</span>
                    <span class="button__icon"><span data-icon="trash-2"></span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="menu__sub"></div>
</div>