@props([
    'index' => 1,
    'locale',
    'data' => []
])

<div data-expander="banner_{{ $index }}">
    <div class="expander__head">
        <div class="expander__info">
            <div class="info__title">
                <span data-sync="title_{{ $locale }}_{{ $data['aid'] }}">{{ $data['title'] }}</span>
                <span class="empty">[Название отсутствует]</span>
            </div>
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
                name="sections[{{ $locale }}][{{ $data['aid'] }}][title]"
                value="{{ $data['title'] }}"
                title="Название секции"
                :data="[
                    'sync' => 'title_'. $locale .'_'. $data['aid']
                ]"
            />

            <x-form.text
                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][title]"
                value="{{ $data['content']['title'] ?? '' }}"
                title="Заголовок баннера"
            />

            <x-form.ckeditor
                name="sections[{{ $locale }}][{{ $data['aid'] }}][content][content]"
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
                            name="sections[{{ $locale }}][{{ $data['aid'] }}][content][images][big]"
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
                            name="sections[{{ $locale }}][{{ $data['aid'] }}][content][images][medium]"
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
                            name="sections[{{ $locale }}][{{ $data['aid'] }}][content][images][small]"
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
        </div>
    </div>
</div>