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
        <div data-action="delete" data-component="items.list-block"><span data-icon="trash"></span></div>
    </div>

    <div class="accordion__body">
        <div class="flex__col">
            <x-form.text
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][{{ $index }}][title]"
                value="{{ data_get($form_data, 'item.title', '') }}"
                title="Заголовок блока"
                :data="[
                    'sync' => 'item'. ($locale ? '_'. $locale : '') .'_'. $form_data['aid'] .'_'. $index
                ]"
            />

            <x-form.text
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][{{ $index }}][subtitle]"
                value="{{ data_get($form_data, 'item.subtitle', '') }}"
                title="Подзаголовок блока"
            />

            <x-form.ckeditor
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][{{ $index }}][content]"
                value="{{ data_get($form_data, 'item.content', '') }}"
                title="Текст"
            />

            <x-form.text
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][{{ $index }}][link]"
                value="{{ data_get($form_data, 'item.link', '') }}"
                title="Ссылка"
            />

            <div style="font-size: 14px;">Изображение</div>

            <div class="file__panel" data-file="{{ $form_data['aid'] }}_{{ $index }}">
                {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}
                <x-form.hidden
                    class="file__input"
                    name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][{{ $index }}][image]"
                    value="{{ data_get($form_data, 'item.image.aid', '') }}"
                />
                <div class="file__body">
                    @if(isset($form_data['item']['image']) && !empty($form_data['item']['image']))
                    <div class="file_info">
                        <span class="file__icon">
                            <img src="{{ $form_data['item']['image']['path'] }}" />
                        </span>
                        <span class="file__name">{{ $form_data['item']['image']['name'] }}.{{ $form_data['item']['image']['extension'] }}</span>
                    </div>
                    @endif
                </div>
                
                {{-- TODO: вынести кнопку в отдельную компоненту --}}
                @if(isset($form_data['item']['image']) && !empty($form_data['item']['image']))
                <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Изменить</div>
                @else
                <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Выбрать</div>
                @endif
            </div>
        </div>
    </div>
</div>