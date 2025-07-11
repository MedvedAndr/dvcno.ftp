@props([
    'index' => 1,
    'locale' => null,
    'form_data' => [],
])

<div class="accordion" data-item="item_{{ $form_data['aid'] }}_{{ $index }}" data-index="{{ $index }}">
    <div class="accordion__head">
        <div class="accordion__head_title">
            <span data-sync="item{{ $locale ? '_'. $locale : '' }}_{{ $form_data['aid'] }}_{{ $index }}">{{ data_get($form_data, 'item.title', '') }}</span>
            <span class="empty">[Заголовок отсутствует]</span>
        </div>
        <div class="accordion__head_icon"><span data-icon=""></span></div>
        <div data-action="delete" data-component="items.list-doc"><span data-icon="trash"></span></div>
    </div>

    <div class="accordion__body">
        <div class="flex__col">
            <x-form.text
                name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][list][{{ $index }}][title]"
                value="{{ data_get($form_data, 'item.title', '') }}"
                title="Заголовок документа"
                :data="[
                    'sync' => 'item'. ($locale ? '_'. $locale : '') .'_'. $form_data['aid'] .'_'. $index
                ]"
            />
            
            <div style="font-size: 14px;">Документ</div>

            <div class="file__panel" data-file="{{ $form_data['aid'] }}_{{ $index }}">
                {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                <x-form.hidden
                    class="file__input"
                    name="sections{{ $locale ? '['. $locale .']' : '' }}[{{ $form_data['aid'] }}][content][list][{{ $index }}][document]"
                    value="{{ data_get($form_data, 'item.document.aid', '') }}"
                />
                <div class="file__body">
                    @if(isset($form_data['item']['document']))
                    <div class="file_info">
                        <span class="file__icon"><span data-icon="file"></span></span>
                        <span class="file__name">{{ $form_data['item']['document']['name'] }}.{{ $form_data['item']['document']['extension'] }}</span>
                    </div>
                    @endif
                </div>
                
                {{-- TODO: вынести кнопку в отдельную компоненту --}}
                @if(isset($form_data['item']['document']))
                <div class="button" data-file-manager="" data-type="single" data-extensions="doc docx pdf">Изменить</div>
                @else
                <div class="button" data-file-manager="" data-type="single" data-extensions="doc docx pdf">Выбрать</div>
                @endif
            </div>
        </div>
    </div>
</div>