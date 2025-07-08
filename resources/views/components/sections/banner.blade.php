@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="banner_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title">
                <span data-sync="title_{{ $locale->aid }}_{{ $data['aid'] }}">{{ $data['title'] }}</span>
                <span class="empty">[Название отсутствует]</span>
            </div>
            <div class="info__type">Баннер</div>
        </div>
        <div class="expander__icon"><span data-icon></span></div>
    </div>
    
    <div class="expander__body">
        <x-form.hidden
            name="sections[{{ $locale->aid }}][{{ $data['aid'] }}]['aid']"
            value="{{ $data['aid'] }}"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][aid]"
            value="{{ $data['aid'] }}"
            :data="[
                'item-data' => 'add-list-doc_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][title]"
            value=""
            :data="[
                'item-data' => 'add-list-doc_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][aid]"
            value="{{ $data['aid'] }}"
            :data="[
                'item-data' => 'add-list-video_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][title]"
            value=""
            :data="[
                'item-data' => 'add-list-video_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][type]"
            value="video"
            :data="[
                'item-data' => 'add-list-video_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][link]"
            value=""
            :data="[
                'item-data' => 'add-list-video_'. $index,
            ]"
        />

        <x-form.hidden
            name="elements[{{ $locale->locale_code }}][item][iframe]"
            value=""
            :data="[
                'item-data' => 'add-list-video_'. $index,
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

            <x-form.text
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['content']['title'] ?? '' }}"
                title="Заголовок баннера"
            />

            <x-form.ckeditor
                name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][content]"
                value="{{ $data['content']['content'] ?? '' }}"
                title="Текст"
            />

            <div style="font-size: 14px;">Изображения</div>

            <div class="section_images">
                <div>
                    <div style="font-size: 14px;">ПК</div>

                    <div class="file__panel" data-file="{{ $data['aid'] }}_big_{{ $index }}">
                        {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                        <x-form.hidden
                            class="file__input"
                            name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][images][big]"
                            value="{{ $data['content']['images']['big']['aid'] ?? '' }}"
                        />
                        <div class="file__body">
                            @if(isset($data['content']['images']['big']) && $data['content']['images']['big'] !== '')
                            <div class="file_info">
                                <span class="file__icon">
                                    <img src="{{ $data['content']['images']['big']['path'] }}" />
                                </span>
                                <span class="file__name">{{ $data['content']['images']['big']['name'] }}.{{ $data['content']['images']['big']['extension'] }}</span>
                            </div>
                            @endif
                        </div>
                        
                        {{-- TODO: вынести кнопку в отдельную компоненту --}}
                        @if(isset($data['content']['images']['big']) && $data['content']['images']['big'] !== '')
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Изменить</div>
                        @else
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Выбрать</div>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-size: 14px;">Планшет</div>

                    <div class="file__panel" data-file="{{ $data['aid'] }}_medium_{{ $index }}">
                        {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                        <x-form.hidden
                            class="file__input"
                            name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][images][medium]"
                            value="{{ $data['content']['images']['medium']['aid'] ?? '' }}"
                        />
                        <div class="file__body">
                            @if(isset($data['content']['images']['medium']) && $data['content']['images']['medium'] !== '')
                            <div class="file_info">
                                <span class="file__icon">
                                    <img src="{{ $data['content']['images']['medium']['path'] }}" />
                                </span>
                                <span class="file__name">{{ $data['content']['images']['medium']['name'] }}.{{ $data['content']['images']['medium']['extension'] }}</span>
                            </div>
                            @endif
                        </div>
                        
                        {{-- TODO: вынести кнопку в отдельную компоненту --}}
                        @if(isset($data['content']['images']['medium']) && $data['content']['images']['medium'] !== '')
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Изменить</div>
                        @else
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Выбрать</div>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="font-size: 14px;">Сматрфон</div>

                    <div class="file__panel" data-file="{{ $data['aid'] }}_small_{{ $index }}">
                        {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                        <x-form.hidden
                            class="file__input"
                            name="sections[{{ $locale->aid }}][{{ $data['aid'] }}][content][images][small]"
                            value="{{ $data['content']['images']['small']['aid'] ?? '' }}"
                        />
                        <div class="file__body">
                            @if(isset($data['content']['images']['small']) && $data['content']['images']['small'] !== '')
                            <div class="file_info">
                                <span class="file__icon">
                                    <img src="{{ $data['content']['images']['small']['path'] }}" />
                                </span>
                                <span class="file__name">{{ $data['content']['images']['small']['name'] }}.{{ $data['content']['images']['small']['extension'] }}</span>
                            </div>
                            @endif
                        </div>
                        
                        {{-- TODO: вынести кнопку в отдельную компоненту --}}
                        @if(isset($data['content']['images']['small']) && $data['content']['images']['small'] !== '')
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Изменить</div>
                        @else
                        <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Выбрать</div>
                        @endif
                    </div>
                </div>
            </div>

            <div style="font-size: 14px;">Перечень документов</div>

            <div data-items="item_{{ $data['aid'] }}_document" data-items-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @isset($data['content']['documents'])
                @foreach($data['content']['documents'] as $item)
                <x-items.add-list-doc
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'title' => $item['title'],
                            'document' => $item['document'],
                        ]
                    ]"
                />
                @php $i++; @endphp
                @endforeach
                @endisset
            </div>

            <div class="button" data-item-add="add-list-doc" data-index="{{ $index }}" data-item-list="item_{{ $data['aid'] }}_document">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить документ</span>
            </div>
            
            <div style="font-size: 14px;">Перечень видео</div>

            <div data-items="item_{{ $data['aid'] }}_video" data-items-lang="{{ $locale->locale_code }}" data-accordion="acc_{{ $data['aid'] }}">
                @php $i = 0; @endphp
                @isset($data['content']['videos'])
                @foreach($data['content']['videos'] as $item)
                <x-items.add-list-video
                    index="{{ $i }}"
                    locale="{{ $locale->aid }}"
                    :form_data="[
                        'aid' => $data['aid'],
                        'item' => [
                            'title' => $item['title'],
                            'image' => $item['image'] ?? null,
                            'type' => $item['type'] ?? 'video',
                            'video_mp4' => $item['video_mp4'] ?? null,
                            'video_webm' => $item['video_webm'] ?? null,
                            'link' => $item['link'] ?? null,
                            'iframe' => $item['iframe'] ?? null
                        ]
                    ]"
                />
                @php $i++; @endphp
                @endforeach
                @endisset
            </div>

            <div class="button" data-item-add="add-list-video" data-index="{{ $index }}" data-item-list="item_{{ $data['aid'] }}_video">
                <span class="button__icon"><span data-icon="plus"></span></span>
                <span class="button__title">Добавить видео</span>
            </div>
        </div>
    </div>
</div>