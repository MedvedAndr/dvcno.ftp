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
        <div data-item-del="add-list-doc"><span data-icon="trash"></span></div>
    </div>

    <div class="accordion__body">
        <div class="flex__col">
            <x-form.text
                name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][title]"
                value="{{ $form_data['item']['title'] ?? '' }}"
                title="Заголовок видео"
                :data="[
                    'sync' => 'item_'. $locale .'_'. $form_data['aid'] .'_'. $index
                ]"
            />

            <div style="font-size: 14px;">Изображение</div>

            <div class="file__panel" data-file="{{ $form_data['aid'] }}_{{ $index }}">
                {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                <x-form.hidden
                    class="file__input"
                    name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][image]"
                    value="{{ $form_data['item']['image']['aid'] ?? '' }}"
                />
                <div class="file__body">
                    @if(isset($form_data['item']['image']) && !is_null($form_data['item']['image']))
                    <div class="file_info">
                        <span class="file__icon"><span data-icon="file"></span></span>
                        <span class="file__name">{{ $form_data['item']['image']['name'] }}.{{ $form_data['item']['image']['extension'] }}</span>
                    </div>
                    @endif
                </div>
                
                {{-- TODO: вынести кнопку в отдельную компоненту --}}
                @if(isset($form_data['item']['image']) && !is_null($form_data['item']['image']))
                <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Изменить</div>
                @else
                <div class="button" data-file-manager="" data-type="single" data-extensions="jpeg jpg png webp svg">Выбрать</div>
                @endif
            </div>

            <div data-radio-tabs="{{ $form_data['aid'] }}_{{ $index }}">
                @php
                $status_1 = $form_data['item']['type'] == 'video' ? 'data-status=active' : '';
                $status_2 = $form_data['item']['type'] == 'link' ? 'data-status=active' : '';
                $status_3 = $form_data['item']['type'] == 'iframe' ? 'data-status=active' : '';
                @endphp
                <x-form.radio
                    name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][type]"
                    value="video"
                    title="Видео"
                    :data="[
                        'radio-tab' => 'video'
                    ]"
                    status="{{ $form_data['item']['type'] == 'video' ? 'checked' : null }}"
                />
                <x-form.radio
                    name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][type]"
                    value="link"
                    title="Ссылка"
                    :data="[
                        'radio-tab' => 'link'
                    ]"
                    status="{{ $form_data['item']['type'] == 'link' ? 'checked' : null }}"
                />
                <x-form.radio
                    name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][type]"
                    value="iframe"
                    title="iframe"
                    :data="[
                        'radio-tab' => 'iframe'
                    ]"
                    status="{{ $form_data['item']['type'] == 'iframe' ? 'checked' : null }}"
                />
            </div>

            <div data-radio-tabs-box="{{ $form_data['aid'] }}_{{ $index }}">
                <div class="radio_tab__box" data-radio-tab-box="video" {{ $status_1 }}>
                    <div class="flex__col">    
                        <div style="font-size: 14px;">Основное видео (webp)</div>

                        <div class="file__panel" data-file="{{ $form_data['aid'] }}_{{ $index }}_webm">
                            {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                            <x-form.hidden
                                class="file__input"
                                name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][video_webm]"
                                value="{{ $form_data['item']['video_webm']['aid'] ?? '' }}"
                            />
                            <div class="file__body">
                                @if(isset($form_data['item']['video_webm']))
                                <div class="file_info">
                                    <span class="file__icon"><span data-icon="file"></span></span>
                                    <span class="file__name">{{ $form_data['item']['video_webm']['name'] }}.{{ $form_data['item']['video_webm']['extension'] }}</span>
                                </div>
                                @endif
                            </div>
                            
                            {{-- TODO: вынести кнопку в отдельную компоненту --}}
                            @if(isset($form_data['item']['video_webm']))
                            <div class="button" data-file-manager="" data-type="single" data-extensions="webm">Изменить</div>
                            @else
                            <div class="button" data-file-manager="" data-type="single" data-extensions="webm">Выбрать</div>
                            @endif
                        </div>

                        <div style="font-size: 14px;">Дополнительное видео (mp4)</div>
                        <div class="file__panel" data-file="{{ $form_data['aid'] }}_{{ $index }}_mp4">
                            {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                            <x-form.hidden
                                class="file__input"
                                name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][video_mp4]"
                                value="{{ $form_data['item']['video_mp4']['aid'] ?? '' }}"
                            />
                            <div class="file__body">
                                @if(isset($form_data['item']['video_mp4']))
                                <div class="file_info">
                                    <span class="file__icon"><span data-icon="file"></span></span>
                                    <span class="file__name">{{ $form_data['item']['video_mp4']['name'] }}.{{ $form_data['item']['video_mp4']['extension'] }}</span>
                                </div>
                                @endif
                            </div>
                            
                            {{-- TODO: вынести кнопку в отдельную компоненту --}}
                            @if(isset($form_data['item']['video_mp4']))
                            <div class="button" data-file-manager="" data-type="single" data-extensions="mp4">Изменить</div>
                            @else
                            <div class="button" data-file-manager="" data-type="single" data-extensions="mp4">Выбрать</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="radio_tab__box" data-radio-tab-box="link" {{ $status_2 }}>
                    <x-form.text
                        name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][link]"
                        value="{{ $form_data['item']['link'] }}"
                        title="Ссылка на видео"
                    />
                </div>
                <div class="radio_tab__box" data-radio-tab-box="iframe" {{ $status_3 }}>
                    <x-form.textarea
                        name="sections[{{ $locale }}][{{ $form_data['aid'] }}][content][list][{{ $index }}][iframe]"
                        value="{{ $form_data['item']['iframe'] }}"
                        title="iFrame"
                    />
                </div>
            </div>
        </div>
    </div>
</div>